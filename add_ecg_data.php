<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\HealthRecord;

// クラス25111の学生の最新記録に心電図データを追加
$records = HealthRecord::whereHas('student', function($query) {
    $query->where('class_id', '25111');
})
->orderBy('created_at', 'desc')
->limit(10)
->get();

echo "Found " . $records->count() . " records\n";

foreach($records as $record) {
    if (!$record->ecg_result || $record->ecg_result === 'null' || $record->ecg_result === '[]') {
        // ランダムな心電図結果を生成
        $results = ['正常', '正常', '正常', '軽度の不整脈', '要観察'];
        $diagnoses = ['異常なし', '異常なし', '異常なし', '経過観察', '再検査推奨'];
        
        $rand = rand(0, 4);
        
        $ecgData = json_encode([[
            'exam_result' => $results[$rand],
            'diagnosis' => $diagnoses[$rand],
            'treatment' => $rand > 2 ? '経過観察' : ''
        ]]);
        
        $record->ecg_result = $ecgData;
        $record->save();
        
        echo "Added ECG to record {$record->id}: {$results[$rand]}\n";
    } else {
        echo "Record {$record->id} already has ECG data\n";
    }
}

echo "Done!\n";
