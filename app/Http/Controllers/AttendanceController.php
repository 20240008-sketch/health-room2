<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AttendanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = AttendanceRecord::with(['student', 'student.schoolClass']);

        // Filter by search (student name or number)
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->whereHas('student', function ($q) use ($searchTerm) {
                $q->where(function($query) use ($searchTerm) {
                    $query->where('name', 'like', '%' . $searchTerm . '%')
                        ->orWhere('name_kana', 'like', '%' . $searchTerm . '%')
                        ->orWhere('student_number', 'like', '%' . $searchTerm . '%')
                        ->orWhere('student_id', 'like', '%' . $searchTerm . '%');
                });
            });
        }

        // Filter by date
        if ($request->has('date')) {
            $query->whereDate('date', $request->date);
        }

        // Filter by grade
        if ($request->has('grade')) {
            $query->whereHas('student', function ($q) use ($request) {
                $q->where('grade', $request->grade);
            });
        }

        // Filter by class
        if ($request->has('class_id')) {
            $query->whereHas('student', function ($q) use ($request) {
                $q->where('school_class_id', $request->class_id);
            });
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $attendanceRecords = $query->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate statistics
        $statistics = [
            'present' => AttendanceRecord::where('status', 'present')->count(),
            'absent' => AttendanceRecord::where('status', 'absent')->count(),
            'late' => AttendanceRecord::where('status', 'late')->count(),
            'early_leave' => AttendanceRecord::where('status', 'early_leave')->count(),
        ];

        if ($request->has('date')) {
            $statistics = [
                'present' => AttendanceRecord::whereDate('date', $request->date)->where('status', 'present')->count(),
                'absent' => AttendanceRecord::whereDate('date', $request->date)->where('status', 'absent')->count(),
                'late' => AttendanceRecord::whereDate('date', $request->date)->where('status', 'late')->count(),
                'early_leave' => AttendanceRecord::whereDate('date', $request->date)->where('status', 'early_leave')->count(),
            ];
        }

        // Return data with student relationship
        return response()->json([
            'data' => $attendanceRecords,
            'statistics' => $statistics
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'student_id' => 'required|exists:students,id',
            'date' => 'required|date',
            'status' => 'required|in:present,absent,late,early_leave',
            'arrival_time' => 'nullable|date_format:H:i',
            'departure_time' => 'nullable|date_format:H:i',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => '入力データが不正です',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $attendanceRecord = AttendanceRecord::updateOrCreate(
                [
                    'student_id' => $request->student_id,
                    'date' => $request->date,
                ],
                [
                    'status' => $request->status,
                    'arrival_time' => $request->arrival_time,
                    'departure_time' => $request->departure_time,
                    'notes' => $request->notes,
                ]
            );

            return response()->json([
                'message' => '出席記録を保存しました',
                'data' => $attendanceRecord->load('student')
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => '出席記録の保存に失敗しました',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk store attendance records.
     */
    public function bulkStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'records' => 'required|array',
            'records.*.student_id' => 'required|exists:students,id',
            'records.*.status' => 'required|in:present,absent,late,early_leave',
            'records.*.arrival_time' => 'nullable|date_format:H:i',
            'records.*.departure_time' => 'nullable|date_format:H:i',
            'records.*.notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => '入力データが不正です',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $savedRecords = [];
            foreach ($request->records as $record) {
                $attendanceRecord = AttendanceRecord::updateOrCreate(
                    [
                        'student_id' => $record['student_id'],
                        'date' => $request->date,
                    ],
                    [
                        'status' => $record['status'],
                        'arrival_time' => $record['arrival_time'] ?? null,
                        'departure_time' => $record['departure_time'] ?? null,
                        'notes' => $record['notes'] ?? null,
                    ]
                );
                $savedRecords[] = $attendanceRecord;
            }

            DB::commit();

            return response()->json([
                'message' => '出席記録を一括保存しました',
                'data' => $savedRecords,
                'count' => count($savedRecords)
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => '出席記録の一括保存に失敗しました',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $attendanceRecord = AttendanceRecord::with('student')->find($id);

        if (!$attendanceRecord) {
            return response()->json([
                'message' => '出席記録が見つかりません'
            ], 404);
        }

        return response()->json([
            'data' => $attendanceRecord
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $attendanceRecord = AttendanceRecord::find($id);

        if (!$attendanceRecord) {
            return response()->json([
                'message' => '出席記録が見つかりません'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'sometimes|in:present,absent,late,early_leave',
            'arrival_time' => 'nullable|date_format:H:i',
            'departure_time' => 'nullable|date_format:H:i',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => '入力データが不正です',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $attendanceRecord->update($request->only([
                'status',
                'arrival_time',
                'departure_time',
                'notes'
            ]));

            return response()->json([
                'message' => '出席記録を更新しました',
                'data' => $attendanceRecord->load('student')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => '出席記録の更新に失敗しました',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $attendanceRecord = AttendanceRecord::find($id);

        if (!$attendanceRecord) {
            return response()->json([
                'message' => '出席記録が見つかりません'
            ], 404);
        }

        try {
            $attendanceRecord->delete();

            return response()->json([
                'message' => '出席記録を削除しました'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => '出席記録の削除に失敗しました',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get attendance statistics.
     */
    public function statistics(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth());
        $endDate = $request->input('end_date', now()->endOfMonth());

        $statistics = [
            'total_records' => AttendanceRecord::whereBetween('date', [$startDate, $endDate])->count(),
            'by_status' => [
                'present' => AttendanceRecord::whereBetween('date', [$startDate, $endDate])->where('status', 'present')->count(),
                'absent' => AttendanceRecord::whereBetween('date', [$startDate, $endDate])->where('status', 'absent')->count(),
                'late' => AttendanceRecord::whereBetween('date', [$startDate, $endDate])->where('status', 'late')->count(),
                'early_leave' => AttendanceRecord::whereBetween('date', [$startDate, $endDate])->where('status', 'early_leave')->count(),
            ],
            'by_date' => AttendanceRecord::whereBetween('date', [$startDate, $endDate])
                ->select('date', DB::raw('count(*) as count'))
                ->groupBy('date')
                ->orderBy('date')
                ->get(),
        ];

        return response()->json($statistics);
    }
}
