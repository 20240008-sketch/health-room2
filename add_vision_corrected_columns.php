<?php

// SQLiteデータベースに矯正視力カラムを追加するスクリプト

$dbPath = __DIR__ . '/database/database.sqlite';

if (!file_exists($dbPath)) {
    echo "Error: Database file not found at $dbPath\n";
    exit(1);
}

try {
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // カラムが存在するか確認
    $stmt = $pdo->query("PRAGMA table_info(health_records)");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $hasVisionLeftCorrected = false;
    $hasVisionRightCorrected = false;
    
    foreach ($columns as $column) {
        if ($column['name'] === 'vision_left_corrected') {
            $hasVisionLeftCorrected = true;
        }
        if ($column['name'] === 'vision_right_corrected') {
            $hasVisionRightCorrected = true;
        }
    }
    
    // カラムを追加
    if (!$hasVisionLeftCorrected) {
        $pdo->exec("ALTER TABLE health_records ADD COLUMN vision_left_corrected REAL");
        echo "Added vision_left_corrected column\n";
    } else {
        echo "vision_left_corrected column already exists\n";
    }
    
    if (!$hasVisionRightCorrected) {
        $pdo->exec("ALTER TABLE health_records ADD COLUMN vision_right_corrected REAL");
        echo "Added vision_right_corrected column\n";
    } else {
        echo "vision_right_corrected column already exists\n";
    }
    
    echo "Success! Vision corrected columns are ready.\n";
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
