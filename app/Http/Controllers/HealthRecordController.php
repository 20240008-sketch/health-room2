<?php

namespace App\Http\Controllers;

use App\Models\HealthRecord;
use App\Models\Student;
use App\Http\Requests\HealthRecordRequest;
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
                'class_id' => 'required|exists:school_classes,id',
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
            
            // Set document information
            $pdf->SetCreator('Health Management System');
            $pdf->SetAuthor('School');
            $pdf->SetTitle('健康記録統計レポート');
            $pdf->SetSubject('健康統計');
            
            // Set default header data
            $pdf->SetHeaderData('', 0, '健康記録統計レポート', $academicYear . '年度');
            
            // Set header and footer fonts
            $pdf->setHeaderFont(['kozgopromedium', '', 10]);
            $pdf->setFooterFont(['kozgopromedium', '', 8]);
            
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
            $pdf->SetFont('kozgopromedium', '', 10);
            
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

            // PDFを生成
            $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false); // 横向き
            
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
            $pdf->SetFont('kozminproregular', '', 9);
            
            // ページ追加
            $pdf->AddPage();
            
            // タイトル
            $pdf->SetFont('kozminproregular', 'B', 14);
            $pdf->Cell(0, 8, '健康記録一覧', 0, 1, 'C');
            $pdf->Ln(3);
            
            // 出力日時
            $pdf->SetFont('kozminproregular', '', 8);
            $pdf->Cell(0, 5, '出力日時: ' . date('Y年m月d日 H:i'), 0, 1, 'R');
            $pdf->Ln(2);
            
            // フィルター情報
            if ($request->has('academic_year') || $request->has('class_id')) {
                $pdf->SetFont('kozminproregular', '', 8);
                $filterText = '絞り込み: ';
                $filters = [];
                if ($request->has('academic_year')) $filters[] = $request->academic_year . '年度';
                if ($request->has('class_id')) $filters[] = 'クラス指定あり';
                $pdf->Cell(0, 5, $filterText . implode(', ', $filters), 0, 1, 'L');
                $pdf->Ln(2);
            }
            
            // テーブルヘッダー
            $pdf->SetFont('kozminproregular', 'B', 8);
            $pdf->SetFillColor(230, 230, 230);
            $pdf->Cell(20, 7, '測定日', 1, 0, 'C', true);
            $pdf->Cell(35, 7, 'クラス', 1, 0, 'C', true);
            $pdf->Cell(25, 7, '出席番号', 1, 0, 'C', true);
            $pdf->Cell(40, 7, '氏名', 1, 0, 'C', true);
            $pdf->Cell(20, 7, '身長(cm)', 1, 0, 'C', true);
            $pdf->Cell(20, 7, '体重(kg)', 1, 0, 'C', true);
            $pdf->Cell(30, 7, '視力(左/右)', 1, 0, 'C', true);
            $pdf->Cell(15, 7, 'BMI', 1, 0, 'C', true);
            $pdf->Cell(70, 7, '備考', 1, 1, 'C', true);
            
            // テーブルデータ
            $pdf->SetFont('kozminproregular', '', 7);
            foreach ($records as $record) {
                // ページの残りスペースをチェック
                if ($pdf->GetY() > 180) {
                    $pdf->AddPage();
                    // ヘッダーを再表示
                    $pdf->SetFont('kozminproregular', 'B', 8);
                    $pdf->SetFillColor(230, 230, 230);
                    $pdf->Cell(20, 7, '測定日', 1, 0, 'C', true);
                    $pdf->Cell(35, 7, 'クラス', 1, 0, 'C', true);
                    $pdf->Cell(25, 7, '出席番号', 1, 0, 'C', true);
                    $pdf->Cell(40, 7, '氏名', 1, 0, 'C', true);
                    $pdf->Cell(20, 7, '身長(cm)', 1, 0, 'C', true);
                    $pdf->Cell(20, 7, '体重(kg)', 1, 0, 'C', true);
                    $pdf->Cell(30, 7, '視力(左/右)', 1, 0, 'C', true);
                    $pdf->Cell(15, 7, 'BMI', 1, 0, 'C', true);
                    $pdf->Cell(70, 7, '備考', 1, 1, 'C', true);
                    $pdf->SetFont('kozminproregular', '', 7);
                }
                
                $measuredDate = $record->created_at ? $record->created_at->format('Y/m/d') : '-';
                $className = $record->student->schoolClass->class_name ?? '-';
                $studentNumber = $record->student->student_number ?? '-';
                $studentName = $record->student->name ?? '-';
                $height = $record->height ?? '-';
                $weight = $record->weight ?? '-';
                
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
                
                $vision = $visionLeft . '/' . $visionRight;
                
                $bmi = $record->bmi ?? '-';
                $notes = $record->notes ? mb_substr($record->notes, 0, 30) : '';
                
                $pdf->Cell(20, 6, $measuredDate, 1, 0, 'C');
                $pdf->Cell(35, 6, $className, 1, 0, 'L');
                $pdf->Cell(25, 6, $studentNumber, 1, 0, 'C');
                $pdf->Cell(40, 6, $studentName, 1, 0, 'L');
                $pdf->Cell(20, 6, $height, 1, 0, 'C');
                $pdf->Cell(20, 6, $weight, 1, 0, 'C');
                $pdf->Cell(30, 6, $vision, 1, 0, 'C');
                $pdf->Cell(15, 6, $bmi, 1, 0, 'C');
                $pdf->Cell(70, 6, $notes, 1, 1, 'L');
            }
            
            // 統計情報
            $pdf->Ln(5);
            $pdf->SetFont('kozminproregular', 'B', 9);
            $pdf->Cell(0, 6, '統計情報', 0, 1, 'L');
            $pdf->SetFont('kozminproregular', '', 8);
            $pdf->Cell(0, 5, '総記録数: ' . $records->count() . '件', 0, 1, 'L');
            
            if ($records->count() > 0) {
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
}
