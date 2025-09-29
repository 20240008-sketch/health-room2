# 健康管理システム - 設計書

## システム構成

### アーキテクチャ概要
```
[フロントエンド: Vue.js + Tailwind CSS]
           ↕ HTTP/JSON
[バックエンド: Laravel API]
           ↕ Eloquent ORM
[データベース: SQLite]
```

### ディレクトリ構造
```
laravel-app/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── StudentController.php
│   │   │   ├── ClassController.php
│   │   │   └── HealthRecordController.php
│   │   └── Requests/
│   │       ├── StudentRequest.php
│   │       └── HealthRecordRequest.php
│   ├── Models/
│   │   ├── Student.php
│   │   ├── SchoolClass.php
│   │   └── HealthRecord.php
│   └── Services/
│       └── StudentSearchService.php
├── database/
│   ├── migrations/
│   │   ├── create_school_classes_table.php
│   │   ├── create_students_table.php
│   │   └── create_health_records_table.php
│   ├── seeders/
│   │   └── SchoolClassSeeder.php
│   └── factories/
│       ├── StudentFactory.php
│       └── HealthRecordFactory.php
├── resources/
│   ├── js/
│   │   ├── components/
│   │   │   ├── StudentList.vue
│   │   │   ├── StudentForm.vue
│   │   │   ├── StudentDetail.vue
│   │   │   ├── HealthRecordForm.vue
│   │   │   └── SearchForm.vue
│   │   ├── pages/
│   │   │   ├── Students.vue
│   │   │   ├── StudentShow.vue
│   │   │   └── StudentCreate.vue
│   │   └── app.js
│   └── views/
│       └── app.blade.php
└── routes/
    ├── web.php
    └── api.php
```

## データベース設計

### 1. school_classes テーブル
```sql
CREATE TABLE school_classes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    class_id VARCHAR(10) UNIQUE NOT NULL,
    class_name VARCHAR(50) NOT NULL,
    grade INTEGER NOT NULL,
    kumi INTEGER NOT NULL,
    year INTEGER NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_class_id (class_id),
    INDEX idx_year_grade (year, grade)
);
```

### 2. students テーブル
```sql
CREATE TABLE students (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(100) NOT NULL,
    kana VARCHAR(100) NOT NULL,
    student_id VARCHAR(20) UNIQUE NOT NULL,
    student_number INTEGER NOT NULL,
    sex ENUM('male', 'female') NOT NULL,
    class_id INTEGER NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES school_classes(id) ON DELETE CASCADE,
    INDEX idx_student_id (student_id),
    INDEX idx_class_id (class_id),
    INDEX idx_name (name),
    INDEX idx_kana (kana)
);
```

### 3. health_records テーブル
```sql
CREATE TABLE health_records (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    student_id INTEGER NOT NULL,
    year INTEGER NOT NULL,
    height DECIMAL(5,2) NULL,
    weight DECIMAL(5,2) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    INDEX idx_student_year (student_id, year),
    UNIQUE KEY unique_student_year (student_id, year)
);
```

## モデル設計

### 1. SchoolClass モデル
```php
class SchoolClass extends Model
{
    protected $fillable = [
        'class_id', 'class_name', 'grade', 'kumi', 'year'
    ];
    
    public function students()
    {
        return $this->hasMany(Student::class, 'class_id');
    }
}
```

### 2. Student モデル
```php
class Student extends Model
{
    protected $fillable = [
        'name', 'kana', 'student_id', 'student_number', 
        'sex', 'class_id'
    ];
    
    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }
    
    public function healthRecords()
    {
        return $this->hasMany(HealthRecord::class);
    }
}
```

### 3. HealthRecord モデル
```php
class HealthRecord extends Model
{
    protected $fillable = [
        'student_id', 'year', 'height', 'weight'
    ];
    
    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
```

## API設計

### 生徒関連API
```
GET    /api/students              - 生徒一覧取得（検索・ページネーション対応）
POST   /api/students              - 生徒登録
GET    /api/students/{id}         - 生徒詳細取得
PUT    /api/students/{id}         - 生徒情報更新
DELETE /api/students/{id}         - 生徒削除
```

### クラス関連API
```
GET    /api/classes               - クラス一覧取得
GET    /api/classes/{id}          - クラス詳細取得
```

### 健康記録関連API
```
GET    /api/students/{id}/health-records     - 生徒の健康記録一覧
POST   /api/students/{id}/health-records     - 健康記録登録
PUT    /api/health-records/{id}              - 健康記録更新
DELETE /api/health-records/{id}              - 健康記録削除
```

## フロントエンド設計

### 1. ルーティング設計
```javascript
const routes = [
    { path: '/', component: Students, name: 'students.index' },
    { path: '/students/create', component: StudentCreate, name: 'students.create' },
    { path: '/students/:id', component: StudentShow, name: 'students.show' },
    { path: '/students/:id/edit', component: StudentEdit, name: 'students.edit' },
    { path: '/students/:id/health-records/create', component: HealthRecordCreate, name: 'health-records.create' }
];
```

### 2. コンポーネント設計

#### StudentList.vue
```vue
<template>
  <div class="container mx-auto px-4">
    <!-- 検索フォーム -->
    <SearchForm @search="handleSearch" />
    
    <!-- 生徒一覧テーブル -->
    <div class="overflow-x-auto">
      <table class="min-w-full bg-white shadow-md rounded-lg">
        <!-- テーブルヘッダー・ボディ -->
      </table>
    </div>
    
    <!-- ページネーション -->
    <Pagination :data="students" @page-changed="handlePageChange" />
  </div>
</template>
```

#### StudentForm.vue
```vue
<template>
  <form @submit.prevent="submitForm" class="max-w-lg mx-auto bg-white p-6 rounded-lg shadow-md">
    <!-- フォームフィールド -->
    <div class="mb-4">
      <label class="block text-gray-700 text-sm font-bold mb-2">氏名</label>
      <input v-model="form.name" type="text" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:border-blue-500">
      <span v-if="errors.name" class="text-red-500 text-xs">{{ errors.name[0] }}</span>
    </div>
    <!-- 他のフィールド -->
  </form>
</template>
```

### 3. 状態管理設計（Pinia）
```javascript
export const useStudentStore = defineStore('student', {
  state: () => ({
    students: [],
    currentStudent: null,
    loading: false,
    errors: {}
  }),
  
  actions: {
    async fetchStudents(params = {}) {
      this.loading = true;
      try {
        const response = await api.get('/students', { params });
        this.students = response.data;
      } catch (error) {
        this.errors = error.response.data.errors || {};
      } finally {
        this.loading = false;
      }
    }
  }
});
```

## UI/UX設計

### 1. カラーパレット
```css
:root {
  --primary-color: #3B82F6;     /* Blue-500 */
  --secondary-color: #6B7280;   /* Gray-500 */
  --success-color: #10B981;     /* Green-500 */
  --danger-color: #EF4444;      /* Red-500 */
  --warning-color: #F59E0B;     /* Yellow-500 */
  --background-color: #F9FAFB;  /* Gray-50 */
}
```

### 2. レスポンシブブレイクポイント
- モバイル: ~767px
- タブレット: 768px~1023px  
- デスクトップ: 1024px~

### 3. コンポーネントスタイル指針
- カード形式のデザイン（shadow-md, rounded-lg）
- 一貫したスペーシング（p-4, m-4, gap-4）
- フォーカス状態の明確な表示
- バリデーションエラーの視覚的フィードバック

## バリデーション設計

### バックエンドバリデーション（FormRequest）
```php
class StudentRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string|max:100',
            'kana' => 'required|string|max:100|regex:/^[ぁ-ん]+$/',
            'student_id' => 'required|string|max:20|unique:students',
            'student_number' => 'required|integer|min:1',
            'sex' => 'required|in:male,female',
            'class_id' => 'required|exists:school_classes,id'
        ];
    }
}
```

### フロントエンドバリデーション
```javascript
const validateStudentForm = (form) => {
  const errors = {};
  
  if (!form.name) errors.name = ['氏名は必須です'];
  if (!form.kana) errors.kana = ['ふりがなは必須です'];
  else if (!/^[ぁ-ん]+$/.test(form.kana)) {
    errors.kana = ['ひらがなで入力してください'];
  }
  
  return errors;
};
```

## セキュリティ設計

### 1. データベースセキュリティ
- SQLインジェクション対策：Eloquent ORM使用
- データベースファイルの適切な権限設定

### 2. 入力値検証
- XSS対策：Vue.jsの自動エスケープ機能
- CSRF対策：Laravel標準機能

### 3. エラーハンドリング
- 詳細なエラー情報の本番環境での非表示
- 適切なHTTPステータスコードの返却

## パフォーマンス設計

### 1. データベース最適化
- 適切なインデックス設定
- N+1問題対策（with()によるEager Loading）

### 2. フロントエンド最適化
- コンポーネントの適切な分割
- 不要な再レンダリングの防止
- 画像遅延読み込み（必要に応じて）

### 3. キャッシュ戦略
- LaravelのQueryビルダーキャッシュ
- ブラウザキャッシュの適切な設定

## テスト設計

### 1. バックエンドテスト
- Unit Test：モデル・サービスクラス
- Feature Test：API エンドポイント
- データベースファクトリーの活用

### 2. フロントエンドテスト
- コンポーネント単体テスト（Vue Test Utils）
- 統合テスト（E2E）

## デプロイメント設計

### 1. 環境設定
- `.env`ファイルでの環境変数管理
- SQLiteデータベースファイルの配置

### 2. アセットビルド
- Viteによるフロントエンドアセットのビルド
- 本番環境向け最適化設定

### 3. 運用考慮事項
- ログローテーション
- データベースバックアップ
- システム監視