<?php

namespace App\Http\Controllers;

use App\Models\HealthRecord;
use App\Models\Student;
use App\Http\Requests\HealthRecordRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class HealthRecordController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = HealthRecord::with(['student']);

            // 生徒IDフィルタ
            if ($request->has('student_id') && !empty($request->student_id)) {
                $query->where('student_id', $request->student_id);
            }

            // 年度フィルタ
            if ($request->has('year') && !empty($request->year)) {
                $query->where('year', $request->year);
            }

            // ソート
            $sortBy = $request->get('sort_by', 'measured_date');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // ページネーション
            $perPage = $request->get('per_page', 15);
            $healthRecords = $query->paginate($perPage);

            // シンプルなレスポンスに変換
            $simplifiedData = $healthRecords->getCollection()->map(function ($record) {
                return [
                    'id' => $record->id,
                    'student' => [
                        'name' => $record->student->name ?? '不明',
                        'student_number' => $record->student->student_number ?? '未設定',
                        'class_id' => $record->student->class_id ?? '未設定',
                        'grade_id' => $record->student->grade_id ?? '?',
                    ],
                    'measured_date' => $record->measured_date,
                    'height' => $record->height,
                    'weight' => $record->weight,
                    'bmi' => $record->bmi ? round($record->bmi, 1) : null,
                    'created_at' => $record->created_at->format('Y-m-d'),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $simplifiedData,
                'pagination' => [
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
                'records.*.student_id' => 'required|exists:students,id',
                'records.*.year' => 'required|integer|min:2020|max:2030',
                'records.*.height' => 'nullable|numeric|min:50|max:250',
                'records.*.weight' => 'nullable|numeric|min:10|max:200',
                'records.*.notes' => 'nullable|string|max:500'
            ]);

            $createdRecords = [];
            foreach ($request->records as $recordData) {
                // BMIを自動計算
                if ($recordData['height'] && $recordData['weight']) {
                    $heightInM = $recordData['height'] / 100;
                    $recordData['bmi'] = round($recordData['weight'] / ($heightInM * $heightInM), 1);
                }

                $record = HealthRecord::create($recordData);
                $record->load(['student.schoolClass']);
                $createdRecords[] = $record;
            }

            return response()->json([
                'success' => true,
                'data' => $createdRecords,
                'message' => count($createdRecords) . '件の健康記録を一括登録しました'
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
                $query->where('academic_year', $request->academic_year);
            }

            if ($request->has('grade') && !empty($request->grade)) {
                $query->whereHas('student.schoolClass', function ($q) use ($request) {
                    $q->where('grade', $request->grade);
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
                    'data' => null,
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

            $statistics = [
                'total_records' => $records->count(),
                'averages' => [
                    'height' => round($records->avg('height'), 1),
                    'weight' => round($records->avg('weight'), 1),
                    'bmi' => round($records->avg('bmi'), 1)
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
                ]
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
}
