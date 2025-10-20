# 出席入力・出席登録画面の改善

## 📋 実装内容

### ✨ 主な変更点

#### 1. **日付入力をスクロール型に変更**
- **変更前**: カレンダー型（`<input type="date">`）
- **変更後**: 年・月・日のドロップダウンセレクト

**メリット:**
- 📱 スマホでの入力が簡単
- 🖱️ マウス操作で素早く選択
- 👁️ 視認性が向上

**実装:**
```vue
<select v-model="dateComponents.year">
  <option>2020年</option>
  <option>2021年</option>
  ...
</select>
<select v-model="dateComponents.month">
  <option>1月</option>
  <option>2月</option>
  ...
</select>
<select v-model="dateComponents.day">
  <option>1日</option>
  <option>2日</option>
  ...
</select>
```

#### 2. **学年・クラス選択を任意に変更**
- **変更前**: 学年・クラスが必須（required）
- **変更後**: すべて任意選択

**検索パターン:**
1. **クラス単位**: 学年 + クラス選択
2. **学年単位**: 学年のみ選択
3. **学生名検索**: 検索フィールドのみ使用
4. **組み合わせ**: 学年 + 検索、クラス + 検索など

#### 3. **柔軟な学生検索**
- クラス未選択でも学生名検索が可能
- 検索フィールドは常時有効（無効化なし）
- 2文字以上入力で自動検索開始

### 🎯 使用例

#### パターン1: クラス全体の出席入力
```
日付: 2025年10月19日
学年: 3年生
クラス: 3年1組
→ クラス全員が表示される
```

#### パターン2: 学年全体から検索
```
日付: 2025年10月19日
学年: 3年生
クラス: (空)
学生名検索: 田中
→ 3年生の「田中」さんが表示される
```

#### パターン3: 全学年から検索
```
日付: 2025年10月19日
学年: (空)
クラス: (空)  
学生名検索: 佐藤
→ 全学年の「佐藤」さんが表示される
```

#### パターン4: 特定学生の出席入力
```
日付: 2025年10月19日
学年: (空)
クラス: (空)
学生名検索: 山田太郎
→ 「山田太郎」さんのみ表示される
```

## 📱 UI改善

### 日付選択のレイアウト
```
┌─────────┬─────────┬─────────┐
│ 2025年  │  10月   │  19日   │
└─────────┴─────────┴─────────┘
```

### 基本情報セクションのレイアウト（4列）
```
┌──────────┬──────────┬──────────┬──────────┐
│   日付   │   学年   │  クラス  │学生名検索│
│ (必須)   │ (任意)   │ (任意)   │ (任意)   │
└──────────┴──────────┴──────────┴──────────┘
```

## 🔧 技術的な実装

### 日付コンポーネントの管理
```javascript
const dateComponents = ref({
  year: 2025,
  month: 10,
  day: 19
});

// 日数の自動計算（うるう年対応）
const daysInMonth = computed(() => {
  return new Date(year, month, 0).getDate();
});
```

### 柔軟な学生読み込み
```javascript
const loadStudents = async () => {
  const params = {};
  if (form.value.grade) params.grade = form.value.grade;
  if (form.value.class_id) params.class_id = form.value.class_id;
  if (searchQuery.value) params.search = searchQuery.value;
  
  await studentStore.fetchStudents(params);
};
```

### バックエンド対応
```php
// StudentController.php
if ($request->filled('grade')) {
    $query->where('grade_id', $request->grade);
}
if ($request->filled('class_id')) {
    $query->where('class_id', $request->class_id);
}
if ($request->filled('search')) {
    $query->where('name', 'LIKE', "%{$request->search}%");
}
```

## 📊 対応画面

✅ **出席入力画面** (`AttendanceRegistrationCreate.vue`)
- 日付: スクロール型
- 学年: 任意選択
- クラス: 任意選択
- 学生名検索: 常時有効

✅ **出席登録一覧画面** (`AttendanceRegistrationIndex.vue`)
- 日付: スクロール型
- フィルタ: すべて任意

## 🎨 ユーザー体験

### 改善前
```
❌ カレンダーを開く手間
❌ 学年・クラス必須で柔軟性がない
❌ クラス未選択時は検索できない
```

### 改善後
```
✅ ドロップダウンで素早く日付選択
✅ 必要な情報だけ入力すればOK
✅ 学生名だけでも検索可能
✅ 様々な検索パターンに対応
```

## 🚀 次のステップ

今後の拡張性:
- 日付範囲選択（開始日〜終了日）
- お気に入りの検索条件を保存
- クイック日付選択（今日、昨日、明日など）
- 複数学生の一括選択
