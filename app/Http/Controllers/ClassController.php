<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ClassController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = SchoolClass::withCount('students');

            // 年度フィルタ
            if ($request->has('year') && !empty($request->year)) {
                $query->where('year', $request->year);
            }

            // 学年フィルタ
            if ($request->has('grade') && !empty($request->grade)) {
                $query->where('grade', $request->grade);
            }

            // ソート
            $sortBy = $request->get('sort_by', 'grade');
            $sortOrder = $request->get('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);
            
            // 組でのサブソート
            if ($sortBy !== 'kumi') {
                $query->orderBy('kumi', 'asc');
            }

            $classes = $query->get();

            return response()->json([
                'success' => true,
                'data' => $classes,
                'message' => 'クラス一覧を取得しました'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'クラス一覧の取得に失敗しました',
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
            $class = SchoolClass::with(['students' => function ($query) {
                $query->orderBy('student_number');
            }])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $class,
                'message' => 'クラス詳細を取得しました'
            ]);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => '指定されたクラスが見つかりません'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'クラス詳細の取得に失敗しました',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available years for filter
     */
    public function getYears(): JsonResponse
    {
        try {
            $years = SchoolClass::distinct()
                              ->orderBy('year', 'desc')
                              ->pluck('year');

            return response()->json([
                'success' => true,
                'data' => $years,
                'message' => '利用可能な年度一覧を取得しました'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => '年度一覧の取得に失敗しました',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get classes by year and grade
     */
    public function getByYearAndGrade(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'year' => 'required|integer',
                'grade' => 'required|integer|min:1|max:3'
            ]);

            $classes = SchoolClass::where('year', $validated['year'])
                                 ->where('grade', $validated['grade'])
                                 ->orderBy('kumi')
                                 ->get();

            return response()->json([
                'success' => true,
                'data' => $classes,
                'message' => '指定条件のクラス一覧を取得しました'
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'バリデーションエラーが発生しました',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'クラス一覧の取得に失敗しました',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // create, store, edit, update, destroy は使用しないが、
    // リソースコントローラーのため空メソッドとして残す
    public function create() { /* Not used */ }
    public function store(Request $request) { /* Not used */ }
    public function edit(string $id) { /* Not used */ }
    public function update(Request $request, string $id) { /* Not used */ }
    public function destroy(string $id) { /* Not used */ }
}
