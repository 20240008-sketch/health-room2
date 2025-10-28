<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
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
            
            // 日本語フォントを設定
            $pdf->SetFont('cid0jp', '', 10);
            
            // ページを追加
            $pdf->AddPage();
            
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
        
        $gradeId = $student['school_class']['grade_id'] ?? '';
        $studentNumber = $student['student_number'] ?? '';
        
        $html = '
        <div style="font-family: serif;">
            <div style="margin-bottom: 10px;">
                <span>※保護者様</span>
                <span style="float: right;">令和' . htmlspecialchars($formData['year'] ?? '　　') . '年' . htmlspecialchars($formData['month'] ?? '　　') . '月</span>
            </div>
            <div style="text-align: center; margin-bottom: 5px;">
                <strong style="font-size: 12pt;">誠英高等学校</strong>
            </div>
            <div style="text-align: right; margin-bottom: 10px;">
                校長　' . htmlspecialchars($formData['principal'] ?? '　　　　　　　　　　') . '
            </div>
            
            <h2 style="text-align: center; border-bottom: 2px solid #000; padding-bottom: 5px;">視力検査結果のお知らせ</h2>
            
            <div style="margin: 15px 0;">
                ' . htmlspecialchars($gradeId) . '年　' . htmlspecialchars($courseName) . 'コース　' . htmlspecialchars($className) . '組　' . htmlspecialchars($studentNumber) . '番　氏名　' . htmlspecialchars($student['name']) . '
            </div>
            
            <p style="text-indent: 1em; line-height: 1.8;">
                このたびの視力検査結果は下記の通りです。視力低下の疑いがありますので、専門医で受診されますようにお勧めします。<br>
                なお、受診は下欄の連絡票に記入してもらい、切り取らずに学校へ提出してください。
            </p>
            
            <h3 style="margin-top: 20px;">学校での検査</h3>
            <table border="1" cellpadding="5" style="margin-left: 20px; margin-bottom: 15px;">
                <tr>
                    <th style="background-color: #f5f5f5;"></th>
                    <th style="background-color: #f5f5f5; text-align: center;">裸眼視力</th>
                    <th style="background-color: #f5f5f5; text-align: center;">矯正視力</th>
                </tr>
                <tr>
                    <td style="font-weight: bold;">右</td>
                    <td style="text-align: center;">' . htmlspecialchars($formData['school_vision_right'] ?? $healthRecord['vision_right'] ?? '') . '</td>
                    <td style="text-align: center;">' . htmlspecialchars($formData['school_vision_right_corrected'] ?? $healthRecord['vision_right_corrected'] ?? '') . '</td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">左</td>
                    <td style="text-align: center;">' . htmlspecialchars($formData['school_vision_left'] ?? $healthRecord['vision_left'] ?? '') . '</td>
                    <td style="text-align: center;">' . htmlspecialchars($formData['school_vision_left_corrected'] ?? $healthRecord['vision_left_corrected'] ?? '') . '</td>
                </tr>
            </table>
            
            <div style="margin-bottom: 20px; font-size: 9pt;">
                〈判断基準〉<br>
                <span style="margin-left: 2em;">A　1.0以上　　　B　0.9〜0.7　　C　0.6〜0.3　　D　0.3未満</span>
            </div>
            
            <div style="border-top: 2px dashed #666; margin: 25px 0;"></div>
            
            <h3>眼科検診受診連絡票</h3>
            <table border="1" cellpadding="5" style="margin-left: 20px; margin-bottom: 15px;">
                <tr>
                    <th style="background-color: #f5f5f5;"></th>
                    <th style="background-color: #f5f5f5; text-align: center;">裸眼視力</th>
                    <th style="background-color: #f5f5f5; text-align: center;">矯正視力</th>
                </tr>
                <tr>
                    <td style="font-weight: bold;">右</td>
                    <td style="text-align: center;">' . htmlspecialchars($formData['followup_vision_right'] ?? '') . '</td>
                    <td style="text-align: center;">' . htmlspecialchars($formData['followup_vision_right_corrected'] ?? '') . '</td>
                </tr>
                <tr>
                    <td style="font-weight: bold;">左</td>
                    <td style="text-align: center;">' . htmlspecialchars($formData['followup_vision_left'] ?? '') . '</td>
                    <td style="text-align: center;">' . htmlspecialchars($formData['followup_vision_left_corrected'] ?? '') . '</td>
                </tr>
            </table>
            
            <div style="margin: 15px 0; line-height: 2;">
                （１）屈折異常<br>
                <div style="margin-left: 2em;">
                    ' . ($formData['diagnosis']['adjustment'] ?? false ? '☑' : '□') . ' 調節緊張症　
                    ' . ($formData['diagnosis']['myopia'] ?? false ? '☑' : '□') . ' 近視　
                    ' . ($formData['diagnosis']['myopic_astigmatism'] ?? false ? '☑' : '□') . ' 近視性乱視　
                    ' . ($formData['diagnosis']['hyperopia'] ?? false ? '☑' : '□') . ' 遠視　
                    ' . ($formData['diagnosis']['hyperopic_astigmatism'] ?? false ? '☑' : '□') . ' 遠視性乱視<br>
                    その他　（' . htmlspecialchars($formData['diagnosis']['other_refraction'] ?? '') . '）
                </div>
            </div>
            
            <div style="margin: 15px 0;">
                （２）眼位異常　　　（' . htmlspecialchars($formData['eye_position_abnormality'] ?? '') . '）
            </div>
            
            <div style="margin: 15px 0; line-height: 2;">
                （３）指導又は治療方針<br>
                <div style="margin-left: 2em;">
                    ' . ($formData['treatment_policy'] === 'observation' ? '☑' : '□') . ' １　経過観察　　　　　
                    ' . ($formData['treatment_policy'] === 'eye_drops' ? '☑' : '□') . ' ２　点眼治療<br><br>
                    ' . ($formData['treatment_policy'] === 'prescription' ? '☑' : '□') . ' ３　眼科・コンタクト処方　　（
                    ' . ($formData['prescription_type'] === 'new' ? '☑' : '□') . ' 新規　・　
                    ' . ($formData['prescription_type'] === 'renewal' ? '☑' : '□') . ' 更新　）<br><br>
                    ' . ($formData['treatment_policy'] === 'other' ? '☑' : '□') . ' ４　その他　（' . htmlspecialchars($formData['other_treatment'] ?? '') . '）
                </div>
            </div>
        </div>';
        
        return $html;
    }
    
    private function generateOtolaryngologyHtml($data)
    {
        $student = $data['student'];
        $formData = $data['form_data'] ?? [];
        $selectedFindings = $formData['selected_findings'] ?? [];
        
        $gradeId = $student['school_class']['grade_id'] ?? '';
        $className = $student['school_class']['name'] ?? '';
        
        $html = '
        <div style="font-family: serif;">
            <h2 style="text-align: center; border-bottom: 2px solid #000; padding-bottom: 5px;">耳鼻咽喉科健康検診結果のお知らせ</h2>
            
            <div style="margin: 15px 0;">
                <strong>生徒氏名：</strong> <u>' . htmlspecialchars($student['name']) . '</u>　　
                <strong>学年・組：</strong> <u>' . htmlspecialchars($gradeId) . '年' . htmlspecialchars($className) . '</u>
            </div>
            
            <p style="text-align: justify; line-height: 1.6; font-size: 9pt;">
                耳鼻咽喉科健康検診の結果、下記のとおりでした。該当する番号の前に○印をつけてお知らせします。耳鼻咽喉科の受診をお勧めします。なお、受診後は下欄の「耳鼻咽喉科受診連絡表」に記入してもらい、切り取らずに学校へ提出してください。
            </p>
            
            <h3 style="font-size: 11pt; margin-top: 15px; margin-bottom: 5px;">【耳】</h3>
            <table border="1" cellpadding="3" style="width: 100%; margin-bottom: 10px; font-size: 8pt; border-collapse: collapse;">
                <tr>
                    <th style="background-color: #e0e0e0; width: 8%; text-align: center;">番号</th>
                    <th style="background-color: #e0e0e0; width: 22%;">所見項目</th>
                    <th style="background-color: #e0e0e0;">説明</th>
                </tr>
                <tr>
                    <td style="text-align: center;">' . (in_array(1, $selectedFindings) ? '○1' : '1') . '</td>
                    <td>耳垢</td>
                    <td>耳あかが多く、耳の聞こえに影響することがあります。</td>
                </tr>
                <tr>
                    <td style="text-align: center;">' . (in_array(2, $selectedFindings) ? '○2' : '2') . '</td>
                    <td>滲出性中耳炎</td>
                    <td>鼓膜の内側に液がたまり、軽い難聴がみられることがあります。</td>
                </tr>
                <tr>
                    <td style="text-align: center;">' . (in_array(3, $selectedFindings) ? '○3' : '3') . '</td>
                    <td>（慢性）中耳炎・鼓膜穿孔</td>
                    <td>中耳に炎症や穴があり、耳だれ・聴力低下が続くことがあります。</td>
                </tr>
                <tr>
                    <td style="text-align: center;">' . (in_array(4, $selectedFindings) ? '○4' : '4') . '</td>
                    <td>難聴の疑い</td>
                    <td>聴力検査で聞こえが弱い可能性があります。精密検査が必要です。</td>
                </tr>
            </table>
            
            <h3 style="font-size: 11pt; margin-top: 10px; margin-bottom: 5px;">【鼻】</h3>
            <table border="1" cellpadding="3" style="width: 100%; margin-bottom: 10px; font-size: 8pt; border-collapse: collapse;">
                <tr>
                    <th style="background-color: #e0e0e0; width: 8%; text-align: center;">番号</th>
                    <th style="background-color: #e0e0e0; width: 22%;">所見項目</th>
                    <th style="background-color: #e0e0e0;">説明</th>
                </tr>
                <tr>
                    <td style="text-align: center;">' . (in_array(5, $selectedFindings) ? '○5' : '5') . '</td>
                    <td>鼻炎・慢性鼻炎</td>
                    <td>鼻の粘膜が炎症を起こし、鼻づまりや鼻水が続きます。</td>
                </tr>
                <tr>
                    <td style="text-align: center;">' . (in_array(6, $selectedFindings) ? '○6' : '6') . '</td>
                    <td>アレルギー性鼻炎</td>
                    <td>花粉やハウスダストなどで鼻炎症状が出ます。</td>
                </tr>
                <tr>
                    <td style="text-align: center;">' . (in_array(7, $selectedFindings) ? '○7' : '7') . '</td>
                    <td>副鼻腔炎</td>
                    <td>鼻の奥の空洞に炎症があり、鼻づまりや頭痛の原因になります。</td>
                </tr>
                <tr>
                    <td style="text-align: center;">' . (in_array(8, $selectedFindings) ? '○8' : '8') . '</td>
                    <td>鼻中隔弯曲症</td>
                    <td>鼻の仕切りが曲がっており、鼻づまりの原因になります。</td>
                </tr>
            </table>
            
            <h3 style="font-size: 11pt; margin-top: 10px; margin-bottom: 5px;">【咽喉】</h3>
            <table border="1" cellpadding="3" style="width: 100%; margin-bottom: 10px; font-size: 8pt; border-collapse: collapse;">
                <tr>
                    <th style="background-color: #e0e0e0; width: 8%; text-align: center;">番号</th>
                    <th style="background-color: #e0e0e0; width: 22%;">所見項目</th>
                    <th style="background-color: #e0e0e0;">説明</th>
                </tr>
                <tr>
                    <td style="text-align: center;">' . (in_array(9, $selectedFindings) ? '○9' : '9') . '</td>
                    <td>扁桃肥大</td>
                    <td>扁桃が大きくなっており、呼吸や飲み込みに影響することがあります。</td>
                </tr>
                <tr>
                    <td style="text-align: center;">' . (in_array(10, $selectedFindings) ? '○10' : '10') . '</td>
                    <td>扁桃炎</td>
                    <td>扁桃が炎症を起こし、のどの痛みや発熱の原因になります。</td>
                </tr>
                <tr>
                    <td style="text-align: center;">' . (in_array(11, $selectedFindings) ? '○11' : '11') . '</td>
                    <td>アデノイド</td>
                    <td>鼻の奥のリンパ組織が肥大し、鼻づまりやいびきの原因になります。</td>
                </tr>
            </table>
            
            <h3 style="font-size: 11pt; margin-top: 10px; margin-bottom: 5px;">【口腔】</h3>
            <table border="1" cellpadding="3" style="width: 100%; margin-bottom: 10px; font-size: 8pt; border-collapse: collapse;">
                <tr>
                    <th style="background-color: #e0e0e0; width: 8%; text-align: center;">番号</th>
                    <th style="background-color: #e0e0e0; width: 22%;">所見項目</th>
                    <th style="background-color: #e0e0e0;">説明</th>
                </tr>
                <tr>
                    <td style="text-align: center;">' . (in_array(12, $selectedFindings) ? '○12' : '12') . '</td>
                    <td>口内炎</td>
                    <td>口の中に炎症があり、痛みを伴います。</td>
                </tr>
                <tr>
                    <td style="text-align: center;">' . (in_array(13, $selectedFindings) ? '○13' : '13') . '</td>
                    <td>舌小帯異常</td>
                    <td>舌の裏のひもが短く、舌の動きに影響することがあります。</td>
                </tr>
                <tr>
                    <td style="text-align: center;">' . (in_array(14, $selectedFindings) ? '○14' : '14') . '</td>
                    <td>舌異常</td>
                    <td>舌の形や色、動きに異常がみられます。</td>
                </tr>
            </table>
            
            <h3 style="font-size: 11pt; margin-top: 10px; margin-bottom: 5px;">【その他】</h3>
            <table border="1" cellpadding="3" style="width: 100%; margin-bottom: 15px; font-size: 8pt; border-collapse: collapse;">
                <tr>
                    <th style="background-color: #e0e0e0; width: 8%; text-align: center;">番号</th>
                    <th style="background-color: #e0e0e0; width: 22%;">所見項目</th>
                    <th style="background-color: #e0e0e0;">説明</th>
                </tr>
                <tr>
                    <td style="text-align: center;">' . (in_array(15, $selectedFindings) ? '○15' : '15') . '</td>
                    <td>その他</td>
                    <td>その他の耳鼻咽喉科的所見があります。</td>
                </tr>
            </table>
            
            <div style="margin: 15px 0; font-size: 9pt;">
                検診実施日：' . htmlspecialchars($formData['exam_date_year'] ?? '') . '年' . htmlspecialchars($formData['exam_date_month'] ?? '') . '月' . htmlspecialchars($formData['exam_date_day'] ?? '') . '日　　
                校医名：' . htmlspecialchars($formData['doctor_name'] ?? '') . '
            </div>
            
            <div style="border-top: 2px dashed #666; margin: 20px 0;"></div>
            
            <h3 style="text-align: center; background-color: #f5f5f5; border: 2px solid #333; padding: 5px; font-size: 11pt;">耳鼻咽喉科受診連絡表</h3>
            
            <table border="1" cellpadding="5" style="width: 100%; margin-top: 10px; font-size: 9pt; border-collapse: collapse;">
                <tr>
                    <td style="background-color: #f0f0f0; width: 25%; text-align: center;"><strong>病名</strong></td>
                    <td>' . htmlspecialchars($formData['followup_disease'] ?? '') . '</td>
                </tr>
                <tr>
                    <td style="background-color: #f0f0f0; text-align: center;"><strong>受診日</strong></td>
                    <td>' . htmlspecialchars($formData['followup_visit_year'] ?? '') . '年' . htmlspecialchars($formData['followup_visit_month'] ?? '') . '月' . htmlspecialchars($formData['followup_visit_day'] ?? '') . '日</td>
                </tr>
                <tr>
                    <td style="background-color: #f0f0f0; text-align: center;"><strong>医療機関名</strong></td>
                    <td>' . htmlspecialchars($formData['followup_clinic_name'] ?? '') . '</td>
                </tr>
                <tr>
                    <td style="background-color: #f0f0f0; text-align: center;"><strong>保護者氏名</strong></td>
                    <td>' . htmlspecialchars($formData['followup_parent_name'] ?? '') . '　　印</td>
                </tr>
            </table>
        </div>';
        
        return $html;
    }
}
