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
                    'active' => Student::where('status', 'active')->count(),
                    'by_gender' => Student::selectRaw('gender, COUNT(*) as count')
                        ->groupBy('gender')
                        ->orderBy('gender')
                        ->get()
                        ->mapWithKeys(fn($item) => [$item->gender => $item->count])
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
                    'recent' => HealthRecord::where('created_at', '>=', now()->subDays(30))->count(),
                    'by_month' => HealthRecord::selectRaw('strftime("%Y", created_at) as year, strftime("%m", created_at) as month, COUNT(*) as count')
                        ->groupByRaw('strftime("%Y", created_at), strftime("%m", created_at)')
                        ->orderByRaw('year DESC, month DESC')
                        ->limit(12)
                        ->get()
                        ->map(fn($item) => [
                            'period' => "{$item->year}-{$item->month}",
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
                    'total' => Student::where('grade_id', $grade)->count(),
                    'gender_distribution' => Student::where('grade_id', $grade)
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
                        'name' => $class->class_name,
                        'student_count' => $class->students_count,
                        'teacher_name' => $class->teacher_name ?? 'N/A',
                    ])
                    ->toArray(),
                'health_records' => [
                    'total' => HealthRecord::whereHas('student', function($query) use ($grade) {
                        $query->where('grade_id', $grade);
                    })->count(),
                    'recent' => HealthRecord::whereHas('student', function($query) use ($grade) {
                        $query->where('grade_id', $grade);
                    })->where('created_at', '>=', now()->subDays(30))->count(),
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
                $query->where('created_at', '>=', $startDate);
            }
            
            if ($endDate) {
                $query->where('created_at', '<=', $endDate);
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
                        ->selectRaw('students.grade_id, AVG(health_records.height) as avg_height')
                        ->when($startDate, fn($q) => $q->where('health_records.created_at', '>=', $startDate))
                        ->when($endDate, fn($q) => $q->where('health_records.created_at', '<=', $endDate))
                        ->groupBy('students.grade_id')
                        ->orderBy('students.grade_id')
                        ->get()
                        ->mapWithKeys(fn($item) => [$item->grade_id => round($item->avg_height, 2)])
                        ->toArray(),
                    'weight_by_grade' => HealthRecord::join('students', 'health_records.student_id', '=', 'students.id')
                        ->selectRaw('students.grade_id, AVG(health_records.weight) as avg_weight')
                        ->when($startDate, fn($q) => $q->where('health_records.created_at', '>=', $startDate))
                        ->when($endDate, fn($q) => $q->where('health_records.created_at', '<=', $endDate))
                        ->groupBy('students.grade_id')
                        ->orderBy('students.grade_id')
                        ->get()
                        ->mapWithKeys(fn($item) => [$item->grade_id => round($item->avg_weight, 2)])
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

    /**
     * 学年別平均データを取得
     */
    public function gradeAverages(): JsonResponse
    {
        try {
            $gradeAverages = HealthRecord::join('students', 'health_records.student_id', '=', 'students.id')
                ->selectRaw('
                    students.grade_id,
                    COUNT(*) as count,
                    AVG(health_records.height) as avg_height,
                    AVG(health_records.weight) as avg_weight,
                    AVG(health_records.weight / (health_records.height/100 * health_records.height/100)) as avg_bmi,
                    AVG(CASE 
                        WHEN health_records.vision_left IS NOT NULL AND health_records.vision_right IS NOT NULL 
                        THEN (health_records.vision_left + health_records.vision_right) / 2
                        WHEN health_records.vision_left_corrected IS NOT NULL AND health_records.vision_right_corrected IS NOT NULL
                        THEN (health_records.vision_left_corrected + health_records.vision_right_corrected) / 2
                        ELSE NULL
                    END) as avg_vision
                ')
                ->where('health_records.year', now()->year)
                ->whereNotNull('students.grade_id')
                ->groupBy('students.grade_id')
                ->orderBy('students.grade_id')
                ->get()
                ->map(function($item) {
                    return [
                        'grade' => $item->grade_id,
                        'student_count' => $item->count,
                        'avg_height' => $item->avg_height ? round($item->avg_height, 1) : null,
                        'avg_weight' => $item->avg_weight ? round($item->avg_weight, 1) : null,
                        'avg_bmi' => $item->avg_bmi ? round($item->avg_bmi, 1) : null,
                        'avg_vision' => $item->avg_vision ? round($item->avg_vision, 1) : null,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $gradeAverages,
                'message' => '学年別平均データを取得しました'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '学年別平均データの取得に失敗しました',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * クラス別平均データを取得
     */
    public function classAverages(): JsonResponse
    {
        try {
            $classAverages = HealthRecord::join('students', 'health_records.student_id', '=', 'students.id')
                ->join('school_classes', 'students.class_id', '=', 'school_classes.class_id')
                ->selectRaw('
                    school_classes.class_id,
                    school_classes.grade,
                    school_classes.class_name,
                    COUNT(*) as count,
                    AVG(health_records.height) as avg_height,
                    AVG(health_records.weight) as avg_weight,
                    AVG(health_records.weight / (health_records.height/100 * health_records.height/100)) as avg_bmi,
                    AVG(CASE 
                        WHEN health_records.vision_left IS NOT NULL AND health_records.vision_right IS NOT NULL 
                        THEN (health_records.vision_left + health_records.vision_right) / 2
                        WHEN health_records.vision_left_corrected IS NOT NULL AND health_records.vision_right_corrected IS NOT NULL
                        THEN (health_records.vision_left_corrected + health_records.vision_right_corrected) / 2
                        ELSE NULL
                    END) as avg_vision
                ')
                ->where('health_records.year', now()->year)
                ->groupBy('school_classes.class_id', 'school_classes.grade', 'school_classes.class_name')
                ->orderBy('school_classes.grade')
                ->orderBy('school_classes.class_name')
                ->get()
                ->map(function($item) {
                    return [
                        'class_id' => $item->class_id,
                        'grade' => $item->grade,
                        'class_name' => $item->class_name,
                        'student_count' => $item->count,
                        'avg_height' => $item->avg_height ? round($item->avg_height, 1) : null,
                        'avg_weight' => $item->avg_weight ? round($item->avg_weight, 1) : null,
                        'avg_bmi' => $item->avg_bmi ? round($item->avg_bmi, 1) : null,
                        'avg_vision' => $item->avg_vision ? round($item->avg_vision, 1) : null,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $classAverages,
                'message' => 'クラス別平均データを取得しました'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'クラス別平均データの取得に失敗しました',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}