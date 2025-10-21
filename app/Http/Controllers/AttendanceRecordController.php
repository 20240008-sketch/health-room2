<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use Illuminate\Http\Request;

class AttendanceRecordController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = AttendanceRecord::with(['student.schoolClass']);

        // 日付フィルター
        if ($request->has('date')) {
            $query->whereDate('date', $request->date);
        }

        // ステータスフィルター（欠席のみなど）
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // 学生検索
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

        $records = $query->orderBy('date', 'desc')->get();

        // レスポンスデータの整形
        $formattedRecords = $records->map(function ($record) {
            return [
                'id' => $record->id,
                'date' => $record->date->format('Y-m-d'),
                'student_id' => $record->student_id,
                'student_name' => $record->student->name ?? '',
                'student_number' => $record->student->student_number ?? '',
                'class_name' => $record->student->schoolClass->name ?? '',
                'grade' => $record->student->schoolClass->grade ?? '',
                'status' => $record->status,
                'arrival_time' => $record->arrival_time ? $record->arrival_time->format('H:i') : null,
                'departure_time' => $record->departure_time ? $record->departure_time->format('H:i') : null,
                'notes' => $record->notes ?? '',
                'created_at' => $record->created_at->toISOString(),
            ];
        });

        return response()->json([
            'data' => $formattedRecords
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
