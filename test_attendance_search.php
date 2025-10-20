<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\AttendanceRecord;
use App\Models\Student;

echo "学生名検索機能のテスト" . PHP_EOL;
echo "===================" . PHP_EOL . PHP_EOL;

// テストデータの作成
echo "1. テストデータを作成中..." . PHP_EOL;
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
    echo "   - {$student->last_name} {$student->first_name} (出席番号: {$student->student_number})" . PHP_EOL;
}

echo PHP_EOL . "2. 検索テスト" . PHP_EOL;

// テスト1: 苗字で検索
echo PHP_EOL . "   [テスト1] 苗字で検索 (例: 最初の学生の苗字)" . PHP_EOL;
$firstStudent = $students->first();
$searchTerm = $firstStudent->last_name;
echo "   検索語: {$searchTerm}" . PHP_EOL;

$results = AttendanceRecord::with('student')
    ->whereHas('student', function ($q) use ($searchTerm) {
        $q->where('last_name', 'like', '%' . $searchTerm . '%');
    })
    ->get();

echo "   結果: " . count($results) . "件" . PHP_EOL;
foreach ($results as $record) {
    echo "     - {$record->student->last_name} {$record->student->first_name} ({$record->status})" . PHP_EOL;
}

// テスト2: 出席番号で検索
echo PHP_EOL . "   [テスト2] 出席番号で検索" . PHP_EOL;
$searchNumber = $students[1]->student_number;
echo "   検索語: {$searchNumber}" . PHP_EOL;

$results = AttendanceRecord::with('student')
    ->whereHas('student', function ($q) use ($searchNumber) {
        $q->where('student_number', 'like', '%' . $searchNumber . '%');
    })
    ->get();

echo "   結果: " . count($results) . "件" . PHP_EOL;
foreach ($results as $record) {
    echo "     - {$record->student->student_number}: {$record->student->last_name} {$record->student->first_name}" . PHP_EOL;
}

// テスト3: 複合検索（姓名連結）
echo PHP_EOL . "   [テスト3] フルネームで検索" . PHP_EOL;
$fullName = $firstStudent->last_name . $firstStudent->first_name;
echo "   検索語: {$fullName}" . PHP_EOL;

$results = AttendanceRecord::with('student')
    ->whereHas('student', function ($q) use ($fullName) {
        $q->whereRaw("CONCAT(last_name, first_name) LIKE ?", ['%' . $fullName . '%']);
    })
    ->get();

echo "   結果: " . count($results) . "件" . PHP_EOL;
foreach ($results as $record) {
    echo "     - {$record->student->last_name}{$record->student->first_name}" . PHP_EOL;
}

echo PHP_EOL . "✓ テスト完了!" . PHP_EOL;
