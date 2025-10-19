<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\AttendanceRecord;
use App\Models\Student;

echo "テスト開始..." . PHP_EOL;

// テーブルをクリア
AttendanceRecord::truncate();
echo "テーブルをクリアしました" . PHP_EOL . PHP_EOL;

// 学生を取得
$students = Student::take(3)->get();
echo count($students) . "人の学生を取得しました" . PHP_EOL . PHP_EOL;

// 出席記録を作成
echo "出席記録を作成中..." . PHP_EOL;
foreach ($students as $student) {
    $record = AttendanceRecord::create([
        'student_id' => $student->id,
        'date' => '2025-10-19',
        'status' => 'present',
        'arrival_time' => '09:00',
        'notes' => 'テスト記録'
    ]);
    echo "  ✓ Record ID {$record->id} - {$student->name} - Status: {$record->status}" . PHP_EOL;
}

echo PHP_EOL . "保存されたレコードを確認中..." . PHP_EOL;
$savedRecords = AttendanceRecord::with('student')->get();
echo "合計 " . count($savedRecords) . " 件のレコードが保存されています" . PHP_EOL . PHP_EOL;

foreach ($savedRecords as $record) {
    echo "ID: {$record->id}" . PHP_EOL;
    echo "  学生: {$record->student->name}" . PHP_EOL;
    echo "  日付: {$record->date}" . PHP_EOL;
    echo "  状態: {$record->status}" . PHP_EOL;
    echo "  到着時刻: " . ($record->arrival_time ?? 'なし') . PHP_EOL;
    echo "  備考: " . ($record->notes ?? 'なし') . PHP_EOL;
    echo PHP_EOL;
}

echo "✓ テスト完了!" . PHP_EOL;
