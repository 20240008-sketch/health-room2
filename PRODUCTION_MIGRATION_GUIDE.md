# 本番環境マイグレーションガイド

## 概要
本番環境でデータベースマイグレーションを安全に実行するためのガイドです。

## 追加されるフィールド

### health_recordsテーブル
- `vision_left` - 左目視力 (A, B, C, D)
- `vision_right` - 右目視力 (A, B, C, D)
- `vision_left_corrected` - 左目矯正視力 (A, B, C, D)
- `vision_right_corrected` - 右目矯正視力 (A, B, C, D)
- `hearing_left` - 左耳聴力
- `hearing_right` - 右耳聴力
- `measured_date` - 測定日
- `ophthalmology_result` - 眼科検診備考
- `otolaryngology_result` - 耳鼻科検診
- `internal_medicine_result` - 内科検診
- `hearing_test_result` - 聴力検査
- `tuberculosis_test_result` - 結核検査
- `urine_test_result` - 尿検査
- `ecg_result` - 心電図
- `ophthalmology_exam_result` - 眼科検診結果
- `ophthalmology_diagnosis` - 眼科診断結果
- `ophthalmology_treatment` - 眼科処置
- `student_id` - VARCHAR型に変更（学籍番号用）

### nursing_visitsテーブル
- `type` - 状態 (illness, injury, consultation, other)
- `absence_reason` - 欠席理由
- `subject` - 教科
- `club` - 部活
- `event` - 行事
- `breakfast` - 朝食
- `bowel_movement` - 便通
- `treatment` - 処置
- `injury_location` - けがの部位
- `injury_place` - けがをした場所
- `surgical_treatment` - 外科的処置
- `student_id` - VARCHAR型に変更（学籍番号用）

## 本番環境での実行手順

### 1. バックアップの作成
**必ず実行前にデータベースのバックアップを取ってください！**

```bash
# データベースファイルのバックアップ
cp database/database.sqlite database/database.sqlite.backup.$(date +%Y%m%d_%H%M%S)
```

### 2. マイグレーションファイルの確認
```bash
# 未実行のマイグレーションを確認
php artisan migrate:status
```

以下の2つのマイグレーションがPending状態であることを確認してください：
- `2025_10_28_072116_add_all_health_record_fields_to_health_records_table`
- `2025_10_28_072253_add_type_field_to_nursing_visits_table`

### 3. マイグレーションの実行

#### オプション1: 通常の実行
```bash
php artisan migrate
```

#### オプション2: ドライラン（実際には実行せず、何が起こるか確認）
```bash
php artisan migrate --pretend
```

#### オプション3: 本番環境で強制実行（本番環境で実行する場合）
```bash
php artisan migrate --force
```

### 4. 実行後の確認
```bash
# マイグレーションステータスの確認
php artisan migrate:status

# テーブル構造の確認
php artisan db:show
```

## トラブルシューティング

### エラーが発生した場合

1. **カラムがすでに存在するエラー**
   - マイグレーションには`hasColumn`チェックが含まれているため、通常は発生しません
   - 既にカラムが存在する場合はスキップされます

2. **データ型変更エラー（student_id）**
   - 既存のデータがある場合、データの移行が必要です
   - 以下のSQLを手動で実行してください：

```sql
-- health_recordsテーブルのstudent_idを確認
SELECT DISTINCT student_id FROM health_records LIMIT 10;

-- nursing_visitsテーブルのstudent_idを確認
SELECT DISTINCT student_id FROM nursing_visits LIMIT 10;
```

3. **ロールバックが必要な場合**
```bash
# 最後のバッチをロールバック
php artisan migrate:rollback

# または特定のステップ数をロールバック
php artisan migrate:rollback --step=2
```

## マイグレーションの特徴

### 安全性
- すべてのカラム追加前に存在チェックを実施
- 既存のカラムがある場合はスキップ
- データの損失を防ぐ設計

### 互換性
- SQLite、MySQL、PostgreSQL対応
- 既存データへの影響なし
- nullableフィールドで安全に追加

## データ移行について

### student_idの形式
開発環境では`student_id`が20230001のような学籍番号形式になっています。
本番環境で数値IDを使用している場合は、以下の手順でデータを移行してください：

1. studentsテーブルから学籍番号を確認
2. health_recordsとnursing_visitsのstudent_idを学籍番号に更新

詳細は`migrate_student_id.sql`ファイルを参照してください。

## 検証方法

マイグレーション後、以下を確認してください：

1. **健康記録の作成**
   - `/health-records/create`にアクセス
   - 眼科検診フィールドが表示されることを確認
   - 個別モード・一括モード両方で正常に動作することを確認

2. **保健室記録の作成**
   - `/attendance-registrations/create`にアクセス
   - 「状態」フィールド（illness, injury, consultation, other）が表示されることを確認

3. **データの保存**
   - 各フォームでデータを入力して保存
   - エラーなく保存されることを確認
   - 保存したデータが正しく表示されることを確認

## 注意事項

1. **本番環境での実行は慎重に**
   - 必ずバックアップを取得してください
   - メンテナンスモードを有効にすることを推奨します：
     ```bash
     php artisan down
     # マイグレーション実行
     php artisan migrate --force
     php artisan up
     ```

2. **学籍番号への移行**
   - student_idを内部IDから学籍番号に変更している場合
   - 既存データの移行スクリプトを実行してください

3. **テスト環境での事前確認**
   - 可能であれば、本番環境と同じデータのコピーでテストしてください

## サポート

問題が発生した場合は、以下の情報を収集してください：
- エラーメッセージの全文
- `php artisan migrate:status`の出力
- データベースのスキーマ情報
- Laravelのログファイル（`storage/logs/laravel.log`）
