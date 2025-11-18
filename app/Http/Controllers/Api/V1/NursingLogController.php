<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Support\PdfFontHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use TCPDF;

class NursingLogController extends Controller
{
    public function test()
    {
        try {
            $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
            $fontName = PdfFontHelper::applyFont($pdf, 12);
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            $pdf->SetMargins(10, 10, 10);
            $pdf->AddPage();
            $pdf->SetFont($fontName, '', 12);
            $pdf->Cell(0, 10, 'Test PDF', 0, 1);
            
            $pdfContent = $pdf->Output('', 'S');
            
            return response($pdfContent, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="test.pdf"',
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function generatePdf(Request $request)
    {
        try {
            Log::info('generatePdf called', [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'input' => $request->all()
            ]);

            $data = $request->validate([
                'date' => 'required|date',
                'year' => 'nullable',
                'month' => 'nullable',
                'day' => 'nullable',
                'weather' => 'nullable|string',
                'temperature' => 'nullable|string',
                'humidity' => 'nullable|string',
                'absence' => 'nullable|array',
                'visits' => 'nullable|array',
                'schoolEvents' => 'nullable|string',
                'waterQuality' => 'nullable|string',
                'chlorine' => 'nullable|string',
                'notes' => 'nullable|string',
            ]);

            Log::info('Validation passed, generating PDF');
            // TCPDFインスタンスを作成
            $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
            $fontName = PdfFontHelper::applyFont($pdf, 10);
            
            // ドキュメント情報を設定
            $pdf->SetCreator('Health Management System');
            $pdf->SetAuthor('School Health Room');
            $pdf->SetTitle('養護日誌 - ' . $data['date']);
            $pdf->SetSubject('養護日誌');
            
            // ヘッダーとフッターを削除
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            
            // マージンを設定
            $pdf->SetMargins(10, 10, 10);
            $pdf->SetAutoPageBreak(true, 10);
            
            // 日本語フォントを設定
            $pdf->SetFont($fontName, '', 10);
            
            // ページを追加
            $pdf->AddPage();
            
            // HTMLコンテンツを作成
            $html = $this->generateHtml($data);
            
            // HTMLを出力
            $pdf->writeHTML($html, true, false, true, false, '');
            
            // PDFを出力
            $filename = 'nursing_log_' . date('Ymd', strtotime($data['date'])) . '.pdf';
            
            // PDFをバイナリで取得
            $pdfContent = $pdf->Output('', 'S');
            
            Log::info('PDF generated successfully', ['size' => strlen($pdfContent)]);
            
            return response($pdfContent, 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation error: ' . json_encode($e->errors()));
            return response()->json([
                'error' => 'バリデーションエラー',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            Log::error('PDF generation error: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'error' => 'PDF生成エラー',
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }
    
    private function generateHtml($data)
    {
        $date = new \DateTime($data['date']);
        $dayOfWeek = ['日', '月', '火', '水', '木', '金', '土'][$date->format('w')];
        
        $year = $data['year'] ?? ($date->format('Y') - 2018);
        $month = $data['month'] ?? $date->format('m');
        $day = $data['day'] ?? $date->format('d');
        
        $absence = $data['absence'] ?? [];
        $visits = $data['visits'] ?? [];
        
    // シンプルなHTMLテーブル構造
    $html = '<style>body { font-family: "seieiIPexMincho", serif; font-size: 10pt; }</style>';
    
    // タイトル
    $html .= '<h1 style="text-align:center;margin-bottom:10px;">養護日誌</h1>';
    
    // テーブル：1行目はヘッダー、2行目はデータと印鑑枠
    $html .= '<table border="1" cellpadding="4" style="width:100%;margin-bottom:10px;border-collapse:collapse;">';
    
    // 1行目：ヘッダー行
    $html .= '<tr>';
    $html .= '<td style="border:1px solid #000;text-align:center;vertical-align:middle;width:20%;"></td>'; // 日付欄（幅20%）
    $html .= '<td style="border:1px solid #000;text-align:center;vertical-align:middle;width:8%;"><b>天候</b></td>';
    $html .= '<td style="border:1px solid #000;text-align:center;vertical-align:middle;width:8%;"><b>温度</b></td>';
    $html .= '<td style="border:1px solid #000;text-align:center;vertical-align:middle;width:8%;"><b>湿度</b></td>';
    $html .= '<td style="border:1px solid #000;text-align:center;vertical-align:middle;width:14%;"><b>校長印</b></td>';
    $html .= '<td style="border:1px solid #000;text-align:center;vertical-align:middle;width:14%;"><b>副校長印</b></td>';
    $html .= '<td style="border:1px solid #000;text-align:center;vertical-align:middle;width:14%;"><b>教頭印</b></td>';
    $html .= '<td style="border:1px solid #000;text-align:center;vertical-align:middle;width:14%;"><b>記入者印</b></td>';
    $html .= '</tr>';
    
    // 2行目：データ行
    $html .= '<tr>';
    $html .= '<td style="border:1px solid #000;text-align:left;vertical-align:middle;width:20%;padding-left:10px;">令和' . htmlspecialchars($year) . '年' . htmlspecialchars($month) . '月' . htmlspecialchars($day) . '日　' . $dayOfWeek . '曜日</td>';
    $html .= '<td style="border:1px solid #000;text-align:left;vertical-align:middle;width:8%;padding-left:10px;">' . htmlspecialchars($data['weather'] ?? '') . '</td>';
    $html .= '<td style="border:1px solid #000;text-align:left;vertical-align:middle;width:8%;padding-left:10px;">' . htmlspecialchars($data['temperature'] ?? '') . '</td>';
    $html .= '<td style="border:1px solid #000;text-align:left;vertical-align:middle;width:8%;padding-left:10px;">' . htmlspecialchars($data['humidity'] ?? '') . '</td>';
    $html .= '<td style="border:1px solid #000;height:50px;width:14%;"></td>'; // 校長印
    $html .= '<td style="border:1px solid #000;height:50px;width:14%;"></td>'; // 副校長印
    $html .= '<td style="border:1px solid #000;height:50px;width:14%;"></td>'; // 教頭印
    $html .= '<td style="border:1px solid #000;height:50px;width:14%;"></td>'; // 記入者印
    $html .= '</tr>';
    
    $html .= '</table>';
        
        // 以前の印鑑枠テーブルは削除（上に移動したため）
        
        // 欠席調査
        $html .= '<h3 style="background-color:#f0f0f0;padding:5px;margin:10px 0 5px 0;">欠席調査</h3>';
        $html .= '<table border="1" cellpadding="3" style="width:100%;margin-bottom:10px;">';
        $html .= '<tr>';
        $html .= '<th rowspan="2" style="background-color:#f0f0f0;">種別</th>';
        $html .= '<th colspan="6" style="background-color:#f0f0f0;">学年別</th>';
        $html .= '<th rowspan="2" style="background-color:#f0f0f0;">計</th>';
        $html .= '</tr>';
        $html .= '<tr>';
        for ($i = 1; $i <= 6; $i++) {
            $html .= '<th style="background-color:#f0f0f0;">' . $i . '年</th>';
        }
        $html .= '</tr>';
        
        // 欠席データ
        $absenceTypes = [
            'absent' => '欠席',
            'sick' => '病欠',
            'accident' => '事故欠',
            'suspension' => '出停',
            'mourning' => '忌引'
        ];
        
        foreach ($absenceTypes as $type => $label) {
            $typeData = $absence[$type] ?? [];
            $total = 0;
            $html .= '<tr>';
            $html .= '<td style="background-color:#f0f0f0;"><b>' . $label . '</b></td>';
            for ($i = 1; $i <= 6; $i++) {
                $val = $typeData['grade' . $i] ?? 0;
                $total += (int)$val;
                $html .= '<td style="text-align:center;">' . ($val ?: '') . '</td>';
            }
            $html .= '<td style="text-align:center;"><b>' . ($total ?: '') . '</b></td>';
            $html .= '</tr>';
        }
        
        $html .= '</table>';
        
        // 学校行事と水質検査
        $html .= '<table border="1" cellpadding="5" style="width:100%;margin-bottom:10px;">';
        $html .= '<tr>';
        $html .= '<td style="width:50%;vertical-align:top;">';
        $html .= '<b>学校行事・保健行事</b><br/>';
        $html .= nl2br(htmlspecialchars($data['schoolEvents'] ?? ''));
        $html .= '</td>';
        $html .= '<td style="width:50%;vertical-align:top;">';
        $html .= '<b>水質検査・残留塩素</b><br/>';
        $html .= '水質検査: ' . htmlspecialchars($data['waterQuality'] ?? '') . '<br/>';
        $html .= '残留塩素: ' . htmlspecialchars($data['chlorine'] ?? '');
        $html .= '</td>';
        $html .= '</tr>';
        $html .= '</table>';
        
        // 保健室来室記録
        $html .= '<h3 style="background-color:#f0f0f0;padding:5px;margin:10px 0 5px 0;">保健室来室記録</h3>';
        $html .= '<table border="1" cellpadding="2" style="width:100%;margin-bottom:10px;font-size:8pt;">';
        $html .= '<tr>';
        $html .= '<th style="background-color:#f0f0f0;width:8%;">時間</th>';
        $html .= '<th style="background-color:#f0f0f0;width:5%;">年</th>';
        $html .= '<th style="background-color:#f0f0f0;width:5%;">組</th>';
        $html .= '<th style="background-color:#f0f0f0;width:5%;">番</th>';
        $html .= '<th style="background-color:#f0f0f0;width:15%;">氏名</th>';
        $html .= '<th style="background-color:#f0f0f0;width:5%;">性</th>';
        $html .= '<th style="background-color:#f0f0f0;width:10%;">種別</th>';
        $html .= '<th style="background-color:#f0f0f0;width:10%;">発生時</th>';
        $html .= '<th style="background-color:#f0f0f0;width:37%;">処置・原因・備考</th>';
        $html .= '</tr>';
        
        // 来室記録（最大10行表示）
        for ($i = 0; $i < 10; $i++) {
            $visit = $visits[$i] ?? [];
            $html .= '<tr>';
            $html .= '<td style="text-align:center;">' . htmlspecialchars($visit['time'] ?? '') . '</td>';
            $html .= '<td style="text-align:center;">' . htmlspecialchars($visit['grade'] ?? '') . '</td>';
            $html .= '<td style="text-align:center;">' . htmlspecialchars($visit['class'] ?? '') . '</td>';
            $html .= '<td style="text-align:center;">' . htmlspecialchars($visit['number'] ?? '') . '</td>';
            $html .= '<td>' . htmlspecialchars($visit['name'] ?? '') . '</td>';
            $html .= '<td style="text-align:center;">' . htmlspecialchars($visit['gender'] ?? '') . '</td>';
            $html .= '<td>' . htmlspecialchars($visit['type'] ?? '') . '</td>';
            $html .= '<td>' . htmlspecialchars($visit['occurrence'] ?? '') . '</td>';
            $html .= '<td>' . htmlspecialchars($visit['treatment'] ?? '') . '</td>';
            $html .= '</tr>';
        }
        
        $html .= '</table>';
        
        // 執務日記
        $html .= '<h3 style="background-color:#f0f0f0;padding:5px;margin:10px 0 5px 0;">執務日記及び特記事項</h3>';
        $html .= '<div style="border:1px solid #000;padding:5px;min-height:40px;">';
        $html .= nl2br(htmlspecialchars($data['notes'] ?? ''));
        $html .= '</div>';
        
        return $html;
    }
}

