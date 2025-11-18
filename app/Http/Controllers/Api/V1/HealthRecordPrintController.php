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
            } elseif ($data['exam_type'] === 'ophthalmology') {
                $html = $this->generateOphthalmologyHtml($data);
            } elseif ($data['exam_type'] === 'internal_medicine') {
                $html = $this->generateInternalMedicineHtml($data);
            } elseif ($data['exam_type'] === 'hearing_test') {
                $html = $this->generateHearingTestHtml($data);
            } elseif ($data['exam_type'] === 'dental') {
                $html = $this->generateDentalHtml($data);
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
    
    private function generateOphthalmologyHtml($data)
    {
        $student = $data['student'];
        $healthRecord = $data['health_record'] ?? [];
        
        // 現在の日付を取得
        $now = new \DateTime();
        $warekiYear = $now->format('Y') - 2018; // 令和年
        $year = $now->format('Y');
        $month = $now->format('n');
        $day = $now->format('j');
        
        // 学年情報を取得
        $classId = $student['school_class']['class_id'] ?? '';
        $gradeId = strlen($classId) >= 3 ? substr($classId, 1, 1) : '';
        $courseCode = strlen($classId) >= 4 ? substr($classId, 2, 1) : '';
        $classNumber = strlen($classId) >= 5 ? substr($classId, 4, 1) : '';
        
        $courseMap = [
            '1' => '特別進学',
            '2' => '進学',
            '3' => '総合',
            '4' => '情報会計',
            '5' => '福祉'
        ];
        $courseName = $courseMap[$courseCode] ?? '';
        
        $studentNumber = $student['student_number'] ?? '';
        
        // 健康記録から眼科検診結果を取得
        $examResult = $healthRecord['ophthalmology_exam_result'] ?? '';
        
        // 各項目をチェック（複数可）
        $checks = [
            1 => false, 2 => false, 3 => false, 4 => false, 5 => false,
            6 => false, 7 => false, 8 => false, 9 => false
        ];
        
        if (!empty($examResult)) {
            if (strpos($examResult, 'アレルギー性結膜炎') !== false) $checks[1] = true;
            if (strpos($examResult, '結膜炎') !== false && strpos($examResult, 'アレルギー性結膜炎') === false) $checks[2] = true;
            if (strpos($examResult, '麦粒腫') !== false) $checks[3] = true;
            if (strpos($examResult, '眼瞼縁炎') !== false) $checks[4] = true;
            if (strpos($examResult, '霰粒腫') !== false) $checks[5] = true;
            if (strpos($examResult, '内反症') !== false) $checks[6] = true;
            if (strpos($examResult, '斜視') !== false) $checks[7] = true;
            if (strpos($examResult, '斜位') !== false) $checks[8] = true;
        }
        
        $circle = function($num) use ($checks) {
            return $checks[$num] ? '◯' : '　';
        };
        
        $html = '
        <style>
            body { font-family: "seieiIPexMincho", serif; font-size: 10pt; line-height: 1.8; }
            .header { text-align: right; margin-bottom: 10px; }
            .school-name { font-size: 12pt; font-weight: bold; margin-bottom: 3px; }
            .principal { font-size: 10pt; }
            h1 { text-align: center; font-size: 14pt; font-weight: bold; margin: 20px 0; }
            .student-info { margin: 15px 0; font-size: 11pt; }
            .notice-text { margin: 15px 0; line-height: 2; }
            table { border-collapse: collapse; width: 100%; margin: 20px 0; }
            th, td { border: 1px solid #000; padding: 8px; }
            th { background-color: #f5f5f5; font-weight: bold; text-align: center; }
            .col-number { width: 40px; text-align: center; }
            .col-disease { width: 150px; }
            .separator { border-top: 2px dashed #000; margin: 30px 0; }
            .report-title { text-align: center; font-size: 12pt; font-weight: bold; margin: 20px 0; }
        </style>
        
        <div style="margin-bottom: 15px;">
            <span style="float: left;">保護者様</span>
            <span style="float: right;">令和' . $warekiYear . '年（' . $year . '年）' . $month . '月' . $day . '日</span>
            <div style="clear: both;"></div>
        </div>
        
        <div class="header">
            <div class="school-name">誠英高等学校</div>
            <div class="principal">校長　大田　真一</div>
        </div>
        
        <h1>眼科検診結果のお知らせ</h1>
        
        <div class="student-info">
            <span>' . htmlspecialchars($gradeId) . '年</span>
            <span>' . htmlspecialchars($courseName) . 'コース</span>
            <span>' . htmlspecialchars($classNumber) . '組</span>
            <span>' . htmlspecialchars($studentNumber) . '番</span>
            <span>' . htmlspecialchars($student['name']) . '</span>
        </div>
        
        <div class="notice-text">
            今年度の眼科検診の結果は下記◯印のとおりです。専門医で受診されますようお勧めします。<br>
            なお、受診後は下欄の連絡表に記入してもらい、切り取らずに学校に提出してください。
        </div>
        
        <table>
            <thead>
                <tr>
                    <th class="col-number"></th>
                    <th class="col-disease">病　　　名</th>
                    <th>説　　　　　明</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="col-number">' . $circle(1) . '１</td>
                    <td class="col-disease">アレルギー性結膜炎</td>
                    <td>かゆみなどの不快感が続くことが多いので、医師にご相談ください。</td>
                </tr>
                <tr>
                    <td class="col-number">' . $circle(2) . '２</td>
                    <td class="col-disease">結膜炎</td>
                    <td>目が赤く充血しています。人にうつることもありますので、早く受診されますようお勧めします。</td>
                </tr>
                <tr>
                    <td class="col-number">' . $circle(3) . '３</td>
                    <td class="col-disease">麦粒腫</td>
                    <td>まぶたが炎症をおこしています。腫れや痛みが進むこともあります。</td>
                </tr>
                <tr>
                    <td class="col-number">' . $circle(4) . '４</td>
                    <td class="col-disease">眼瞼縁炎</td>
                    <td>目の縁が炎症をおこしています。症状が進むと、まつげが抜けたり、結膜炎をおこしやすくなります。</td>
                </tr>
                <tr>
                    <td class="col-number">' . $circle(5) . '５</td>
                    <td class="col-disease">霰粒腫</td>
                    <td>まぶたの中に硬いしこりができています。大きくなるとまぶたが開きにくくなることもあります。</td>
                </tr>
                <tr>
                    <td class="col-number">' . $circle(6) . '６</td>
                    <td class="col-disease">内反症</td>
                    <td>まぶたが内側に入り込んでいます。まつげが角膜をこすると、ゴロゴロして涙がでたり、視力が下がることもあります。</td>
                </tr>
                <tr>
                    <td class="col-number">' . $circle(7) . '７</td>
                    <td class="col-disease">斜視</td>
                    <td>そのままにしておくと弱視になる場合があります。できるだけ早く受診されますようにお勧めします。</td>
                </tr>
                <tr>
                    <td class="col-number">' . $circle(8) . '８</td>
                    <td class="col-disease">斜位</td>
                    <td>眼精疲労を起こしやすく、矯正が必要となる場合もあります。</td>
                </tr>
                <tr>
                    <td class="col-number">' . $circle(9) . '９</td>
                    <td colspan="2">その他（　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　）</td>
                </tr>
            </tbody>
        </table>
        
        <div class="separator"></div>
        
        <div class="report-title">眼　科　受　診　連　絡　票</div>
        
        <table>
            <tr>
                <th style="width: 150px;">診　断　名</th>
                <td></td>
            </tr>
            <tr>
                <th>治療の状況</th>
                <td>
                    １　治療済み　　　２　治療中　　　３　経過観察<br>
                    ４　治療を要しない
                </td>
            </tr>
            <tr>
                <th>運動について</th>
                <td>
                    １水泳可　　　　　２水泳不可　　　３　その他
                </td>
            </tr>
        </table>
        
        <div style="text-align: right; margin-top: 20px;">
            <div>令和　　年　　月　　日</div>
            <div style="margin-top: 15px;">医療機関名　　　　　　　　　　　　　　　　　</div>
            <div style="margin-top: 10px;">保護者氏名　　　　　　　　　　　　　　　　　</div>
        </div>';
        
        return $html;
    }
    
    private function generateInternalMedicineHtml($data)
    {
        $student = $data['student'];
        $healthRecord = $data['health_record'] ?? [];
        
        // 現在の日付を取得
        $now = new \DateTime();
        $warekiYear = $now->format('Y') - 2018;
        $year = $now->format('Y');
        $month = $now->format('n');
        $day = $now->format('j');
        
        // 学年情報を取得
        $classId = $student['school_class']['class_id'] ?? '';
        $gradeId = strlen($classId) >= 3 ? substr($classId, 1, 1) : '';
        $courseCode = strlen($classId) >= 4 ? substr($classId, 2, 1) : '';
        $classNumber = strlen($classId) >= 5 ? substr($classId, 4, 1) : '';
        
        $courseMap = [
            '1' => '特別進学',
            '2' => '進学',
            '3' => '総合',
            '4' => '情報会計',
            '5' => '福祉'
        ];
        $courseName = $courseMap[$courseCode] ?? '';
        
        $studentNumber = $student['student_number'] ?? '';
        
        $html = '
        <style>
            body { font-family: "seieiIPexMincho", serif; font-size: 10pt; line-height: 1.8; }
            .header { text-align: right; margin-bottom: 10px; }
            .school-name { font-size: 12pt; font-weight: bold; margin-bottom: 3px; }
            .principal { font-size: 10pt; }
            h1 { text-align: center; font-size: 14pt; font-weight: bold; margin: 20px 0; }
            .student-info { margin: 15px 0; font-size: 11pt; }
            .notice-text { margin: 15px 0; line-height: 2; }
            table { border-collapse: collapse; width: 100%; margin: 20px 0; }
            th, td { border: 1px solid #000; padding: 8px; }
            th { background-color: #f5f5f5; font-weight: bold; text-align: center; }
            .separator { border-top: 2px dashed #000; margin: 30px 0; }
            .report-title { text-align: center; font-size: 12pt; font-weight: bold; margin: 20px 0; }
        </style>
        
        <div style="margin-bottom: 15px;">
            <span style="float: left;">保護者様</span>
            <span style="float: right;">令和' . $warekiYear . '年（' . $year . '年）' . $month . '月' . $day . '日</span>
            <div style="clear: both;"></div>
        </div>
        
        <div class="header">
            <div class="school-name">誠英高等学校</div>
            <div class="principal">校長　大田　真一</div>
        </div>
        
        <h1>内科検診結果について</h1>
        
        <div class="student-info">
            <span>' . htmlspecialchars($gradeId) . '年</span>
            <span>' . htmlspecialchars($courseName) . 'コース</span>
            <span>' . htmlspecialchars($classNumber) . '組</span>
            <span>' . htmlspecialchars($studentNumber) . '番</span>
            <span>' . htmlspecialchars($student['name']) . '</span>
        </div>
        
        <div class="notice-text">
            今年度の内科検診の結果、医師の診察が必要と思われます。<br>
            なお、受診後は下欄の連絡表に記入してもらい、切り取らずに学校に提出してください。
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>診　断　結　果</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="height: 200px; vertical-align: top;">
                        <div style="height: 50px;"></div>
                        <div style="height: 50px;"></div>
                        <div style="height: 50px;"></div>
                    </td>
                </tr>
            </tbody>
        </table>
        
        <div class="separator"></div>
        
        <div class="report-title">内　科　受　診　連　絡　票</div>
        
        <table>
            <tr>
                <th style="width: 150px;">診　断　名</th>
                <td></td>
            </tr>
            <tr>
                <th>治療の状況</th>
                <td>
                    １　治療済み　　　２　治療中　　　３　経過観察<br>
                    ４　治療を要しない
                </td>
            </tr>
            <tr>
                <th>運動について</th>
                <td>
                    １　通常通り　　　２　制限あり　　３　その他
                </td>
            </tr>
        </table>
        
        <div style="text-align: right; margin-top: 20px;">
            <div>令和　　年　　月　　日</div>
            <div style="margin-top: 15px;">医療機関名　　　　　　　　　　　　　　　　　</div>
            <div style="margin-top: 10px;">保護者氏名　　　　　　　　　　　　　　　　　</div>
        </div>';
        
        return $html;
    }
    
    private function generateHearingTestHtml($data)
    {
        $student = $data['student'];
        $healthRecord = $data['health_record'] ?? [];
        
        // 現在の日付を取得
        $now = new \DateTime();
        $warekiYear = $now->format('Y') - 2018;
        $year = $now->format('Y');
        $month = $now->format('n');
        $day = $now->format('j');
        
        // 学年情報を取得
        $classId = $student['school_class']['class_id'] ?? '';
        $gradeId = strlen($classId) >= 3 ? substr($classId, 1, 1) : '';
        $courseCode = strlen($classId) >= 4 ? substr($classId, 2, 1) : '';
        $classNumber = strlen($classId) >= 5 ? substr($classId, 4, 1) : '';
        
        $courseMap = [
            '1' => '特別進学',
            '2' => '進学',
            '3' => '総合',
            '4' => '情報会計',
            '5' => '福祉'
        ];
        $courseName = $courseMap[$courseCode] ?? '';
        
        $studentNumber = $student['student_number'] ?? '';
        
        // 聴力検査結果から右耳・左耳の判定
        $hearingTestResult = $healthRecord['hearing_test_result'] ?? '';
        $isRightEar = strpos($hearingTestResult, '右') !== false;
        $isLeftEar = strpos($hearingTestResult, '左') !== false;
        
        $html = '
        <style>
            body { font-family: "seieiIPexMincho", serif; font-size: 10pt; line-height: 1.6; }
            .date { text-align: right; margin-bottom: 10px; }
            .header { margin-bottom: 15px; }
            .school-info { text-align: right; }
            h1 { text-align: center; font-size: 12pt; font-weight: bold; margin: 15px 0; }
            .student-info { margin: 10px 0; }
            .notice-text { margin: 15px 0; line-height: 1.8; }
            table { border-collapse: collapse; width: 100%; margin: 15px 0; }
            th, td { border: 1px solid #000; padding: 8px; }
            th { background-color: #f5f5f5; font-weight: bold; }
            .separator { border-top: 2px dashed #000; margin: 25px 0; }
            .report-title { text-align: center; font-size: 12pt; font-weight: bold; margin: 15px 0; }
        </style>
        
        <div class="date">令和' . $warekiYear . '年' . $month . '月' . $day . '日</div>
        
        <div class="student-info">
            ' . htmlspecialchars($gradeId) . '年　普通　科　' . htmlspecialchars($classNumber) . '組　' . htmlspecialchars($studentNumber) . '番<br>
            （' . ($courseName === '特別進学' ? '<u>特別進学</u>' : '特別進学') . '・' . 
            ($courseName === '進学' ? '<u>進学</u>' : '進学') . '・' . 
            ($courseName === '総合' ? '<u>総合</u>' : '総合') . '）<br>
            情報会計科<br>
            福祉　科
        </div>
        
        <div>生徒　' . htmlspecialchars($student['name']) . '</div>
        <div style="margin: 10px 0;">保　護　者　様</div>
        
        <div class="school-info">
            誠英高等学校<br>
            校長　大田　真一
        </div>
        
        <h1>聴力検査について（お知らせ）</h1>
        
        <div class="notice-text">
            検査の結果、（' . ($isRightEar ? '<u>右</u>' : '右') . '　・　' . ($isLeftEar ? '<u>左</u>' : '左') . '）の耳がやや聞こえにくいように思いますので、一度専門医の診断を受けることをお勧めします。<br>
            なお、結果については担当医に下記報告書を依頼し、学校に提出してください。
        </div>
        
        <div class="separator"></div>
        
        <div class="report-title">報　　　告　　　書</div>
        
        <div style="text-align: right; margin-bottom: 15px;">令和　　年　　月　　日</div>
        
        <div style="margin-bottom: 15px;">
            ' . htmlspecialchars($gradeId) . '年　普通科（' . htmlspecialchars($courseName) . 'コース）　' . htmlspecialchars($classNumber) . '組<br>
            情報会計科・福祉科　　組<br>
            氏名　' . htmlspecialchars($student['name']) . '
        </div>
        
        <table>
            <tr>
                <td>１．異常を認めず</td>
            </tr>
            <tr>
                <td>２．異常あり（　　　　　　　　　　　　　　　）</td>
            </tr>
            <tr>
                <td style="height: 80px; vertical-align: top;">３．指導または留意事項</td>
            </tr>
        </table>
        
        <div style="text-align: right; margin-top: 30px;">
            <div>医療機関名　　　　　　　　　　　　　　　　　</div>
            <div style="margin-top: 10px;">医    師   名　　　　　　　　　　　　　印</div>
        </div>';
        
        return $html;
    }
    
    private function generateDentalHtml($data)
    {
        $student = $data['student'];
        $healthRecord = $data['health_record'] ?? [];
        
        // 現在の日付を取得
        $now = new \DateTime();
        $warekiYear = $now->format('Y') - 2018;
        $year = $now->format('Y');
        $month = $now->format('n');
        $day = $now->format('j');
        
        // 学年情報を取得
        $classId = $student['school_class']['class_id'] ?? '';
        $gradeId = strlen($classId) >= 3 ? substr($classId, 1, 1) : '';
        $courseCode = strlen($classId) >= 4 ? substr($classId, 2, 1) : '';
        $classNumber = strlen($classId) >= 5 ? substr($classId, 4, 1) : '';
        
        $courseMap = [
            '1' => '特別進学',
            '2' => '進学',
            '3' => '総合',
            '4' => '情報会計',
            '5' => '福祉'
        ];
        $courseName = $courseMap[$courseCode] ?? '';
        
        $studentNumber = $student['student_number'] ?? '';
        
        // 歯科検診結果を取得
        $dentalResult = $healthRecord['dental_result'] ?? '';
        
        // A群とB群のチェック
        $checksA = [1 => false, 2 => false, 3 => false, 4 => false, 5 => false, 6 => false];
        $checksB = [1 => false, 2 => false, 3 => false, 4 => false, 5 => false];
        $checkC = false;
        
        if (!empty($dentalResult)) {
            // A群のチェック
            if (strpos($dentalResult, '虫歯') !== false || strpos($dentalResult, 'むし歯') !== false) $checksA[1] = true;
            if (strpos($dentalResult, '歯並び') !== false || strpos($dentalResult, 'かみ合わせ') !== false || strpos($dentalResult, '顎関節') !== false) $checksA[2] = true;
            if (strpos($dentalResult, '歯垢が相当') !== false) $checksA[3] = true;
            if (strpos($dentalResult, '歯肉炎') !== false) $checksA[4] = true;
            if (strpos($dentalResult, '要注意乳歯') !== false) $checksA[5] = true;
            
            // B群のチェック
            if (strpos($dentalResult, '虫歯になりかけ') !== false) $checksB[1] = true;
            if (strpos($dentalResult, '定期的な観察') !== false) $checksB[2] = true;
            if (strpos($dentalResult, '歯垢が若干') !== false) $checksB[3] = true;
            if (strpos($dentalResult, '軽い歯肉炎') !== false) $checksB[4] = true;
            
            // C: 異常なし
            if (strpos($dentalResult, '異常なし') !== false || strpos($dentalResult, '異常はありません') !== false) $checkC = true;
        }
        
        $circleA = function($num) use ($checksA) {
            return $checksA[$num] ? '◯' : '　';
        };
        
        $circleB = function($num) use ($checksB) {
            return $checksB[$num] ? '◯' : '　';
        };
        
        $html = '
        <style>
            body { font-family: "seieiIPexMincho", serif; font-size: 10pt; line-height: 1.6; }
            .header { margin-bottom: 10px; }
            .school-info { text-align: right; }
            h1 { text-align: center; font-size: 13pt; font-weight: bold; margin: 15px 0; }
            .student-info { margin: 10px 0; }
            .notice-text { margin: 15px 0; line-height: 1.8; }
            .section-title { font-weight: bold; margin: 15px 0 5px 0; }
            .item { margin: 5px 0 5px 20px; }
            .separator { border-top: 2px dashed #000; margin: 25px 0; }
            .report-title { text-align: center; font-size: 12pt; font-weight: bold; margin: 15px 0; }
        </style>
        
        <div style="margin-bottom: 15px;">
            <span style="float: left;">保護者様</span>
            <span style="float: right;">令和' . $warekiYear . '年（' . $year . '年）' . $month . '月' . $day . '日</span>
            <div style="clear: both;"></div>
        </div>
        
        <div class="school-info">
            誠英高等学校<br>
            校長　大田　真一
        </div>
        
        <h1>歯　科　検　診　結　果　の　お　知　ら　せ</h1>
        
        <div class="student-info">
            ' . htmlspecialchars($gradeId) . '年　' . htmlspecialchars($courseName) . 'コース　' . htmlspecialchars($classNumber) . '組　' . htmlspecialchars($studentNumber) . '番　' . htmlspecialchars($student['name']) . '
        </div>
        
        <div class="notice-text">
            今年度の歯科検診の結果は下記◯印のとおりです。専門医の指導や治療が必要な人は、できるだけ早く受診されますようお勧めします。<br>
            なお、受診後は下欄の連絡表に記入してもらい、切り取らずに学校に提出してください。
        </div>
        
        <div class="section-title">A・専門医に相談し治療の指示を受けてください</div>
        <div class="item">' . $circleA(1) . '１　虫歯があります</div>
        <div class="item">' . $circleA(2) . '２　歯並びやかみ合わせ、顎関節について相談してください</div>
        <div class="item">' . $circleA(3) . '３　歯垢が相当付着しています</div>
        <div class="item">' . $circleA(4) . '４　歯肉炎があります</div>
        <div class="item">' . $circleA(5) . '５　要注意乳歯があります</div>
        <div class="item">' . $circleA(6) . '６　その他（　　　　　　　　　　　　　　　　　　　　　　　　　　　）</div>
        
        <div class="section-title" style="margin-top: 20px;">B・今すぐ治療の必要はありませんが、定期的な観察をしてください</div>
        <div class="item">' . $circleB(1) . '１　虫歯になりかけている歯があります</div>
        <div class="item">' . $circleB(2) . '２　歯並びやかみ合わせ、顎関節について定期的な観察が必要です</div>
        <div class="item">' . $circleB(3) . '３　歯垢が若干付着しています</div>
        <div class="item">' . $circleB(4) . '４　軽い歯肉炎があります</div>
        <div class="item">' . $circleB(5) . '５　その他（　　　　　　　　　　　　　　　　　　　　　　　　　　　）</div>
        
        <div class="section-title" style="margin-top: 20px;">' . ($checkC ? '◯' : '　') . 'C・今回の検査で異常はありません</div>
        
        <div class="separator"></div>
        
        <div class="report-title">歯科検診受診票</div>
        
        <div style="margin: 15px 0;">
            <div style="font-weight: bold; margin-bottom: 10px;">診察結果</div>
            <div style="margin-left: 20px;">
                <div>１　治療済み</div>
                <div>２　ブラッシング指導</div>
                <div>３　経過観察</div>
                <div>４　治療を要しない</div>
                <div>５　その他（　　　　　　　　　　　　　　　　　　　　　　　　　　　　）</div>
            </div>
        </div>
        
        <div style="text-align: right; margin-top: 30px;">
            <div>令和　　年　　月　　日</div>
            <div style="margin-top: 15px;">医療機関名　　　　　　　　　　　　　　　　　</div>
            <div style="margin-top: 10px;">保護者氏名　　　　　　　　　　　　　　　　　</div>
        </div>';
        
        return $html;
    }
}
