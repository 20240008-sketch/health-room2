<?php

namespace App\Http\Controllers;

use App\Models\HealthRecord;
use App\Models\Student;
use App\Http\Requests\HealthRecordRequest;
use App\Support\PdfFontHelper;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use TCPDF;

class HealthRecordController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = HealthRecord::with(['student.schoolClass']);

            // 生徒IDフィルタ
            if ($request->has('student_id') && !empty($request->student_id)) {
                $query->where('student_id', $request->student_id);
            }

            // 年度フィルタ
            if ($request->has('year') && !empty($request->year)) {
                $query->where('year', $request->year);
            }

            // 学年フィルタ
            if ($request->has('grade') && !empty($request->grade)) {
                $query->whereHas('student', function ($q) use ($request) {
                    $q->where('grade_id', $request->grade);
                });
            }

            // クラスフィルタ
            if ($request->has('class_id') && !empty($request->class_id)) {
                $query->whereHas('student', function ($q) use ($request) {
                    $q->where('class_id', $request->class_id);
                });
            }

            // ソート
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            
            // measured_date は created_at にマッピング
            if ($sortBy === 'measured_date') {
                $sortBy = 'created_at';
            }
            
            $query->orderBy($sortBy, $sortOrder);

            // ページネーションまたは全件取得
            $perPage = $request->get('per_page', 1000);
            
            if ($perPage > 500) {
                // 大量データの場合は全件取得
                $healthRecords = $query->get();
                $data = $healthRecords;
            } else {
                $healthRecords = $query->paginate($perPage);
                $data = $healthRecords->items();
            }

            return response()->json([
                'success' => true,
                'data' => $data,
                'pagination' => $perPage > 500 ? null : [
                    'current_page' => $healthRecords->currentPage(),
                    'last_page' => $healthRecords->lastPage(),
                    'per_page' => $healthRecords->perPage(),
                    'total' => $healthRecords->total(),
                ],
                'message' => '健康記録一覧を取得しました'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '健康記録一覧の取得に失敗しました',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get health records by student ID
     */
    public function getByStudent(string $studentId): JsonResponse
    {
        try {
            $student = Student::findOrFail($studentId);
            
            $healthRecords = HealthRecord::with('student')
                                        ->where('student_id', $studentId)
                                        ->orderBy('year', 'desc')
                                        ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'student' => $student,
                    'health_records' => $healthRecords
                ],
                'message' => '生徒の健康記録一覧を取得しました'
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => '指定された生徒が見つかりません'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '健康記録一覧の取得に失敗しました',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): JsonResponse
    {
        try {
            $studentId = $request->get('student_id');
            $student = null;

            if ($studentId) {
                $student = Student::with('schoolClass')->findOrFail($studentId);
            }

            $currentYear = date('Y');
            $availableYears = range($currentYear - 5, $currentYear + 1);

            return response()->json([
                'success' => true,
                'data' => [
                    'student' => $student,
                    'available_years' => $availableYears,
                    'current_year' => $currentYear
                ],
                'message' => '健康記録作成フォームデータを取得しました'
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => '指定された生徒が見つかりません'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'フォームデータの取得に失敗しました',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(HealthRecordRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            $healthRecord = HealthRecord::create($validated);
            $healthRecord->load('student.schoolClass');

            return response()->json([
                'success' => true,
                'data' => $healthRecord,
                'message' => '健康記録を登録しました'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '健康記録の登録に失敗しました',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $healthRecord = HealthRecord::with('student.schoolClass')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $healthRecord,
                'message' => '健康記録詳細を取得しました'
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => '指定された健康記録が見つかりません'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '健康記録詳細の取得に失敗しました',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): JsonResponse
    {
        try {
            $healthRecord = HealthRecord::with('student.schoolClass')->findOrFail($id);
            
            $currentYear = date('Y');
            $availableYears = range($currentYear - 5, $currentYear + 1);

            return response()->json([
                'success' => true,
                'data' => [
                    'health_record' => $healthRecord,
                    'available_years' => $availableYears
                ],
                'message' => '健康記録編集フォームデータを取得しました'
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => '指定された健康記録が見つかりません'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '編集フォームデータの取得に失敗しました',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(HealthRecordRequest $request, string $id): JsonResponse
    {
        try {
            $healthRecord = HealthRecord::findOrFail($id);
            $validated = $request->validated();
            $healthRecord->update($validated);
            $healthRecord->load('student.schoolClass');

            return response()->json([
                'success' => true,
                'data' => $healthRecord,
                'message' => '健康記録を更新しました'
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => '指定された健康記録が見つかりません'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '健康記録の更新に失敗しました',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $healthRecord = HealthRecord::with('student')->findOrFail($id);
            $studentName = $healthRecord->student->name;
            $year = $healthRecord->year;
            
            $healthRecord->delete();

            return response()->json([
                'success' => true,
                'message' => "「{$studentName}」の{$year}年度健康記録を削除しました"
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => '指定された健康記録が見つかりません'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '健康記録の削除に失敗しました',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk create health records.
     */
    public function bulkStore(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'records' => 'required|array',
                'records.*.student_id' => 'required|string|exists:students,student_id',
                'records.*.year' => 'required|integer|min:2020|max:2030',
                'records.*.height' => 'nullable|numeric|min:50|max:250',
                'records.*.weight' => 'nullable|numeric|min:10|max:200',
                'records.*.vision_left' => 'nullable|string|in:A,B,C,D',
                'records.*.vision_right' => 'nullable|string|in:A,B,C,D',
                'records.*.vision_left_corrected' => 'nullable|string|in:A,B,C,D',
                'records.*.vision_right_corrected' => 'nullable|string|in:A,B,C,D',
                'records.*.ophthalmology_result' => 'nullable|string|max:1000',
                'records.*.otolaryngology_result' => 'nullable|string|max:1000',
                'records.*.internal_medicine_result' => 'nullable|string|max:1000',
                'records.*.hearing_test_result' => 'nullable|string|max:1000',
                'records.*.tuberculosis_test_result' => 'nullable|string|max:1000',
                'records.*.urine_test_result' => 'nullable|string|max:1000',
                'records.*.ecg_result' => 'nullable|string|max:1000',
                'records.*.notes' => 'nullable|string|max:500'
            ]);

            $createdRecords = [];
            foreach ($request->records as $recordData) {
                // BMIを自動計算
                if (isset($recordData['height']) && isset($recordData['weight']) && 
                    $recordData['height'] && $recordData['weight']) {
                    $heightInM = $recordData['height'] / 100;
                    $recordData['bmi'] = round($recordData['weight'] / ($heightInM * $heightInM), 1);
                }

                // 既存のレコードをチェック（student_id と year の組み合わせでユニーク）
                $existingRecord = HealthRecord::where('student_id', $recordData['student_id'])
                                             ->where('year', $recordData['year'])
                                             ->first();

                if ($existingRecord) {
                    // 既存レコードを更新（空でないフィールドのみ）
                    $updateData = array_filter($recordData, function($value) {
                        return $value !== null && $value !== '';
                    });
                    $existingRecord->update($updateData);
                    $record = $existingRecord;
                } else {
                    // 新しいレコードを作成
                    $record = HealthRecord::create($recordData);
                }
                
                $record->load(['student.schoolClass']);
                $createdRecords[] = $record;
            }

            return response()->json([
                'success' => true,
                'data' => $createdRecords,
                'message' => count($createdRecords) . '件の健康記録を一括処理しました'
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => '入力データに問題があります',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '健康記録の一括登録に失敗しました',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get peer comparison data.
     */
    public function peerComparison(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'class_id' => 'required|string',
                'year' => 'required|integer'
            ]);

            $classId = $request->class_id;
            $year = $request->year;

            // クラスの最新健康記録を取得
            $records = HealthRecord::whereHas('student', function ($query) use ($classId) {
                $query->where('class_id', $classId);
            })
            ->where('year', $year)
            ->whereNotNull('height')
            ->whereNotNull('weight')
            ->with(['student'])
            ->get();

            if ($records->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'data' => null,
                    'message' => '比較対象のデータが見つかりません'
                ]);
            }

            // 平均値を計算
            $averageHeight = $records->avg('height');
            $averageWeight = $records->avg('weight');
            $averageBMI = $records->avg('bmi');

            // 性別ごとの平均も計算
            $maleRecords = $records->filter(function ($record) {
                return $record->student->sex === 'male';
            });
            $femaleRecords = $records->filter(function ($record) {
                return $record->student->sex === 'female';
            });

            $comparison = [
                'class_id' => $classId,
                'year' => $year,
                'total_students' => $records->count(),
                'averageHeight' => round($averageHeight, 1),
                'averageWeight' => round($averageWeight, 1),
                'averageBMI' => round($averageBMI, 1),
                'by_gender' => [
                    'male' => [
                        'count' => $maleRecords->count(),
                        'averageHeight' => $maleRecords->count() > 0 ? round($maleRecords->avg('height'), 1) : null,
                        'averageWeight' => $maleRecords->count() > 0 ? round($maleRecords->avg('weight'), 1) : null,
                        'averageBMI' => $maleRecords->count() > 0 ? round($maleRecords->avg('bmi'), 1) : null,
                    ],
                    'female' => [
                        'count' => $femaleRecords->count(),
                        'averageHeight' => $femaleRecords->count() > 0 ? round($femaleRecords->avg('height'), 1) : null,
                        'averageWeight' => $femaleRecords->count() > 0 ? round($femaleRecords->avg('weight'), 1) : null,
                        'averageBMI' => $femaleRecords->count() > 0 ? round($femaleRecords->avg('bmi'), 1) : null,
                    ]
                ]
            ];

            return response()->json([
                'success' => true,
                'data' => $comparison,
                'message' => '同級生比較データを取得しました'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'パラメータが正しくありません',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '同級生比較データの取得に失敗しました',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get health record statistics.
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $query = HealthRecord::with(['student.schoolClass']);

            // フィルタリング
            if ($request->has('academic_year') && !empty($request->academic_year)) {
                $query->where('year', $request->academic_year);
            }

            if ($request->has('grade') && !empty($request->grade)) {
                $query->whereHas('student', function ($q) use ($request) {
                    $q->where('grade_id', $request->grade);
                });
            }

            if ($request->has('class_id') && !empty($request->class_id)) {
                $query->whereHas('student', function ($q) use ($request) {
                    $q->where('class_id', $request->class_id);
                });
            }

            $records = $query->whereNotNull('height')
                           ->whereNotNull('weight')
                           ->get();

            if ($records->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'data' => [
                        'total_records' => 0,
                        'averages' => [
                            'height' => 0,
                            'weight' => 0,
                            'bmi' => 0,
                            'vision_left' => 0,
                            'vision_right' => 0,
                        ],
                        'ranges' => [
                            'height' => ['min' => 0, 'max' => 0],
                            'weight' => ['min' => 0, 'max' => 0],
                            'bmi' => ['min' => 0, 'max' => 0]
                        ],
                        'bmi_distribution' => [
                            'underweight' => 0,
                            'normal' => 0,
                            'overweight' => 0,
                            'obese' => 0
                        ],
                        'bmi_percentages' => [
                            'underweight' => 0,
                            'normal' => 0,
                            'overweight' => 0,
                            'obese' => 0
                        ],
                        'by_grade' => [],
                        'by_class' => [],
                    ],
                    'message' => '統計データが見つかりません'
                ]);
            }

            // BMI分類ごとの統計
            $bmiCategories = [
                'underweight' => 0, // BMI < 18.5
                'normal' => 0,      // 18.5 <= BMI < 25
                'overweight' => 0,  // 25 <= BMI < 30
                'obese' => 0        // BMI >= 30
            ];

            foreach ($records as $record) {
                if ($record->bmi < 18.5) {
                    $bmiCategories['underweight']++;
                } elseif ($record->bmi < 25) {
                    $bmiCategories['normal']++;
                } elseif ($record->bmi < 30) {
                    $bmiCategories['overweight']++;
                } else {
                    $bmiCategories['obese']++;
                }
            }

            // 学年別統計
            $gradeStats = $records->groupBy(function($record) {
                return $record->student->grade_id ?? 'unknown';
            })->map(function($gradeRecords, $gradeId) {
                return [
                    'grade' => $gradeId,
                    'grade_name' => $gradeId . '年生',
                    'count' => $gradeRecords->count(),
                    'avg_height' => round($gradeRecords->avg('height'), 1),
                    'avg_weight' => round($gradeRecords->avg('weight'), 1),
                    'avg_bmi' => round($gradeRecords->avg(function($record) {
                        return $record->bmi;
                    }), 1),
                    'avg_vision_left' => round($gradeRecords->whereNotNull('vision_left')->avg('vision_left'), 2),
                    'avg_vision_right' => round($gradeRecords->whereNotNull('vision_right')->avg('vision_right'), 2),
                ];
            })->sortBy('grade')->values();

            // クラス別統計
            $classStats = $records->groupBy(function($record) {
                return $record->student->class_id ?? 'unknown';
            })->map(function($classRecords, $classId) {
                $firstRecord = $classRecords->first();
                $className = $firstRecord->student->schoolClass->class_name ?? $classId;
                $grade = $firstRecord->student->grade_id ?? 'unknown';
                
                return [
                    'class_id' => $classId,
                    'class_name' => $className,
                    'grade' => $grade,
                    'count' => $classRecords->count(),
                    'avg_height' => round($classRecords->avg('height'), 1),
                    'avg_weight' => round($classRecords->avg('weight'), 1),
                    'avg_bmi' => round($classRecords->avg(function($record) {
                        return $record->bmi;
                    }), 1),
                    'avg_vision_left' => round($classRecords->whereNotNull('vision_left')->avg('vision_left'), 2),
                    'avg_vision_right' => round($classRecords->whereNotNull('vision_right')->avg('vision_right'), 2),
                ];
            })->sortBy('grade')->values();

            $statistics = [
                'total_records' => $records->count(),
                'averages' => [
                    'height' => round($records->avg('height'), 1),
                    'weight' => round($records->avg('weight'), 1),
                    'bmi' => round($records->avg('bmi'), 1),
                    'vision_left' => round($records->whereNotNull('vision_left')->avg('vision_left'), 2),
                    'vision_right' => round($records->whereNotNull('vision_right')->avg('vision_right'), 2),
                ],
                'ranges' => [
                    'height' => [
                        'min' => $records->min('height'),
                        'max' => $records->max('height')
                    ],
                    'weight' => [
                        'min' => $records->min('weight'),
                        'max' => $records->max('weight')
                    ],
                    'bmi' => [
                        'min' => round($records->min('bmi'), 1),
                        'max' => round($records->max('bmi'), 1)
                    ]
                ],
                'bmi_distribution' => $bmiCategories,
                'bmi_percentages' => [
                    'underweight' => round(($bmiCategories['underweight'] / $records->count()) * 100, 1),
                    'normal' => round(($bmiCategories['normal'] / $records->count()) * 100, 1),
                    'overweight' => round(($bmiCategories['overweight'] / $records->count()) * 100, 1),
                    'obese' => round(($bmiCategories['obese'] / $records->count()) * 100, 1)
                ],
                'by_grade' => $gradeStats,
                'by_class' => $classStats,
            ];

            return response()->json([
                'success' => true,
                'data' => $statistics,
                'message' => '健康記録統計を取得しました'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '統計データの取得に失敗しました',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export health record statistics to PDF
     */
    public function statisticsPdf(Request $request)
    {
        try {
            $academicYear = $request->input('academic_year', date('Y'));
            $grade = $request->input('grade');
            $classId = $request->input('class_id');

            // Get records with filters
            $query = HealthRecord::with(['student.schoolClass'])
                ->where('year', $academicYear);

            if ($grade) {
                $query->whereHas('student', function ($q) use ($grade) {
                    $q->where('grade_id', $grade);
                });
            }

            if ($classId) {
                $query->whereHas('student', function ($q) use ($classId) {
                    $q->where('class_id', $classId);
                });
            }

            $records = $query->get();

            if ($records->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'データが見つかりません'
                ], 404);
            }

            // Calculate statistics (same as statistics method)
            $bmiCategories = [
                'underweight' => $records->where('bmi', '<', 18.5)->count(),
                'normal' => $records->whereBetween('bmi', [18.5, 24.9])->count(),
                'overweight' => $records->whereBetween('bmi', [25, 29.9])->count(),
                'obese' => $records->where('bmi', '>=', 30)->count()
            ];

            // Grade statistics
            $gradeStats = $records->groupBy('student.grade_id')->map(function ($gradeRecords, $gradeId) {
                return [
                    'grade' => $gradeId,
                    'count' => $gradeRecords->count(),
                    'avg_height' => round($gradeRecords->avg('height'), 1),
                    'avg_weight' => round($gradeRecords->avg('weight'), 1),
                    'avg_bmi' => round($gradeRecords->avg('bmi'), 1),
                    'avg_vision_left' => round($gradeRecords->whereNotNull('vision_left')->avg('vision_left'), 2),
                    'avg_vision_right' => round($gradeRecords->whereNotNull('vision_right')->avg('vision_right'), 2),
                ];
            })->sortBy('grade')->values();

            // Class statistics
            $classStats = $records->groupBy('student.class_id')->map(function ($classRecords, $classId) {
                $firstStudent = $classRecords->first()->student;
                return [
                    'class_id' => $classId,
                    'class_name' => $firstStudent->schoolClass->name ?? '不明',
                    'grade' => $firstStudent->grade_id ?? 0,
                    'count' => $classRecords->count(),
                    'avg_height' => round($classRecords->avg('height'), 1),
                    'avg_weight' => round($classRecords->avg('weight'), 1),
                    'avg_bmi' => round($classRecords->avg('bmi'), 1),
                    'avg_vision_left' => round($classRecords->whereNotNull('vision_left')->avg('vision_left'), 2),
                    'avg_vision_right' => round($classRecords->whereNotNull('vision_right')->avg('vision_right'), 2),
                ];
            })->sortBy('grade')->values();

            $statistics = [
                'academic_year' => $academicYear,
                'grade' => $grade,
                'class_id' => $classId,
                'total_records' => $records->count(),
                'averages' => [
                    'height' => round($records->avg('height'), 1),
                    'weight' => round($records->avg('weight'), 1),
                    'bmi' => round($records->avg('bmi'), 1),
                    'vision_left' => round($records->whereNotNull('vision_left')->avg('vision_left'), 2),
                    'vision_right' => round($records->whereNotNull('vision_right')->avg('vision_right'), 2),
                ],
                'ranges' => [
                    'height' => [
                        'min' => $records->min('height'),
                        'max' => $records->max('height')
                    ],
                    'weight' => [
                        'min' => $records->min('weight'),
                        'max' => $records->max('weight')
                    ],
                    'bmi' => [
                        'min' => round($records->min('bmi'), 1),
                        'max' => round($records->max('bmi'), 1)
                    ]
                ],
                'bmi_distribution' => $bmiCategories,
                'bmi_percentages' => [
                    'underweight' => round(($bmiCategories['underweight'] / $records->count()) * 100, 1),
                    'normal' => round(($bmiCategories['normal'] / $records->count()) * 100, 1),
                    'overweight' => round(($bmiCategories['overweight'] / $records->count()) * 100, 1),
                    'obese' => round(($bmiCategories['obese'] / $records->count()) * 100, 1)
                ],
                'by_grade' => $gradeStats,
                'by_class' => $classStats,
            ];

            // Generate PDF using TCPDF
            $pdf = app('TCPDF');
            $fontName = PdfFontHelper::applyFont($pdf, 10, 10, 8);
            
            // Set document information
            $pdf->SetCreator('Health Management System');
            $pdf->SetAuthor('School');
            $pdf->SetTitle('健康記録統計レポート');
            $pdf->SetSubject('健康統計');
            
            // Set default header data
            $pdf->SetHeaderData('', 0, '健康記録統計レポート', $academicYear . '年度');
            
            // Set header and footer fonts
            $pdf->setHeaderFont([$fontName, '', 10]);
            $pdf->setFooterFont([$fontName, '', 8]);
            
            // Set default monospaced font
            $pdf->SetDefaultMonospacedFont('courier');
            
            // Set margins
            $pdf->SetMargins(15, 27, 15);
            $pdf->SetHeaderMargin(5);
            $pdf->SetFooterMargin(10);
            
            // Set auto page breaks
            $pdf->SetAutoPageBreak(TRUE, 25);
            
            // Set image scale factor
            $pdf->setImageScale(1.25);
            
            // Set font
            $pdf->SetFont($fontName, '', 10);
            
            // Add a page
            $pdf->AddPage();
            
            // Generate HTML content
            $html = view('pdfs.health-statistics-tcpdf', ['statistics' => $statistics])->render();
            
            // Output the HTML content
            $pdf->writeHTML($html, true, false, true, false, '');
            
            // Close and output PDF document
            $filename = 'health_statistics_' . $academicYear;
            if ($grade) {
                $filename .= '_grade' . $grade;
            }
            if ($classId) {
                $filename .= '_class' . $classId;
            }
            $filename .= '.pdf';
            
            return response($pdf->Output($filename, 'S'))
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');

        } catch (\Exception $e) {
            Log::error('Statistics PDF generation error: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'PDF生成に失敗しました',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export health records to PDF
     */
    public function exportPdf(Request $request)
    {
        try {
            // Get selected columns (default to basic measurements)
            $selectedColumns = $request->input('columns', ['height', 'weight', 'vision', 'bmi']);
            
            // Get filtered records
            $query = HealthRecord::with(['student.schoolClass']);

            // Apply filters (same as index method)
            if ($request->has('academic_year') && !empty($request->academic_year)) {
                $query->where('year', $request->academic_year);
            }

            if ($request->has('grade') && !empty($request->grade)) {
                $query->whereHas('student', function ($q) use ($request) {
                    $q->where('grade_id', $request->grade);
                });
            }

            if ($request->has('class_id') && !empty($request->class_id)) {
                $query->whereHas('student', function ($q) use ($request) {
                    $q->where('class_id', $request->class_id);
                });
            }

            if ($request->has('search') && !empty($request->search)) {
                $query->whereHas('student', function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%')
                      ->orWhere('student_number', 'like', '%' . $request->search . '%');
                });
            }

            // Date filters
            if ($request->has('year') && !empty($request->year)) {
                $query->whereYear('created_at', $request->year);
            }
            if ($request->has('month') && !empty($request->month)) {
                $query->whereMonth('created_at', $request->month);
            }
            if ($request->has('day') && !empty($request->day)) {
                $query->whereDay('created_at', $request->day);
            }

            $records = $query->orderBy('created_at', 'desc')->get();

            // 視力をABCDグレードに変換するヘルパー関数
            $getVisionGrade = function($vision) {
                if (!$vision) return '-';
                $v = floatval($vision);
                if ($v >= 1.0) return 'A';
                if ($v >= 0.7) return 'B';
                if ($v >= 0.3) return 'C';
                return 'D';
            };
            
            // 検査結果の概要を取得
            $getExamSummary = function($record, $examType) {
                try {
                    switch ($examType) {
                        case 'ophthalmology':
                            if ($record->ophthalmology_exam_result) {
                                return mb_substr($record->ophthalmology_exam_result, 0, 20);
                            }
                            return '-';
                        
                        case 'otolaryngology':
                            if ($record->otolaryngology_result) {
                                $items = json_decode($record->otolaryngology_result, true);
                                if ($items && count($items) > 0 && isset($items[0]['exam_result'])) {
                                    return mb_substr($items[0]['exam_result'], 0, 20);
                                }
                            }
                            return '-';
                        
                        case 'internal_medicine':
                            if ($record->internal_medicine_result) {
                                $items = json_decode($record->internal_medicine_result, true);
                                if ($items && count($items) > 0 && isset($items[0]['exam_result'])) {
                                    return mb_substr($items[0]['exam_result'], 0, 20);
                                }
                            }
                            return '-';
                        
                        case 'hearing_test':
                            if ($record->hearing_test_result) {
                                $items = json_decode($record->hearing_test_result, true);
                                if ($items && count($items) > 0 && isset($items[0]['exam_result'])) {
                                    return mb_substr($items[0]['exam_result'], 0, 20);
                                }
                            }
                            return '-';
                        
                        case 'tuberculosis_test':
                            if ($record->tuberculosis_test_result) {
                                $items = json_decode($record->tuberculosis_test_result, true);
                                if ($items && count($items) > 0 && isset($items[0]['exam_result'])) {
                                    return mb_substr($items[0]['exam_result'], 0, 20);
                                }
                            }
                            return '-';
                        
                        case 'urine_test':
                            if ($record->urine_test_result) {
                                $items = json_decode($record->urine_test_result, true);
                                if ($items && count($items) > 0 && isset($items[0]['exam_result'])) {
                                    return mb_substr($items[0]['exam_result'], 0, 20);
                                }
                            }
                            return '-';
                        
                        case 'ecg':
                            if ($record->ecg_result) {
                                $items = json_decode($record->ecg_result, true);
                                if ($items && count($items) > 0 && isset($items[0]['exam_result'])) {
                                    return mb_substr($items[0]['exam_result'], 0, 20);
                                }
                            }
                            return '-';
                        
                        default:
                            return '-';
                    }
                } catch (\Exception $e) {
                    return '-';
                }
            };

            // PDFを生成
            $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false); // 横向き
            $fontName = PdfFontHelper::applyFont($pdf, 9);
            
            // PDFの基本設定
            $pdf->SetCreator('Health Room System');
            $pdf->SetAuthor('School');
            $pdf->SetTitle('健康記録一覧');
            $pdf->SetSubject('健康記録一覧');
            
            // ヘッダー・フッターを削除
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            
            // マージン設定
            $pdf->SetMargins(10, 10, 10);
            $pdf->SetAutoPageBreak(true, 10);
            
            // フォント設定（日本語対応）
            $pdf->SetFont($fontName, '', 9);
            
            // ページ追加
            $pdf->AddPage();
            
            // タイトル
            $pdf->SetFont($fontName, '', 14);
            $pdf->Cell(0, 8, '健康記録一覧', 0, 1, 'C');
            $pdf->Ln(3);
            
            // 出力日時
            $pdf->SetFont($fontName, '', 8);
            $pdf->Cell(0, 5, '出力日時: ' . date('Y年m月d日 H:i'), 0, 1, 'R');
            $pdf->Ln(2);
            
            // フィルター情報
            if ($request->has('academic_year') || $request->has('class_id')) {
                $pdf->SetFont($fontName, '', 8);
                $filterText = '絞り込み: ';
                $filters = [];
                if ($request->has('academic_year')) $filters[] = $request->academic_year . '年度';
                if ($request->has('class_id')) $filters[] = 'クラス指定あり';
                $pdf->Cell(0, 5, $filterText . implode(', ', $filters), 0, 1, 'L');
                $pdf->Ln(2);
            }
            
            // カラム定義（幅とラベル）
            $columnConfig = [
                'measured_date' => ['width' => 20, 'label' => '測定日'],
                'class' => ['width' => 30, 'label' => 'クラス'],
                'student_number' => ['width' => 20, 'label' => '出席番号'],
                'name' => ['width' => 35, 'label' => '氏名'],
                'height' => ['width' => 18, 'label' => '身長(cm)'],
                'weight' => ['width' => 18, 'label' => '体重(kg)'],
                'vision' => ['width' => 22, 'label' => '視力(左/右)'],
                'bmi' => ['width' => 15, 'label' => 'BMI'],
                'ophthalmology' => ['width' => 25, 'label' => '眼科検診'],
                'otolaryngology' => ['width' => 25, 'label' => '耳鼻科検診'],
                'internal_medicine' => ['width' => 25, 'label' => '内科検診'],
                'hearing_test' => ['width' => 25, 'label' => '聴力検査'],
                'tuberculosis_test' => ['width' => 25, 'label' => '結核検査'],
                'urine_test' => ['width' => 25, 'label' => '尿検査'],
                'ecg' => ['width' => 25, 'label' => '心電図'],
            ];
            
            // 表示する列を構築（基本列 + 選択された列）
            $displayColumns = ['measured_date', 'class', 'student_number', 'name'];
            foreach ($selectedColumns as $col) {
                if (isset($columnConfig[$col])) {
                    $displayColumns[] = $col;
                }
            }
            
            // テーブルヘッダーを描画
            $drawTableHeader = function() use ($pdf, $columnConfig, $displayColumns, $fontName) {
                $pdf->SetFont($fontName, '', 8);
                $pdf->SetFillColor(230, 230, 230);
                foreach ($displayColumns as $col) {
                    $config = $columnConfig[$col];
                    $pdf->Cell($config['width'], 7, $config['label'], 1, 0, 'C', true);
                }
                $pdf->Ln();
            };
            
            // ヘッダー描画
            $drawTableHeader();
            
            // テーブルデータ
            $pdf->SetFont($fontName, '', 7);
            foreach ($records as $record) {
                // ページの残りスペースをチェック
                if ($pdf->GetY() > 180) {
                    $pdf->AddPage();
                    $drawTableHeader();
                    $pdf->SetFont($fontName, '', 7);
                }
                
                // 各列のデータを準備
                $rowData = [];
                
                // 測定日
                $rowData['measured_date'] = $record->created_at ? $record->created_at->format('Y/m/d') : '-';
                
                // クラス
                $rowData['class'] = $record->student->schoolClass->class_name ?? '-';
                
                // 出席番号
                $rowData['student_number'] = $record->student->student_number ?? '-';
                
                // 氏名
                $rowData['name'] = $record->student->name ?? '-';
                
                // 身長
                $rowData['height'] = $record->height ?? '-';
                
                // 体重
                $rowData['weight'] = $record->weight ?? '-';
                
                // 視力（裸眼または矯正）をABCDグレードで表示
                $visionLeft = '-';
                if ($record->vision_left) {
                    $visionLeft = $getVisionGrade($record->vision_left);
                } elseif ($record->vision_left_corrected) {
                    $visionLeft = '矯' . $getVisionGrade($record->vision_left_corrected);
                }
                
                $visionRight = '-';
                if ($record->vision_right) {
                    $visionRight = $getVisionGrade($record->vision_right);
                } elseif ($record->vision_right_corrected) {
                    $visionRight = '矯' . $getVisionGrade($record->vision_right_corrected);
                }
                
                $rowData['vision'] = $visionLeft . '/' . $visionRight;
                
                // BMI
                $rowData['bmi'] = $record->bmi ?? '-';
                
                // 検査結果
                $rowData['ophthalmology'] = $getExamSummary($record, 'ophthalmology');
                $rowData['otolaryngology'] = $getExamSummary($record, 'otolaryngology');
                $rowData['internal_medicine'] = $getExamSummary($record, 'internal_medicine');
                $rowData['hearing_test'] = $getExamSummary($record, 'hearing_test');
                $rowData['tuberculosis_test'] = $getExamSummary($record, 'tuberculosis_test');
                $rowData['urine_test'] = $getExamSummary($record, 'urine_test');
                $rowData['ecg'] = $getExamSummary($record, 'ecg');
                
                // 行を描画
                foreach ($displayColumns as $col) {
                    $config = $columnConfig[$col];
                    $align = in_array($col, ['measured_date', 'student_number', 'height', 'weight', 'vision', 'bmi']) ? 'C' : 'L';
                    $pdf->Cell($config['width'], 6, $rowData[$col] ?? '-', 1, 0, $align);
                }
                $pdf->Ln();
            }
            
            // 統計情報
            $pdf->Ln(5);
            $pdf->SetFont($fontName, '', 9);
            $pdf->Cell(0, 6, '統計情報', 0, 1, 'L');
            $pdf->SetFont($fontName, '', 8);
            $pdf->Cell(0, 5, '総記録数: ' . $records->count() . '件', 0, 1, 'L');
            
            if ($records->count() > 0 && in_array('height', $selectedColumns) && in_array('weight', $selectedColumns) && in_array('bmi', $selectedColumns)) {
                $avgHeight = round($records->avg('height'), 1);
                $avgWeight = round($records->avg('weight'), 1);
                $avgBmi = round($records->avg('bmi'), 1);
                
                $pdf->Cell(0, 5, '平均身長: ' . $avgHeight . 'cm  平均体重: ' . $avgWeight . 'kg  平均BMI: ' . $avgBmi, 0, 1, 'L');
            }
            
            // PDFを出力
            $fileName = '健康記録一覧_' . date('Ymd') . '.pdf';
            
            return response($pdf->Output($fileName, 'S'))
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
                
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'PDF生成に失敗しました',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get student's health records for printing exam results
     */
    public function getPrintResults(string $studentId): JsonResponse
    {
        try {
            $student = Student::with(['schoolClass', 'healthRecords' => function($query) {
                $query->orderBy('year', 'desc')->orderBy('measured_date', 'desc');
            }])->where('student_id', $studentId)->firstOrFail();

            return response()->json([
                'success' => true,
                'data' => [
                    'student' => $student,
                    'health_records' => $student->healthRecords
                ],
                'message' => '印刷用データを取得しました'
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => '指定された生徒が見つかりません'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'データの取得に失敗しました',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
