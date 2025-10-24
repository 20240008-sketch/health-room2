-- attendance_records テーブルの student_id を学籍番号に変換
BEGIN TRANSACTION;

-- 1. バックアップテーブルを作成
CREATE TABLE attendance_records_backup AS SELECT * FROM attendance_records;

-- 2. 古いテーブルを削除
DROP TABLE attendance_records;

-- 3. 新しいテーブルを作成（student_id を VARCHAR 型に変更）
CREATE TABLE attendance_records (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    student_id VARCHAR NOT NULL,
    date DATE NOT NULL,
    status VARCHAR NOT NULL DEFAULT 'present',
    arrival_time TIME,
    departure_time TIME,
    notes TEXT,
    created_at DATETIME,
    updated_at DATETIME
);

-- 4. データをコピー（内部ID → 学籍番号）
INSERT INTO attendance_records 
SELECT 
    ar.id,
    s.student_id,
    ar.date,
    ar.status,
    ar.arrival_time,
    ar.departure_time,
    ar.notes,
    ar.created_at,
    ar.updated_at
FROM attendance_records_backup ar
JOIN students s ON ar.student_id = s.id;

-- 5. バックアップテーブルを削除
DROP TABLE attendance_records_backup;

COMMIT;

-- 6. 確認
SELECT id, date, student_id FROM attendance_records LIMIT 10;
