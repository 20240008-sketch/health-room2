<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);

use Illuminate\Http\Request;
use App\Models\Student;

echo "APIエンドポイントのテスト開始..." . PHP_EOL . PHP_EOL;

// 学生IDを取得
$students = Student::take(5)->get();
echo "テスト用学生: " . count($students) . "人" . PHP_EOL . PHP_EOL;

// 一括保存APIのテスト
echo "=== 一括保存APIのテスト ===" . PHP_EOL;

$records = [];
foreach ($students as $index => $student) {
    $records[] = [
        'student_id' => $student->id,
        'status' => $index % 4 === 0 ? 'absent' : 'present',
        'arrival_time' => $index % 4 === 1 ? '09:15' : null,
        'departure_time' => $index % 4 === 2 ? '14:30' : null,
        'notes' => "テスト記録 {$index}"
    ];
}

$request = Request::create('/api/v1/attendance-records/bulk', 'POST', [
    'date' => '2025-10-20',
    'records' => $records
]);

$request->headers->set('Accept', 'application/json');
$request->headers->set('Content-Type', 'application/json');

$response = $kernel->handle($request);
$result = json_decode($response->getContent(), true);

echo "ステータスコード: " . $response->getStatusCode() . PHP_EOL;
echo "レスポンス: " . PHP_EOL;
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;

echo PHP_EOL . "=== 取得APIのテスト ===" . PHP_EOL;

$request = Request::create('/api/v1/attendance-records?date=2025-10-20', 'GET');
$request->headers->set('Accept', 'application/json');

$response = $kernel->handle($request);
$result = json_decode($response->getContent(), true);

echo "ステータスコード: " . $response->getStatusCode() . PHP_EOL;
echo "取得件数: " . count($result['data'] ?? []) . PHP_EOL;
echo "統計情報: " . json_encode($result['statistics'] ?? [], JSON_UNESCAPED_UNICODE) . PHP_EOL;

echo PHP_EOL . "✓ APIテスト完了!" . PHP_EOL;
