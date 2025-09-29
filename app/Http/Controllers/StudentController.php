<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\SchoolClass;
use App\Http\Requests\StudentRequest;
use App\Services\StudentSearchService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

class StudentController extends Controller
{
    protected $studentSearchService;

    public function __construct(StudentSearchService $studentSearchService)
    {
        $this->studentSearchService = $studentSearchService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // シンプルな検索実装
            $query = Student::query();
            
            // 名前検索
            if ($request->filled('search')) {
                $searchTerm = $request->search;
                $query->where(function($q) use ($searchTerm) {
                    $q->where('name', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('name_kana', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('student_id', 'LIKE', "%{$searchTerm}%")
                      ->orWhere('student_number', '=', $searchTerm)
                      ->orWhere('student_number', 'LIKE', "%{$searchTerm}%");
                });
            }
            
            // クラスフィルター
            if ($request->filled('class_id')) {
                $query->where('class_id', $request->class_id);
            }
            
            // 学年フィルター
            if ($request->filled('grade_id')) {
                $query->where('grade_id', $request->grade_id);
            }
            
            // ソート
            $sortBy = $request->get('sort', 'name');
            if (strpos($sortBy, '-') === 0) {
                $query->orderBy(substr($sortBy, 1), 'desc');
            } else {
                $query->orderBy($sortBy, 'asc');
            }
            
            $students = $query->paginate($request->get('per_page', 15));

            return response()->json([
                'success' => true,
                'data' => $students,
                'message' => '生徒一覧を取得しました'
            ]);

        } catch (\Exception $e) {
            Log::error('Student index error: ' . $e->getMessage());
            Log::error('Student index trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => '生徒一覧の取得に失敗しました',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): JsonResponse
    {
        try {
            $classes = SchoolClass::orderBy('grade')->orderBy('kumi')->get();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'classes' => $classes
                ],
                'message' => '生徒作成フォームデータを取得しました'
            ]);

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
    public function store(StudentRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            
            // student_idが提供されていない場合は自動生成
            if (empty($validated['student_id'])) {
                $validated['student_id'] = 'ST' . str_pad($validated['student_number'], 6, '0', STR_PAD_LEFT);
            }
            
            $student = Student::create($validated);
            $student->load(['schoolClass', 'healthRecords']);

            return response()->json([
                'success' => true,
                'data' => $student,
                'message' => '生徒を登録しました'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '生徒の登録に失敗しました',
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
            $student = Student::with(['schoolClass', 'healthRecords' => function ($query) {
                $query->orderBy('year', 'desc');
            }])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $student,
                'message' => '生徒詳細を取得しました'
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => '指定された生徒が見つかりません'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '生徒詳細の取得に失敗しました',
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
            $student = Student::with('schoolClass')->findOrFail($id);
            $classes = SchoolClass::orderBy('grade')->orderBy('kumi')->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'student' => $student,
                    'classes' => $classes
                ],
                'message' => '生徒編集フォームデータを取得しました'
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => '指定された生徒が見つかりません'
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
    public function update(StudentRequest $request, string $id): JsonResponse
    {
        try {
            $student = Student::findOrFail($id);
            $validated = $request->validated();
            $student->update($validated);
            $student->load(['schoolClass', 'healthRecords']);

            return response()->json([
                'success' => true,
                'data' => $student,
                'message' => '生徒情報を更新しました'
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => '指定された生徒が見つかりません'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '生徒情報の更新に失敗しました',
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
            $student = Student::findOrFail($id);
            $studentName = $student->name;
            
            $student->delete();

            return response()->json([
                'success' => true,
                'message' => "生徒「{$studentName}」を削除しました"
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => '指定された生徒が見つかりません'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '生徒の削除に失敗しました',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 検索候補を取得（オートコンプリート用）
     */
    public function searchSuggestions(Request $request): JsonResponse
    {
        try {
            $query = $request->get('query', '');
            $limit = $request->get('limit', 10);

            if (empty($query)) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'message' => '検索候補を取得しました'
                ]);
            }

            $suggestions = $this->studentSearchService->getSearchSuggestions($query, $limit);

            return response()->json([
                'success' => true,
                'data' => $suggestions,
                'message' => '検索候補を取得しました'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '検索候補の取得に失敗しました',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 高度な検索
     */
    public function advancedSearch(Request $request): JsonResponse
    {
        try {
            $conditions = $request->all();
            $results = $this->studentSearchService->advancedSearch($conditions);

            return response()->json([
                'success' => true,
                'data' => $results,
                'message' => '高度な検索結果を取得しました'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '高度な検索に失敗しました',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * エクスポート用データ取得
     */
    public function exportData(Request $request): JsonResponse
    {
        try {
            $searchParams = $request->all();
            $exportData = $this->studentSearchService->getExportData($searchParams);

            return response()->json([
                'success' => true,
                'data' => $exportData,
                'message' => 'エクスポート用データを取得しました',
                'count' => count($exportData)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'エクスポート用データの取得に失敗しました',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get student's health records
     */
    public function healthRecords(string $id): JsonResponse
    {
        try {
            $student = Student::with(['healthRecords' => function ($query) {
                $query->orderBy('measured_date', 'desc');
            }])->findOrFail($id);

            // 最新の健康記録情報を付加
            $latestRecord = $student->healthRecords->first();
            
            $response = [
                'success' => true,
                'data' => $student->healthRecords,
                'student' => [
                    'id' => $student->id,
                    'name' => $student->name,
                    'student_number' => $student->student_number
                ],
                'message' => '生徒の健康記録一覧を取得しました'
            ];

            if ($latestRecord) {
                $response['latest_record'] = $latestRecord;
            }

            return response()->json($response);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => '指定された生徒が見つかりません'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '生徒の健康記録取得に失敗しました',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
