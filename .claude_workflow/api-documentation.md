# API Routes Documentation - 健康管理システム

## Base URL
```
http://your-domain.com/api
```

## ヘルスチェック
- `GET /health` - システム稼働状況確認

## API Version 1 エンドポイント

### 🎓 学生管理 API (`/v1/students`)

#### CRUD操作
- `GET /v1/students` - 学生一覧取得（ページネーション対応）
- `POST /v1/students` - 新規学生登録
- `GET /v1/students/{id}` - 特定学生の詳細取得
- `PUT/PATCH /v1/students/{id}` - 学生情報更新
- `DELETE /v1/students/{id}` - 学生削除

#### 検索機能
- `GET /v1/students/search/suggestions?query={検索語}&limit={件数}` - オートコンプリート候補取得
- `GET /v1/students/search/advanced?grade={学年}&class_id={クラスID}&gender={性別}` - 高度な検索
- `GET /v1/students/export/data?{検索パラメータ}` - エクスポート用データ取得

### 🏫 クラス管理 API (`/v1/classes`)
- `GET /v1/classes` - クラス一覧取得
- `POST /v1/classes` - 新規クラス作成
- `GET /v1/classes/{id}` - 特定クラス詳細取得
- `PUT/PATCH /v1/classes/{id}` - クラス情報更新
- `DELETE /v1/classes/{id}` - クラス削除

### 🏥 健康記録管理 API (`/v1/health-records`)
- `GET /v1/health-records` - 健康記録一覧取得
- `POST /v1/health-records` - 新規健康記録登録
- `GET /v1/health-records/{id}` - 特定健康記録詳細取得
- `PUT/PATCH /v1/health-records/{id}` - 健康記録更新
- `DELETE /v1/health-records/{id}` - 健康記録削除

### 📊 統計データ API (`/v1/statistics`)
- `GET /v1/statistics/system` - システム全体統計
- `GET /v1/statistics/grade?grade={学年}` - 学年別統計
- `GET /v1/statistics/class/{classId}` - クラス別統計
- `GET /v1/statistics/health?start_date={開始日}&end_date={終了日}` - 健康記録統計

## 開発用デバッグエンドポイント（開発環境のみ）

### 🔧 デバッグ API (`/debug`)
- `GET /debug/database` - データベース接続確認・データ件数取得
- `GET /debug/routes` - 登録済みAPIルート一覧表示

## API Response Format

### 成功レスポンス
```json
{
    "success": true,
    "data": { /* 実際のデータ */ },
    "message": "操作完了メッセージ"
}
```

### エラーレスポンス
```json
{
    "success": false,
    "message": "エラーメッセージ",
    "error": "詳細なエラー情報（開発環境のみ）"
}
```

## HTTP Status Codes
- `200 OK` - 正常処理完了
- `201 Created` - 新規作成完了
- `400 Bad Request` - リクエストエラー
- `404 Not Found` - リソースが見つからない
- `422 Unprocessable Entity` - バリデーションエラー
- `500 Internal Server Error` - サーバーエラー

## Rate Limiting
- API全体で1分間に60リクエストまで
- 制限に達した場合、HTTPステータス429を返す

## Authentication
現在は認証なしでアクセス可能。
将来的にLaravel Sanctumを使用したトークン認証を実装予定。

## CORS Policy
フロントエンドアプリケーションからのアクセスを許可するため、
必要に応じてCORSミドルウェアの設定を行う。