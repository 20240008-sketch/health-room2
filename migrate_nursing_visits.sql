-- nursing_visits テーブルの student_id を学籍番号に変換
BEGIN TRANSACTION;

-- 1. バックアップテーブルを作成
CREATE TABLE nursing_visits_backup AS SELECT * FROM nursing_visits;

-- 2. 古いテーブルを削除
DROP TABLE nursing_visits;

-- 3. 新しいテーブルを作成（student_id を VARCHAR 型に変更）
CREATE TABLE nursing_visits (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    date DATE NOT NULL,
    time TIME NOT NULL,
    student_id VARCHAR NOT NULL,
    type VARCHAR NOT NULL DEFAULT 'illness',
    occurrence_time VARCHAR,
    treatment_notes TEXT,
    created_at DATETIME,
    updated_at DATETIME,
    category VARCHAR NOT NULL DEFAULT 'internal',
    type_detail VARCHAR
);

-- 4. データをコピー（内部ID → 学籍番号）
INSERT INTO nursing_visits 
SELECT 
    nv.id,
    nv.date,
    nv.time,
    s.student_id,
    nv.type,
    nv.occurrence_time,
    nv.treatment_notes,
    nv.created_at,
    nv.updated_at,
    nv.category,
    nv.type_detail
FROM nursing_visits_backup nv
JOIN students s ON nv.student_id = s.id;

-- 5. バックアップテーブルを削除
DROP TABLE nursing_visits_backup;

COMMIT;

-- 6. 確認
SELECT id, date, student_id FROM nursing_visits LIMIT 10;
