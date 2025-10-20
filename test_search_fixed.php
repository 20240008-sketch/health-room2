<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\AttendanceRecord;
use App\Models\Student;

echo "✨ 学生名検索機能のテスト（修正版）" . PHP_EOL;
echo "=====================================" . PHP_EOL . PHP_EOL;

// テストデータの作成
echo "📝 1. テストデータを作成中..." . PHP_EOL;
AttendanceRecord::truncate();

$students = Student::take(10)->get();
echo "   取得した学生: " . count($students) . "人" . PHP_EOL . PHP_EOL;

foreach ($students as $index => $student) {
    AttendanceRecord::create([
        'student_id' => $student->id,
        'date' => '2025-10-20',
        'status' => $index % 2 === 0 ? 'present' : 'absent',
        'notes' => "テストデータ {$index}"
    ]);
    echo "   ✓ {$student->name} (出席番号: {$student->student_number}, ID: {$student->student_id})" . PHP_EOL;
}

echo PHP_EOL . "🔍 2. 検索テスト開始" . PHP_EOL;
echo "==================" . PHP_EOL;

// テスト1: 名前の一部で検索
echo PHP_EOL . "📌 [テスト1] 名前の一部で検索" . PHP_EOL;
$firstStudent = $students->first();
$searchTerm = mb_substr($firstStudent->name, 0, 2); // 最初の2文字
echo "   検索語: 「{$searchTerm}」" . PHP_EOL;

$results = AttendanceRecord::with('student')
    ->whereHas('student', function ($q) use ($searchTerm) {
        $q->where('name', 'like', '%' . $searchTerm . '%');
    })
    ->get();

echo "   ✓ 結果: " . count($results) . "件" . PHP_EOL;
foreach ($results as $record) {
    echo "      → {$record->student->name} ({$record->status})" . PHP_EOL;
}

// テスト2: かな名で検索
echo PHP_EOL . "📌 [テスト2] かな名で検索" . PHP_EOL;
$searchKana = mb_substr($firstStudent->name_kana, 0, 3);
echo "   検索語: 「{$searchKana}」" . PHP_EOL;

$results = AttendanceRecord::with('student')
    ->whereHas('student', function ($q) use ($searchKana) {
        $q->where('name_kana', 'like', '%' . $searchKana . '%');
    })
    ->get();

echo "   ✓ 結果: " . count($results) . "件" . PHP_EOL;
foreach ($results as $record) {
    echo "      → {$record->student->name} ({$record->student->name_kana})" . PHP_EOL;
}

// テスト3: 出席番号で検索
echo PHP_EOL . "📌 [テスト3] 出席番号で検索" . PHP_EOL;
$searchNumber = $students[2]->student_number;
echo "   検索語: 「{$searchNumber}」" . PHP_EOL;

$results = AttendanceRecord::with('student')
    ->whereHas('student', function ($q) use ($searchNumber) {
        $q->where('student_number', 'like', '%' . $searchNumber . '%');
    })
    ->get();

echo "   ✓ 結果: " . count($results) . "件" . PHP_EOL;
foreach ($results as $record) {
    echo "      → 出席番号{$record->student->student_number}: {$record->student->name}" . PHP_EOL;
}

// テスト4: 複合検索（OR条件）
echo PHP_EOL . "📌 [テスト4] 複合検索（名前 OR かな OR 番号）" . PHP_EOL;
$searchTerm = mb_substr($students[1]->name, 1, 1); // 2番目の学生の名前から1文字
echo "   検索語: 「{$searchTerm}」" . PHP_EOL;

$results = AttendanceRecord::with('student')
    ->whereHas('student', function ($q) use ($searchTerm) {
        $q->where(function($query) use ($searchTerm) {
            $query->where('name', 'like', '%' . $searchTerm . '%')
                ->orWhere('name_kana', 'like', '%' . $searchTerm . '%')
                ->orWhere('student_number', 'like', '%' . $searchTerm . '%');
        });
    })
    ->get();

echo "   ✓ 結果: " . count($results) . "件" . PHP_EOL;
foreach ($results as $record) {
    echo "      → {$record->student->name} (#{$record->student->student_number})" . PHP_EOL;
}

echo PHP_EOL . "✅ テスト完了!" . PHP_EOL;
echo "   合計 " . AttendanceRecord::count() . " 件のレコードが作成されました" . PHP_EOL;
