<?php

namespace App\Http\Controllers;

use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use TCPDF;

class ClassController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            // Use raw SQL to get accurate student counts
            $query = SchoolClass::selectRaw('
                school_classes.*, 
                COALESCE(student_counts.students_count, 0) as students_count
            ')
            ->leftJoin(DB::raw('(
                SELECT class_id, COUNT(*) as students_count 
                FROM students 
                GROUP BY class_id
            ) as student_counts'), 'school_classes.class_id', '=', 'student_counts.class_id');

            // 年度フィルタ
            if ($request->has('year') && !empty($request->year)) {
                $query->where('school_classes.year', $request->year);
            }

            // 学年フィルタ
            if ($request->has('grade') && !empty($request->grade)) {
                $query->where('school_classes.grade', $request->grade);
            }

            // ソート
            $sortBy = $request->get('sort_by', 'grade');
            $sortOrder = $request->get('sort_order', 'asc');
            
            if ($sortBy === 'students_count') {
                $query->orderBy('students_count', $sortOrder);
            } else {
                $query->orderBy("school_classes.{$sortBy}", $sortOrder);
            }
            
            // 組でのサブソート
            if ($sortBy !== 'kumi') {
                $query->orderBy('school_classes.kumi', 'asc');
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
                $query->with(['latestHealthRecord'])
                    ->withCount('healthRecords')
                    ->orderBy('student_number');
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

    /**
     * Export classes to PDF
     */
    public function exportPdf()
    {
        try {
            // クラスデータを取得
            $classes = SchoolClass::selectRaw('
                school_classes.*, 
                COALESCE(student_counts.students_count, 0) as students_count
            ')
            ->leftJoin(DB::raw('(
                SELECT class_id, COUNT(*) as students_count 
                FROM students 
                GROUP BY class_id
            ) as student_counts'), 'school_classes.class_id', '=', 'student_counts.class_id')
            ->orderBy('school_classes.year', 'desc')
            ->orderBy('school_classes.grade', 'asc')
            ->orderBy('school_classes.kumi', 'asc')
            ->get();

            // PDFを生成
            $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
            
            // PDFの基本設定
            $pdf->SetCreator('Health Room System');
            $pdf->SetAuthor('School');
            $pdf->SetTitle('クラス一覧');
            $pdf->SetSubject('クラス一覧');
            
            // ヘッダー・フッターを削除
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            
            // マージン設定
            $pdf->SetMargins(15, 15, 15);
            $pdf->SetAutoPageBreak(true, 15);
            
            // フォント設定（日本語対応）
            $pdf->SetFont('kozminproregular', '', 10);
            
            // ページ追加
            $pdf->AddPage();
            
            // タイトル
            $pdf->SetFont('kozminproregular', 'B', 16);
            $pdf->Cell(0, 10, 'クラス一覧', 0, 1, 'C');
            $pdf->Ln(5);
            
            // 出力日時
            $pdf->SetFont('kozminproregular', '', 9);
            $pdf->Cell(0, 5, '出力日時: ' . date('Y年m月d日 H:i'), 0, 1, 'R');
            $pdf->Ln(5);
            
            // テーブルヘッダー
            $pdf->SetFont('kozminproregular', 'B', 10);
            $pdf->SetFillColor(230, 230, 230);
            $pdf->Cell(30, 8, '年度', 1, 0, 'C', true);
            $pdf->Cell(25, 8, '学年', 1, 0, 'C', true);
            $pdf->Cell(25, 8, '組', 1, 0, 'C', true);
            $pdf->Cell(80, 8, 'クラス名', 1, 0, 'C', true);
            $pdf->Cell(20, 8, '生徒数', 1, 1, 'C', true);
            
            // テーブルデータ
            $pdf->SetFont('kozminproregular', '', 9);
            foreach ($classes as $class) {
                $pdf->Cell(30, 7, $class->year . '年度', 1, 0, 'C');
                $pdf->Cell(25, 7, $class->grade . '年', 1, 0, 'C');
                $pdf->Cell(25, 7, ($class->kumi ? $class->kumi . '組' : '-'), 1, 0, 'C');
                $pdf->Cell(80, 7, $class->class_name, 1, 0, 'L');
                $pdf->Cell(20, 7, $class->students_count . '名', 1, 1, 'C');
            }
            
            // 統計情報
            $pdf->Ln(10);
            $pdf->SetFont('kozminproregular', 'B', 10);
            $pdf->Cell(0, 7, '統計情報', 0, 1, 'L');
            $pdf->SetFont('kozminproregular', '', 9);
            $pdf->Cell(0, 6, '総クラス数: ' . $classes->count() . 'クラス', 0, 1, 'L');
            $pdf->Cell(0, 6, '総生徒数: ' . $classes->sum('students_count') . '名', 0, 1, 'L');
            
            // PDFを出力
            $fileName = 'クラス一覧_' . date('Ymd') . '.pdf';
            
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

    // create, store, edit, update, destroy は使用しないが、
    // リソースコントローラーのため空メソッドとして残す
    public function create() { /* Not used */ }
    public function store(Request $request) { /* Not used */ }
    public function edit(string $id) { /* Not used */ }
    public function update(Request $request, string $id) { /* Not used */ }
    public function destroy(string $id) { /* Not used */ }
}
