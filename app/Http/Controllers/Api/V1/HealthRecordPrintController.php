<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Support\PdfFontHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use TCPDF;

class HealthRecordPrintController extends Controller
{
    public function generatePdf(Request $request)
    {
        try {
            Log::info('Health Record PDF generation called', [
                'input' => $request->all()
            ]);

            $data = $request->validate([
                'exam_type' => 'required|string',
                'student' => 'required|array',
                'student.name' => 'required|string',
                'student.student_id' => 'required|string',
                'student.student_number' => 'nullable',
                'student.school_class' => 'nullable|array',
                'health_record' => 'nullable|array',
                'form_data' => 'nullable|array',
            ]);

            Log::info('Validation passed, generating PDF');
            
            // TCPDFインスタンスを作成
            $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
            $fontName = PdfFontHelper::applyFont($pdf, 10);
            
            // ドキュメント情報を設定
            $pdf->SetCreator('Health Management System');
            $pdf->SetAuthor('School Health Room');
            $pdf->SetTitle('健康診断結果 - ' . $data['student']['name']);
            $pdf->SetSubject('健康診断結果');
            
            // ヘッダーとフッターを削除
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            
            // マージンを設定
            $pdf->SetMargins(15, 15, 15);
            $pdf->SetAutoPageBreak(true, 15);
            
            // ページを追加
            $pdf->AddPage();
            
            // 日本語フォントを設定（ページ追加後に設定）
            $pdf->SetFont($fontName, '', 10);
            
            // HTMLコンテンツを作成
            if ($data['exam_type'] === 'vision_test') {
                $html = $this->generateVisionTestHtml($data);
            } elseif ($data['exam_type'] === 'otolaryngology') {
                $html = $this->generateOtolaryngologyHtml($data);
            } else {
                throw new \Exception('Unknown exam type: ' . $data['exam_type']);
            }
            
            // HTMLを出力
            $pdf->writeHTML($html, true, false, true, false, '');
            
            // PDFを出力
            $examTypeName = $data['exam_type'] === 'vision_test' ? '視力検査' : '耳鼻科検診';
            $filename = $data['student']['name'] . '_' . $examTypeName . '_' . date('Ymd') . '.pdf';
            
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
    
    private function generateVisionTestHtml($data)
    {
        $student = $data['student'];
        $formData = $data['form_data'] ?? [];
        $healthRecord = $data['health_record'] ?? [];
        
        $className = '';
        $courseName = '';
        if (isset($student['school_class']['name'])) {
            preg_match('/([^\d]+)/', $student['school_class']['name'], $courseMatch);
            preg_match('/(\d+)/', $student['school_class']['name'], $classMatch);
            $courseName = $courseMatch[1] ?? '';
            $className = $classMatch[1] ?? '';
        }
        
        // 学年を取得（school_class.gradeまたはgrade_idから）
        $gradeId = $student['school_class']['grade'] ?? $student['school_class']['grade_id'] ?? '';
        $studentNumber = $student['student_number'] ?? '';
        
        $html = '
        <style>
            body { font-size: 10pt; font-family: "seieiIPexMincho", serif; }
            h2 { font-size: 14pt; text-align: center; border-bottom: 2px solid #000; padding-bottom: 5px; margin: 10px 0; }
            h3 { font-size: 11pt; margin-top: 15px; margin-bottom: 8px; }
            table { border-collapse: collapse; width: 90%; margin: 10px 0; }
            th, td { border: 1px solid #000; padding: 5px; }
            th { background-color: #f0f0f0; text-align: center; }
            .info-line { margin: 8px 0; }
        </style>
        
        <div class="info-line">※保護者様<span style="float: right;">令和' . htmlspecialchars($formData['year'] ?? '　　') . '年' . htmlspecialchars($formData['month'] ?? '　　') . '月</span></div>
        <div style="text-align: center; margin-bottom: 5px;"><strong>誠英高等学校</strong></div>
        <div style="text-align: right; margin-bottom: 10px;">校長　' . htmlspecialchars($formData['principal'] ?? '　　　　　　　　　　') . '</div>
        
        <h2>視力検査結果のお知らせ</h2>
        
        <div class="info-line">' . htmlspecialchars($gradeId) . '年　' . htmlspecialchars($courseName) . 'コース　' . htmlspecialchars($className) . '組　' . htmlspecialchars($studentNumber) . '番　氏名　' . htmlspecialchars($student['name']) . '</div>
        
        <p style="margin: 10px 0; line-height: 1.6;">このたびの視力検査結果は下記の通りです。視力低下の疑いがありますので、専門医で受診されますようにお勧めします。<br>なお、受診は下欄の連絡票に記入してもらい、切り取らずに学校へ提出してください。</p>
        
        <h3>学校での検査</h3>
        <table>
            <tr>
                <th></th>
                <th>裸眼視力</th>
                <th>矯正視力</th>
            </tr>
            <tr>
                <td style="font-weight: bold; text-align: center;">右</td>
                <td style="text-align: center;">' . htmlspecialchars($formData['school_vision_right'] ?? $healthRecord['vision_right'] ?? '') . '</td>
                <td style="text-align: center;">' . htmlspecialchars($formData['school_vision_right_corrected'] ?? $healthRecord['vision_right_corrected'] ?? '') . '</td>
            </tr>
            <tr>
                <td style="font-weight: bold; text-align: center;">左</td>
                <td style="text-align: center;">' . htmlspecialchars($formData['school_vision_left'] ?? $healthRecord['vision_left'] ?? '') . '</td>
                <td style="text-align: center;">' . htmlspecialchars($formData['school_vision_left_corrected'] ?? $healthRecord['vision_left_corrected'] ?? '') . '</td>
            </tr>
        </table>
        
            <div style="margin-bottom: 20px; font-size: 9pt;">
                〈判断基準〉<br>
        <div style="font-size: 9pt; margin: 8px 0;">〈判断基準〉A　1.0以上　B　0.9〜0.7　C　0.6〜0.3　D　0.3未満</div>
        
        <div style="border-top: 2px dashed #666; margin: 15px 0;"></div>
        
        <h3>眼科検診受診連絡票</h3>
        <table>
            <tr>
                <th></th>
                <th>裸眼視力</th>
                <th>矯正視力</th>
            </tr>
            <tr>
                <td style="font-weight: bold; text-align: center;">右</td>
                <td style="text-align: center;">' . htmlspecialchars($formData['followup_vision_right'] ?? '') . '</td>
                <td style="text-align: center;">' . htmlspecialchars($formData['followup_vision_right_corrected'] ?? '') . '</td>
            </tr>
            <tr>
                <td style="font-weight: bold; text-align: center;">左</td>
                <td style="text-align: center;">' . htmlspecialchars($formData['followup_vision_left'] ?? '') . '</td>
                <td style="text-align: center;">' . htmlspecialchars($formData['followup_vision_left_corrected'] ?? '') . '</td>
            </tr>
        </table>
        
        <div style="margin: 10px 0;">
            （１）屈折異常<br>
            <div style="margin-left: 15px; font-size: 9pt;">
                ' . ($formData['diagnosis']['adjustment'] ?? false ? '■' : '□') . ' 調節緊張症　
                ' . ($formData['diagnosis']['myopia'] ?? false ? '■' : '□') . ' 近視　
                ' . ($formData['diagnosis']['myopic_astigmatism'] ?? false ? '■' : '□') . ' 近視性乱視<br>
                ' . ($formData['diagnosis']['hyperopia'] ?? false ? '■' : '□') . ' 遠視　
                ' . ($formData['diagnosis']['hyperopic_astigmatism'] ?? false ? '■' : '□') . ' 遠視性乱視　
                その他（' . htmlspecialchars($formData['diagnosis']['other_refraction'] ?? '') . '）
            </div>
        </div>
        
        <div style="margin: 10px 0; font-size: 9pt;">（２）眼位異常　（' . htmlspecialchars($formData['eye_position_abnormality'] ?? '') . '）</div>
        
        <div style="margin: 10px 0;">
            （３）指導又は治療方針<br>
            <div style="margin-left: 15px; font-size: 9pt;">
                ' . (($formData['treatment_policy'] ?? '') === 'observation' ? '■' : '□') . ' １　経過観察<br>
                ' . (($formData['treatment_policy'] ?? '') === 'eye_drops' ? '■' : '□') . ' ２　点眼治療<br>
                ' . (($formData['treatment_policy'] ?? '') === 'prescription' ? '■' : '□') . ' ３　眼科・コンタクト処方（
                ' . (($formData['prescription_type'] ?? '') === 'new' ? '■' : '□') . ' 新規　
                ' . (($formData['prescription_type'] ?? '') === 'renewal' ? '■' : '□') . ' 更新）<br>
                ' . (($formData['treatment_policy'] ?? '') === 'other' ? '■' : '□') . ' ４　その他（' . htmlspecialchars($formData['other_treatment'] ?? '') . '）
            </div>
        </div>';
        
        return $html;
    }
    
    private function generateOtolaryngologyHtml($data)
    {
        $student = $data['student'];
        $formData = $data['form_data'] ?? [];
        $selectedFindings = $formData['selected_findings'] ?? [];
        
        // 学年を取得（複数のパスを試す）
        $gradeId = $student['school_class']['grade'] ?? 
                   $student['school_class']['grade_id'] ?? 
                   $student['grade_id'] ?? 
                   '';
        $className = $student['school_class']['class_name'] ?? 
                     $student['school_class']['name'] ?? 
                     '';
        
        // 番号を丸で囲む関数
        $circleNumber = function($num) use ($selectedFindings) {
            if (in_array($num, $selectedFindings)) {
                return '<span style="display: inline-block; width: 18px; height: 18px; line-height: 18px; text-align: center; border: 2px solid #000; border-radius: 50%; font-weight: bold;">' . $num . '</span>';
            }
            return $num;
        };
        
        $html = '
        <style>
            body { font-size: 9pt; font-family: "seieiIPexMincho", serif; }
            h2 { font-size: 12pt; text-align: center; border-bottom: 2px solid #000; padding-bottom: 5px; margin: 8px 0; }
            h3 { font-size: 10pt; margin: 8px 0 3px 0; }
            table { border-collapse: collapse; width: 100%; margin: 3px 0; font-size: 8pt; }
            th, td { border: 1px solid #000; padding: 2px; }
            th { background-color: #e0e0e0; text-align: center; }
            .circle-num { display: inline-block; width: 18px; height: 18px; line-height: 18px; text-align: center; border: 2px solid #000; border-radius: 50%; font-weight: bold; }
        </style>
        
        <h2>耳鼻咽喉科健康検診結果のお知らせ</h2>
        
        <div style="margin: 8px 0;"><strong>氏名：</strong>' . htmlspecialchars($student['name']) . '　<strong>学年：</strong>' . htmlspecialchars($gradeId) . '年' . htmlspecialchars($className) . '</div>
        
        <p style="margin: 5px 0; line-height: 1.3; font-size: 8pt;">検診の結果、下記のとおりでした。○で囲んだ項目について受診をお勧めします。受診後は下欄の連絡票に記入してもらい、切り取らずに学校へ提出してください。</p>
        
        <h3>【耳】</h3>
        <table>
            <tr><th style="width: 8%;">番号</th><th style="width: 23%;">所見項目</th><th>説明</th></tr>
            <tr><td style="text-align: center;">' . $circleNumber(1) . '</td><td>耳垢</td><td>耳あかが多い</td></tr>
            <tr><td style="text-align: center;">' . $circleNumber(2) . '</td><td>滲出性中耳炎</td><td>鼓膜の内側に液がたまる</td></tr>
            <tr><td style="text-align: center;">' . $circleNumber(3) . '</td><td>中耳炎・鼓膜穿孔</td><td>中耳に炎症や穴がある</td></tr>
            <tr><td style="text-align: center;">' . $circleNumber(4) . '</td><td>難聴の疑い</td><td>聴力検査で聞こえが弱い</td></tr>
        </table>
        
        <h3>【鼻】</h3>
        <table>
            <tr><th style="width: 8%;">番号</th><th style="width: 23%;">所見項目</th><th>説明</th></tr>
            <tr><td style="text-align: center;">' . $circleNumber(5) . '</td><td>鼻炎・慢性鼻炎</td><td>鼻の粘膜が炎症</td></tr>
            <tr><td style="text-align: center;">' . $circleNumber(6) . '</td><td>アレルギー性鼻炎</td><td>花粉やハウスダストでの鼻炎</td></tr>
            <tr><td style="text-align: center;">' . $circleNumber(7) . '</td><td>副鼻腔炎</td><td>鼻の奥の空洞に炎症</td></tr>
            <tr><td style="text-align: center;">' . $circleNumber(8) . '</td><td>鼻中隔弯曲症</td><td>鼻の仕切りが曲がっている</td></tr>
        </table>
        
        <h3>【咽喉】</h3>
        <table>
            <tr><th style="width: 8%;">番号</th><th style="width: 23%;">所見項目</th><th>説明</th></tr>
            <tr><td style="text-align: center;">' . $circleNumber(9) . '</td><td>扁桃肥大</td><td>扁桃が大きくなっている</td></tr>
            <tr><td style="text-align: center;">' . $circleNumber(10) . '</td><td>扁桃炎</td><td>扁桃が炎症を起こしている</td></tr>
            <tr><td style="text-align: center;">' . $circleNumber(11) . '</td><td>アデノイド</td><td>鼻の奥のリンパ組織が肥大</td></tr>
        </table>
        
        <h3>【口腔】</h3>
        <table>
            <tr><th style="width: 8%;">番号</th><th style="width: 23%;">所見項目</th><th>説明</th></tr>
            <tr><td style="text-align: center;">' . $circleNumber(12) . '</td><td>口内炎</td><td>口の中に炎症</td></tr>
            <tr><td style="text-align: center;">' . $circleNumber(13) . '</td><td>舌小帯異常</td><td>舌の裏のひもが短い</td></tr>
            <tr><td style="text-align: center;">' . $circleNumber(14) . '</td><td>舌異常</td><td>舌の形や色に異常</td></tr>
        </table>
        
        <h3>【その他】</h3>
        <table>
            <tr><th style="width: 8%;">番号</th><th style="width: 23%;">所見項目</th><th>説明</th></tr>
            <tr><td style="text-align: center;">' . $circleNumber(15) . '</td><td>その他</td><td>その他の所見</td></tr>
        </table>
        
        <div style="margin: 8px 0; font-size: 8pt;">検診実施日：' . htmlspecialchars($formData['exam_date_year'] ?? '') . '年' . htmlspecialchars($formData['exam_date_month'] ?? '') . '月' . htmlspecialchars($formData['exam_date_day'] ?? '') . '日　校医名：' . htmlspecialchars($formData['doctor_name'] ?? '') . '</div>
        
        <div style="border-top: 2px dashed #666; margin: 10px 0;"></div>
        
        <h3 style="text-align: center; background-color: #f5f5f5; border: 2px solid #333; padding: 3px;">耳鼻咽喉科受診連絡表</h3>
        
        <table style="font-size: 9pt;">
            <tr><td style="background-color: #f0f0f0; width: 20%; text-align: center;"><strong>病名</strong></td><td>' . htmlspecialchars($formData['followup_disease'] ?? '') . '</td></tr>
            <tr><td style="background-color: #f0f0f0; text-align: center;"><strong>受診日</strong></td><td>' . htmlspecialchars($formData['followup_visit_year'] ?? '') . '年' . htmlspecialchars($formData['followup_visit_month'] ?? '') . '月' . htmlspecialchars($formData['followup_visit_day'] ?? '') . '日</td></tr>
            <tr><td style="background-color: #f0f0f0; text-align: center;"><strong>医療機関名</strong></td><td>' . htmlspecialchars($formData['followup_clinic_name'] ?? '') . '</td></tr>
            <tr><td style="background-color: #f0f0f0; text-align: center;"><strong>保護者氏名</strong></td><td>' . htmlspecialchars($formData['followup_parent_name'] ?? '') . '　印</td></tr>
        </table>';
        
        return $html;
    }
}
