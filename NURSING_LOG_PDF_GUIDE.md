# 養護日誌PDF出力機能 実装ガイド

## 📋 実装概要

養護日誌（http://127.0.0.1:8000/attendance）にPDF出力機能を追加しました。

## 🔧 実装内容

### 1. バックエンド（Laravel + TCPDF）

#### インストールしたライブラリ
```bash
composer require tecnickcom/tcpdf
```

#### 作成したファイル
- **コントローラー**: `app/Http/Controllers/Api/V1/NursingLogController.php`
  - TCPDFを使用してPDFを生成
  - A4横向きで日本語フォント対応
  - 養護日誌の全項目をPDF化

#### APIエンドポイント
```
POST /api/v1/nursing-log/generate-pdf
```

**リクエストボディ例:**
```json
{
  "date": "2025-10-19",
  "year": "7",
  "month": "10",
  "day": "19",
  "weather": "晴れ",
  "temperature": "22",
  "humidity": "60",
  "absence": {
    "absent": {"grade1": 0, "grade2": 0, ...},
    "sick": {"grade1": 1, "grade2": 0, ...},
    ...
  },
  "visits": [
    {
      "time": "10:30",
      "grade": "3",
      "class": "1",
      "number": "15",
      "name": "山田太郎",
      "gender": "男",
      "type": "けが",
      "occurrence": "休み時間",
      "treatment": "消毒、絆創膏"
    }
  ],
  "schoolEvents": "避難訓練",
  "waterQuality": "良好",
  "chlorine": "0.5",
  "notes": "特になし"
}
```

**レスポンス:**
- Content-Type: `application/pdf`
- PDFファイルがダウンロードされます

### 2. フロントエンド（Vue.js）

#### 追加したボタン
`AttendanceIndex.vue`に「PDF出力」ボタンを追加

```vue
<BaseButton
  variant="secondary"
  @click="downloadPdf"
>
  <DocumentArrowDownIcon class="h-4 w-4 mr-2" />
  PDF出力
</BaseButton>
```

#### 実装した機能
- `downloadPdf()` メソッド
  - 養護日誌の全データをAPIに送信
  - PDFファイルを自動ダウンロード
  - 成功/エラー通知を表示

### 3. PDF内容

PDFには以下の情報が含まれます：

#### ヘッダー情報
- 日付（令和年月日、曜日）
- 天候
- 温度・湿度
- 各種印欄（校長、副校長、教頭、記入者）

#### 欠席調査表
- 学年別（1年〜6年）
- 種別：欠席、病欠、事故欠、出停、忌引
- 各種別の合計

#### 学校行事・保健行事欄

#### 水質検査・残留塩素欄

#### 保健室来室記録表（最大15件）
- 時間、学年、組、番号、氏名、性別
- 種別、発生時、処置・原因・備考

#### 執務日記及び特記事項欄

## 📝 使用方法

### 1. 養護日誌ページにアクセス
```
http://127.0.0.1:8000/attendance
```

### 2. 日誌データを入力
- 日付を選択
- 天候、温度、湿度を入力
- 欠席調査データを入力
- 来室記録を追加
- その他の項目を記入

### 3. PDF出力
- 「PDF出力」ボタンをクリック
- PDFファイルが自動的にダウンロードされます
- ファイル名: `養護日誌_YYYY-MM-DD.pdf`

## 🎨 PDFデザイン

- **用紙**: A4横向き
- **フォント**: kozgopromedium（日本語対応）
- **フォントサイズ**: 10pt（本文）、9pt（表）、8pt（来室記録）
- **レイアウト**: 表形式で見やすく整理
- **マージン**: 各辺10mm

## ⚙️ 技術仕様

### TCPDF設定
```php
$pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetFont('kozgopromedium', '', 10);
$pdf->SetMargins(10, 10, 10);
```

### HTMLテーブル生成
- CSSスタイルでテーブルをフォーマット
- `writeHTML()`メソッドでHTMLをPDFに変換

## 🔍 トラブルシューティング

### PDFが生成されない場合
1. TCPDFがインストールされているか確認
   ```bash
   composer show tecnickcom/tcpdf
   ```

2. ルートが登録されているか確認
   ```bash
   php artisan route:list | grep nursing
   ```

3. エラーログを確認
   ```bash
   tail -f storage/logs/laravel.log
   ```

### 日本語が表示されない場合
- TCPDFの日本語フォント（kozgopromedium）が使用されているか確認
- フォント設定: `$pdf->SetFont('kozgopromedium', '', 10);`

### PDFレイアウトが崩れる場合
- HTMLテーブル構造を確認
- CSSスタイルを調整
- `NursingLogController.php`の`generateHtml()`メソッドを修正

## 📦 関連ファイル

### バックエンド
- `app/Http/Controllers/Api/V1/NursingLogController.php`
- `routes/api.php`

### フロントエンド
- `resources/js/views/attendance/AttendanceIndex.vue`

## 🚀 今後の拡張案

1. **PDFテンプレートのカスタマイズ**
   - 学校ロゴの追加
   - ヘッダー/フッターのカスタマイズ

2. **保存機能の追加**
   - 養護日誌データをデータベースに保存
   - 過去の日誌を検索・再出力

3. **複数日のPDF出力**
   - 月次まとめPDF
   - 期間指定での一括出力

4. **電子印鑑対応**
   - デジタル署名機能
   - QRコードでの真正性確認

## ✅ 完了事項

- ✅ TCPDFライブラリのインストール
- ✅ NursingLogControllerの作成
- ✅ APIルートの登録
- ✅ フロントエンドにPDF出力ボタン追加
- ✅ PDF生成機能の実装
- ✅ 日本語フォント対応
- ✅ エラーハンドリング
- ✅ ビルド完了

---

**実装完了日**: 2025年10月19日  
**使用技術**: Laravel, TCPDF, Vue.js, Tailwind CSS
