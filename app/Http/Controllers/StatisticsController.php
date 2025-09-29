<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\StudentSearchService;
use App\Models\Student;
use App\Models\SchoolClass;
use App\Models\HealthRecord;

class StatisticsController extends Controller
{
    private StudentSearchService $studentSearchService;

    public function __construct(StudentSearchService $studentSearchService)
    {
        $this->studentSearchService = $studentSearchService;
    }

    /**
     * システム全体の統計データを取得
     */
    public function systemStats(): JsonResponse
    {
        try {
            $stats = [
                'students' => [
                    'total' => Student::count(),
                    'active' => Student::count(), // 実際のアクティブ学生判定ロジックを追加
                    'by_grade' => Student::selectRaw('grade, COUNT(*) as count')
                        ->groupBy('grade')
                        ->orderBy('grade')
                        ->get()
                        ->mapWithKeys(fn($item) => [$item->grade => $item->count])
                        ->toArray(),
                ],
                'classes' => [
                    'total' => SchoolClass::count(),
                    'by_grade' => SchoolClass::selectRaw('grade, COUNT(*) as count')
                        ->groupBy('grade')
                        ->orderBy('grade')
                        ->get()
                        ->mapWithKeys(fn($item) => [$item->grade => $item->count])
                        ->toArray(),
                ],
                'health_records' => [
                    'total' => HealthRecord::count(),
                    'recent' => HealthRecord::where('record_date', '>=', now()->subDays(30))->count(),
                    'by_month' => HealthRecord::selectRaw('YEAR(record_date) as year, MONTH(record_date) as month, COUNT(*) as count')
                        ->groupByRaw('YEAR(record_date), MONTH(record_date)')
                        ->orderByRaw('year DESC, month DESC')
                        ->limit(12)
                        ->get()
                        ->map(fn($item) => [
                            'period' => "{$item->year}-" . str_pad($item->month, 2, '0', STR_PAD_LEFT),
                            'count' => $item->count
                        ])
                        ->toArray(),
                ],
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'システム統計データを取得しました'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'システム統計データの取得に失敗しました',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 学年別統計データを取得
     */
    public function gradeStats(Request $request): JsonResponse
    {
        try {
            $grade = $request->get('grade');
            
            if (!$grade) {
                return response()->json([
                    'success' => false,
                    'message' => '学年を指定してください'
                ], 400);
            }

            $stats = [
                'grade' => $grade,
                'students' => [
                    'total' => Student::where('grade', $grade)->count(),
                    'gender_distribution' => Student::where('grade', $grade)
                        ->selectRaw('gender, COUNT(*) as count')
                        ->groupBy('gender')
                        ->get()
                        ->mapWithKeys(fn($item) => [$item->gender => $item->count])
                        ->toArray(),
                ],
                'classes' => SchoolClass::where('grade', $grade)
                    ->withCount('students')
                    ->get()
                    ->map(fn($class) => [
                        'id' => $class->id,
                        'name' => $class->name,
                        'student_count' => $class->students_count,
                        'teacher_name' => $class->teacher_name,
                    ])
                    ->toArray(),
                'health_records' => [
                    'total' => HealthRecord::whereHas('student', function($query) use ($grade) {
                        $query->where('grade', $grade);
                    })->count(),
                    'recent' => HealthRecord::whereHas('student', function($query) use ($grade) {
                        $query->where('grade', $grade);
                    })->where('record_date', '>=', now()->subDays(30))->count(),
                ],
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => "{$grade}年生の統計データを取得しました"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '学年別統計データの取得に失敗しました',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * クラス別統計データを取得
     */
    public function classStats(Request $request, string $classId): JsonResponse
    {
        try {
            $schoolClass = SchoolClass::findOrFail($classId);
            
            $stats = [
                'class_info' => [
                    'id' => $schoolClass->id,
                    'name' => $schoolClass->name,
                    'grade' => $schoolClass->grade,
                    'teacher_name' => $schoolClass->teacher_name,
                ],
                'students' => [
                    'total' => $schoolClass->students()->count(),
                    'gender_distribution' => $schoolClass->students()
                        ->selectRaw('gender, COUNT(*) as count')
                        ->groupBy('gender')
                        ->get()
                        ->mapWithKeys(fn($item) => [$item->gender => $item->count])
                        ->toArray(),
                ],
                'health_records' => [
                    'total' => HealthRecord::whereHas('student', function($query) use ($classId) {
                        $query->where('school_class_id', $classId);
                    })->count(),
                    'recent' => HealthRecord::whereHas('student', function($query) use ($classId) {
                        $query->where('school_class_id', $classId);
                    })->where('record_date', '>=', now()->subDays(30))->count(),
                ],
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => "クラス「{$schoolClass->name}」の統計データを取得しました"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'クラス別統計データの取得に失敗しました',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 健康記録統計データを取得
     */
    public function healthStats(Request $request): JsonResponse
    {
        try {
            $startDate = $request->get('start_date');
            $endDate = $request->get('end_date');
            
            $query = HealthRecord::query();
            
            if ($startDate) {
                $query->where('record_date', '>=', $startDate);
            }
            
            if ($endDate) {
                $query->where('record_date', '<=', $endDate);
            }

            $stats = [
                'summary' => [
                    'total_records' => $query->count(),
                    'average_height' => round($query->avg('height'), 2),
                    'average_weight' => round($query->avg('weight'), 2),
                    'average_bmi' => round($query->selectRaw('AVG(weight / (height/100 * height/100)) as avg_bmi')->first()->avg_bmi ?? 0, 2),
                ],
                'trends' => [
                    'height_by_grade' => HealthRecord::join('students', 'health_records.student_id', '=', 'students.id')
                        ->selectRaw('students.grade, AVG(health_records.height) as avg_height')
                        ->when($startDate, fn($q) => $q->where('health_records.record_date', '>=', $startDate))
                        ->when($endDate, fn($q) => $q->where('health_records.record_date', '<=', $endDate))
                        ->groupBy('students.grade')
                        ->orderBy('students.grade')
                        ->get()
                        ->mapWithKeys(fn($item) => [$item->grade => round($item->avg_height, 2)])
                        ->toArray(),
                    'weight_by_grade' => HealthRecord::join('students', 'health_records.student_id', '=', 'students.id')
                        ->selectRaw('students.grade, AVG(health_records.weight) as avg_weight')
                        ->when($startDate, fn($q) => $q->where('health_records.record_date', '>=', $startDate))
                        ->when($endDate, fn($q) => $q->where('health_records.record_date', '<=', $endDate))
                        ->groupBy('students.grade')
                        ->orderBy('students.grade')
                        ->get()
                        ->mapWithKeys(fn($item) => [$item->grade => round($item->avg_weight, 2)])
                        ->toArray(),
                ],
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => '健康記録統計データを取得しました'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '健康記録統計データの取得に失敗しました',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}