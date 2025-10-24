-- テーブルを再作成する方法
BEGIN TRANSACTION;

-- 1. バックアップテーブルを作成
CREATE TABLE health_records_backup AS SELECT * FROM health_records;

-- 2. 古いテーブルを削除
DROP TABLE health_records;

-- 3. 新しいテーブルを作成（student_id を VARCHAR 型に変更）
CREATE TABLE health_records (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    student_id VARCHAR NOT NULL,
    year INTEGER NOT NULL,
    height NUMERIC,
    weight NUMERIC,
    created_at DATETIME,
    updated_at DATETIME,
    vision_left NUMERIC,
    vision_right NUMERIC,
    hearing_left VARCHAR,
    hearing_right VARCHAR,
    vision_left_corrected REAL,
    vision_right_corrected REAL,
    measured_date DATE,
    ophthalmology_result TEXT,
    otolaryngology_result TEXT,
    internal_medicine_result TEXT,
    hearing_test_result TEXT,
    tuberculosis_test_result TEXT,
    urine_test_result TEXT,
    ecg_result TEXT
);

-- 4. データをコピー（student_id_new を student_id として）
INSERT INTO health_records 
SELECT 
    id, 
    student_id_new, 
    year, 
    height, 
    weight, 
    created_at, 
    updated_at, 
    vision_left, 
    vision_right, 
    hearing_left, 
    hearing_right, 
    vision_left_corrected, 
    vision_right_corrected, 
    measured_date,
    ophthalmology_result,
    otolaryngology_result,
    internal_medicine_result,
    hearing_test_result,
    tuberculosis_test_result,
    urine_test_result,
    ecg_result
FROM health_records_backup;

-- 5. バックアップテーブルを削除
DROP TABLE health_records_backup;

COMMIT;

-- 6. 確認
SELECT id, student_id, year FROM health_records LIMIT 10;
