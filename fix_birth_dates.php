<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Student;

echo "生年月日の修正を開始します...\n\n";

// 全学生を取得
$students = Student::all();

$updated = 0;
$skipped = 0;

foreach ($students as $student) {
    // class_idから学年を取得（例: "24221" -> 2年生）
    $classId = $student->class_id;
    if (empty($classId) || strlen($classId) < 3) {
        echo "スキップ: {$student->name} (class_id: {$classId})\n";
        $skipped++;
        continue;
    }
    
    // class_idの2文字目が学年
    $grade = (int)substr($classId, 1, 1);
    
    // 現在の日付が不正（2025年）かチェック
    $birthYear = date('Y', strtotime($student->birth_date));
    
    if ($birthYear >= 2024) {
        // 学年に応じた生年を計算
        // 現在2025年10月、高校生として
        // 1年生: 2010年生まれ (15-16歳)
        // 2年生: 2009年生まれ (16-17歳)
        // 3年生: 2008年生まれ (17-18歳)
        // 4年生以降は専門学校と仮定
        
        $birthYear = 2011 - $grade; // 1年生=2010, 2年生=2009, 3年生=2008
        
        // ランダムな月日を生成（4月2日〜翌年4月1日の範囲）
        $month = rand(4, 12);
        if ($month == 4) {
            $day = rand(2, 30);
        } elseif (in_array($month, [4, 6, 9, 11])) {
            $day = rand(1, 30);
        } elseif ($month == 2) {
            $day = rand(1, 28);
        } else {
            $day = rand(1, 31);
        }
        
        $newBirthDate = sprintf('%04d-%02d-%02d', $birthYear, $month, $day);
        
        $student->birth_date = $newBirthDate;
        $student->save();
        
        echo "更新: {$student->name} ({$student->class_id}) -> {$newBirthDate}\n";
        $updated++;
    } else {
        $skipped++;
    }
}

echo "\n完了しました。\n";
echo "更新: {$updated}件\n";
echo "スキップ: {$skipped}件\n";
