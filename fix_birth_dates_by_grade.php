<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Student;

echo "学年に応じた生年月日の修正を開始します...\n\n";

// 2025年10月時点の高校生の正しい生年月日範囲
// 1年生: 2009年4月2日～2010年4月1日生まれ（15-16歳）
// 2年生: 2008年4月2日～2009年4月1日生まれ（16-17歳）
// 3年生: 2007年4月2日～2008年4月1日生まれ（17-18歳）

$gradeRanges = [
    '1' => ['start' => 2009, 'end' => 2010],
    '2' => ['start' => 2008, 'end' => 2009],
    '3' => ['start' => 2007, 'end' => 2008],
];

$updated = 0;
$skipped = 0;

foreach (['1', '2', '3'] as $grade) {
    $students = Student::where('grade_id', $grade)->get();
    
    echo "{$grade}年生: {$students->count()}名\n";
    
    foreach ($students as $student) {
        $originalDate = new DateTime($student->birth_date);
        $currentBirthYear = (int)$originalDate->format('Y');
        $month = (int)$originalDate->format('m');
        $day = (int)$originalDate->format('d');
        
        $expectedStartYear = $gradeRanges[$grade]['start'];
        $expectedEndYear = $gradeRanges[$grade]['end'];
        
        // 正しい生年を決定
        // 4月2日以降は開始年、4月1日以前は終了年
        if ($month > 4 || ($month == 4 && $day >= 2)) {
            $correctYear = $expectedStartYear;
        } else {
            $correctYear = $expectedEndYear;
        }
        
        // 生年が異なる場合のみ修正
        if ($currentBirthYear != $correctYear) {
            $oldBirthDate = $student->birth_date->format('Y-m-d');
            $newBirthDate = sprintf('%04d-%02d-%02d', $correctYear, $month, $day);
            
            $student->birth_date = $newBirthDate;
            $student->save();
            
            echo "  更新: {$student->name} ({$oldBirthDate} → {$newBirthDate})\n";
            $updated++;
        } else {
            $skipped++;
        }
    }
    
    echo "\n";
}

echo "完了しました。\n";
echo "更新: {$updated}件\n";
echo "スキップ: {$skipped}件\n";
