# 本番環境マイグレーション - クイックスタートガイド

## 重要: 実行前の確認事項

✅ **必ずバックアップを取得してください！**

## 本番環境での実行手順（簡易版）

### 1. バックアップ作成
```bash
# データベースのバックアップ
cp database/database.sqlite database/database.sqlite.backup.$(date +%Y%m%d_%H%M%S)
```

### 2. マイグレーション実行
```bash
# 本番環境で強制実行（確認プロンプトをスキップ）
php artisan migrate --force
```

**注意**: `--force`オプションは本番環境（APP_ENV=production）で確認プロンプトなしで実行するために必要です。

### 3. 実行結果の確認
以下のような出力が表示されれば成功です：
```
INFO  Running migrations.

2025_10_28_072116_add_all_health_record_fields_to_health_records_table .... DONE
2025_10_28_072253_add_type_field_to_nursing_visits_table ................. DONE
```

### 4. 動作確認
```bash
# マイグレーションステータスの確認
php artisan migrate:status
```

最後の2つのマイグレーションが「Ran」と表示されていればOKです：
- `2025_10_28_072116_add_all_health_record_fields_to_health_records_table`
- `2025_10_28_072253_add_type_field_to_nursing_visits_table`

## 画面での確認

### 健康記録作成画面
http://あなたのドメイン/health-records/create

✅ 確認項目：
- 眼科検診のチェックボックスが表示される
- 個別モードで「検診結果」「診断結果」「処置」の3つの選択フィールドが表示される
- 一括モードで学生ごとにカードが表示され、3つの選択フィールドが表示される

### 保健室記録作成画面
http://あなたのドメイン/attendance-registrations/create

✅ 確認項目：
- 「状態」フィールドが表示される（illness, injury, consultation, other）

## トラブルシューティング

### Q: "Nothing to migrate" と表示される
A: すでにマイグレーションが実行済みです。問題ありません。

### Q: エラーが発生した
A: 以下を確認してください：
1. データベースファイルの書き込み権限
2. エラーメッセージの内容（`storage/logs/laravel.log`を確認）
3. バックアップから復元する場合：
   ```bash
   cp database/database.sqlite.backup.YYYYMMDD_HHMMSS database/database.sqlite
   ```

### Q: 確認プロンプトが表示されて止まる
A: `--force`オプションを必ず付けてください：
   ```bash
   php artisan migrate --force
   ```

## メンテナンスモードの使用（推奨）

ユーザーがアクセス中に実行する場合は、メンテナンスモードを有効にすることを推奨します：

```bash
# メンテナンスモードを有効化
php artisan down

# マイグレーション実行
php artisan migrate --force

# メンテナンスモードを解除
php artisan up
```

## 追加される機能

### health_recordsテーブル
- 視力4フィールド（A/B/C/D形式）
- 聴力2フィールド
- 7つの医療検診フィールド
- 眼科検診詳細3フィールド（検診結果、診断結果、処置）

### nursing_visitsテーブル
- 状態フィールド（illness, injury, consultation, other）
- その他10個の詳細フィールド

詳細は`PRODUCTION_MIGRATION_GUIDE.md`を参照してください。

## サポート

問題が発生した場合は、以下の情報を収集してください：
- `php artisan migrate:status`の出力
- `storage/logs/laravel.log`の最新のエラー
- 実行したコマンドとエラーメッセージ
