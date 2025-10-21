<?php

namespace App\Http\Controllers;

use App\Models\NursingVisit;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NursingVisitController extends Controller
{
    /**
     * 来室記録一覧取得
     */
    public function index(Request $request)
    {
        $query = NursingVisit::with(['student.schoolClass']);

        // 日付フィルター
        if ($request->has('date')) {
            $query->whereDate('date', $request->date);
        }

        // 学生検索（名前、かな、出席番号）
        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('name_kana', 'LIKE', "%{$search}%")
                    ->orWhere('student_number', 'LIKE', "%{$search}%");
            });
        }

        // クラスフィルター
        if ($request->has('class_id') && $request->class_id !== '') {
            $query->whereHas('student', function ($q) use ($request) {
                $q->where('class_id', $request->class_id);
            });
        }

        // 種別フィルター
        if ($request->has('type') && $request->type !== '') {
            $query->where('type', $request->type);
        }

        $visits = $query->orderBy('date', 'desc')
            ->orderBy('time', 'desc')
            ->get();

        // レスポンスデータの整形
        $formattedVisits = $visits->map(function ($visit) {
            return [
                'id' => $visit->id,
                'date' => $visit->date->format('Y-m-d'),
                'time' => $visit->time->format('H:i'),
                'student_id' => $visit->student_id,
                'student_name' => $visit->student->name ?? '',
                'student_number' => $visit->student->student_number ?? '',
                'class_name' => $visit->student->schoolClass->name ?? '',
                'grade' => $visit->student->schoolClass->grade ?? '',
                'gender' => $visit->student->gender ?? '',
                'category' => $visit->category ?? '',
                'type' => $visit->type ?? '',
                'type_detail' => $visit->type_detail ?? '',
                'occurrence_time' => $visit->occurrence_time ?? '',
                'treatment_notes' => $visit->treatment_notes ?? '',
                'created_at' => $visit->created_at->toISOString(),
            ];
        });

        return response()->json([
            'data' => $formattedVisits
        ]);
    }

    /**
     * 来室記録作成
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'time' => 'required',
            'student_id' => 'required|exists:students,id',
            'category' => 'nullable|string|max:20',
            'type' => 'nullable|string|max:50',
            'type_detail' => 'nullable|string|max:50',
            'occurrence_time' => 'nullable|string|max:255',
            'treatment_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'バリデーションエラー',
                'errors' => $validator->errors()
            ], 422);
        }

        $visit = NursingVisit::create($validator->validated());

        return response()->json([
            'message' => '来室記録を保存しました',
            'data' => $visit->load(['student.schoolClass'])
        ], 201);
    }

    /**
     * 一括作成
     */
    public function bulkStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'visits' => 'required|array',
            'visits.*.date' => 'required|date',
            'visits.*.time' => 'required',
            'visits.*.student_id' => 'required|exists:students,id',
            'visits.*.category' => 'nullable|string|max:20',
            'visits.*.type' => 'nullable|string|max:50',
            'visits.*.type_detail' => 'nullable|string|max:50',
            'visits.*.occurrence_time' => 'nullable|string|max:255',
            'visits.*.treatment_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'バリデーションエラー',
                'errors' => $validator->errors()
            ], 422);
        }

        $visits = [];
        foreach ($request->visits as $visitData) {
            $visit = NursingVisit::create($visitData);
            $visit->load(['student.schoolClass']);
            $visits[] = $visit;
        }

        return response()->json([
            'message' => count($visits) . '件の来室記録を保存しました',
            'data' => $visits
        ], 201);
    }

    /**
     * 来室記録詳細取得
     */
    public function show($id)
    {
        $visit = NursingVisit::with('student')->findOrFail($id);

        return response()->json([
            'data' => $visit
        ]);
    }

    /**
     * 来室記録更新
     */
    public function update(Request $request, $id)
    {
        $visit = NursingVisit::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'date' => 'sometimes|required|date',
            'time' => 'sometimes|required',
            'student_id' => 'sometimes|required|exists:students,id',
            'type' => 'sometimes|required|in:injury,illness,other',
            'occurrence_time' => 'nullable|string|max:255',
            'treatment_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'バリデーションエラー',
                'errors' => $validator->errors()
            ], 422);
        }

        $visit->update($validator->validated());

        return response()->json([
            'message' => '来室記録を更新しました',
            'data' => $visit->load('student')
        ]);
    }

    /**
     * 来室記録削除
     */
    public function destroy($id)
    {
        $visit = NursingVisit::findOrFail($id);
        $visit->delete();

        return response()->json([
            'message' => '来室記録を削除しました'
        ]);
    }

    /**
     * 統計情報取得
     */
    public function statistics(Request $request)
    {
        $query = NursingVisit::query();

        // 日付範囲フィルター
        if ($request->has('start_date')) {
            $query->whereDate('date', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $query->whereDate('date', '<=', $request->end_date);
        }

        $total = $query->count();
        $byType = $query->selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type');

        return response()->json([
            'total' => $total,
            'by_type' => [
                'injury' => $byType['injury'] ?? 0,
                'illness' => $byType['illness'] ?? 0,
                'other' => $byType['other'] ?? 0,
            ]
        ]);
    }
}
