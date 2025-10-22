<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Student;

echo "学生のgrade_idを修正します...\n\n";

// grade_idが空または未設定の学生を取得
$students = Student::whereNull('grade_id')
    ->orWhere('grade_id', '')
    ->get();

echo "対象学生数: " . $students->count() . "名\n\n";

$updated = 0;
$skipped = 0;

foreach ($students as $student) {
    // class_idから学年を抽出（3文字目）
    if (!empty($student->class_id) && strlen($student->class_id) >= 3) {
        $grade = substr($student->class_id, 2, 1);
        
        // 学年が1-3の範囲内か確認
        if (in_array($grade, ['1', '2', '3'])) {
            $student->grade_id = $grade;
            $student->save();
            
            echo "更新: {$student->name} (class_id: {$student->class_id}) -> grade_id: {$grade}\n";
            $updated++;
        } else {
            echo "スキップ: {$student->name} (class_id: {$student->class_id}) - 学年が不明\n";
            $skipped++;
        }
    } else {
        echo "スキップ: {$student->name} - class_idが未設定\n";
        $skipped++;
    }
}

echo "\n完了しました。\n";
echo "更新: {$updated}件\n";
echo "スキップ: {$skipped}件\n";
