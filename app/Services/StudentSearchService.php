<?php

namespace App\Services;

use App\Models\Student;
use App\Models\SchoolClass;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class StudentSearchService
{
    /**
     * 高度な生徒検索機能
     * 
     * @param array $searchParams 検索パラメータ
     * @return LengthAwarePaginator
     */
    public function search(array $searchParams): LengthAwarePaginator
    {
        $query = Student::with(['schoolClass', 'healthRecords' => function ($query) {
            $query->orderBy('year', 'desc');
        }]);

        // 基本検索（名前、ふりがな、生徒ID）
        if (!empty($searchParams['search'])) {
            $query = $this->applyBasicSearch($query, $searchParams['search']);
        }

        // 詳細検索フィルタ
        $query = $this->applyDetailedFilters($query, $searchParams);

        // ソート処理
        $query = $this->applySorting($query, $searchParams);

        // ページネーション
        $perPage = $searchParams['per_page'] ?? 15;
        
        return $query->paginate($perPage);
    }

    /**
     * 基本検索（あいまい検索）を適用
     */
    private function applyBasicSearch(Builder $query, string $search): Builder
    {
        $searchTerms = $this->parseSearchTerms($search);

        return $query->where(function ($q) use ($searchTerms) {
            foreach ($searchTerms as $term) {
                $q->orWhere(function ($subQ) use ($term) {
                    $subQ->where('name', 'LIKE', "%{$term}%")
                         ->orWhere('name_kana', 'LIKE', "%{$term}%")
                         ->orWhere('student_id', 'LIKE', "%{$term}%")
                         ->orWhere('student_number', 'LIKE', "%{$term}%");
                });
            }
        });
    }

    /**
     * 詳細フィルタを適用
     */
    private function applyDetailedFilters(Builder $query, array $searchParams): Builder
    {
        // クラスIDフィルタ（文字列値で直接検索）
        if (!empty($searchParams['class_id'])) {
            $query->where('class_id', $searchParams['class_id']);
        }

        // 学年フィルタ（grade_idで直接検索）
        if (!empty($searchParams['grade_id'])) {
            $query->where('grade_id', $searchParams['grade_id']);
        }

        // 年度フィルタ（現在は使用しない）
        if (!empty($searchParams['year'])) {
            // 年度フィルタは現在のデータ構造では使用しない
        }

        // 性別フィルタ
        if (!empty($searchParams['sex'])) {
            $query->where('sex', $searchParams['sex']);
        }

        // 出席番号範囲フィルタ
        if (!empty($searchParams['student_number_min'])) {
            $query->where('student_number', '>=', $searchParams['student_number_min']);
        }
        if (!empty($searchParams['student_number_max'])) {
            $query->where('student_number', '<=', $searchParams['student_number_max']);
        }

        // 健康記録の有無でフィルタ
        if (isset($searchParams['has_health_records'])) {
            if ($searchParams['has_health_records']) {
                $query->has('healthRecords');
            } else {
                $query->doesntHave('healthRecords');
            }
        }

        // 特定年度の健康記録有無でフィルタ
        if (!empty($searchParams['health_record_year'])) {
            $year = $searchParams['health_record_year'];
            if (!empty($searchParams['has_health_record_for_year'])) {
                $query->whereHas('healthRecords', function ($q) use ($year) {
                    $q->where('year', $year);
                });
            } else {
                $query->whereDoesntHave('healthRecords', function ($q) use ($year) {
                    $q->where('year', $year);
                });
            }
        }

        return $query;
    }

    /**
     * ソート処理を適用
     */
    private function applySorting(Builder $query, array $searchParams): Builder
    {
        $sortBy = $searchParams['sort_by'] ?? 'student_number';
        $sortOrder = $searchParams['sort_order'] ?? 'asc';

        switch ($sortBy) {
            case 'class':
                $query->join('school_classes', 'students.class_id', '=', 'school_classes.id')
                      ->orderBy('school_classes.grade', $sortOrder)
                      ->orderBy('school_classes.kumi', $sortOrder)
                      ->orderBy('students.student_number', 'asc')
                      ->select('students.*');
                break;

            case 'name_kana':
                $query->orderBy('kana', $sortOrder);
                break;

            case 'health_records_count':
                $query->withCount('healthRecords')
                      ->orderBy('health_records_count', $sortOrder);
                break;

            default:
                $query->orderBy($sortBy, $sortOrder);
                break;
        }

        return $query;
    }

    /**
     * 検索語を解析（スペース区切りで複数語対応）
     */
    private function parseSearchTerms(string $search): array
    {
        // 全角スペースを半角に変換
        $search = str_replace('　', ' ', $search);
        
        // スペースで分割し、空要素を除去
        $terms = array_filter(explode(' ', trim($search)), function ($term) {
            return !empty($term);
        });

        return $terms;
    }

    /**
     * 検索候補を取得（オートコンプリート用）
     */
    public function getSearchSuggestions(string $query, int $limit = 10): array
    {
        $suggestions = [];

        // 名前の候補
        $nameMatches = Student::where('name', 'LIKE', "%{$query}%")
                             ->limit($limit)
                             ->pluck('name')
                             ->toArray();

        // ふりがなの候補
        $kanaMatches = Student::where('kana', 'LIKE', "%{$query}%")
                             ->limit($limit)
                             ->pluck('kana')
                             ->toArray();

        // 生徒IDの候補
        $studentIdMatches = Student::where('student_id', 'LIKE', "%{$query}%")
                                  ->limit($limit)
                                  ->pluck('student_id')
                                  ->toArray();

        $suggestions = array_merge($nameMatches, $kanaMatches, $studentIdMatches);

        // 重複除去と制限
        return array_slice(array_unique($suggestions), 0, $limit);
    }

    /**
     * 検索統計情報を取得
     */
    public function getSearchStats(array $searchParams): array
    {
        $query = Student::with('schoolClass');

        // 同じフィルタを適用（ページネーション除く）
        if (!empty($searchParams['search'])) {
            $query = $this->applyBasicSearch($query, $searchParams['search']);
        }
        $query = $this->applyDetailedFilters($query, $searchParams);

        $students = $query->get();
        $totalCount = $students->count();

        // 統計データ計算
        $stats = [
            'total_count' => $totalCount,
            'by_grade' => $students->groupBy('schoolClass.grade')->map->count(),
            'by_sex' => $students->groupBy('sex')->map->count(),
            'by_class' => $students->groupBy('schoolClass.class_name')->map->count(),
            'with_health_records' => $students->filter(function ($student) {
                return $student->healthRecords->count() > 0;
            })->count(),
            'without_health_records' => $students->filter(function ($student) {
                return $student->healthRecords->count() === 0;
            })->count(),
        ];

        return $stats;
    }

    /**
     * 高度な検索（複合条件）
     */
    public function advancedSearch(array $conditions): LengthAwarePaginator
    {
        $query = Student::with(['schoolClass', 'healthRecords']);

        // AND条件
        if (!empty($conditions['and_conditions'])) {
            foreach ($conditions['and_conditions'] as $condition) {
                $query = $this->applyCondition($query, $condition, 'and');
            }
        }

        // OR条件
        if (!empty($conditions['or_conditions'])) {
            $query->where(function ($q) use ($conditions) {
                foreach ($conditions['or_conditions'] as $condition) {
                    $q = $this->applyCondition($q, $condition, 'or');
                }
            });
        }

        $perPage = $conditions['per_page'] ?? 15;
        return $query->paginate($perPage);
    }

    /**
     * 個別条件を適用
     */
    private function applyCondition(Builder $query, array $condition, string $type): Builder
    {
        $field = $condition['field'] ?? '';
        $operator = $condition['operator'] ?? '=';
        $value = $condition['value'] ?? '';

        $method = $type === 'or' ? 'orWhere' : 'where';

        switch ($field) {
            case 'name':
            case 'kana':
            case 'student_id':
            case 'sex':
                $query->{$method}($field, $operator, $value);
                break;

            case 'student_number':
                $query->{$method}('student_number', $operator, (int)$value);
                break;

            case 'class_grade':
                $query->whereHas('schoolClass', function ($q) use ($method, $operator, $value) {
                    $q->{$method}('grade', $operator, (int)$value);
                });
                break;

            case 'class_year':
                $query->whereHas('schoolClass', function ($q) use ($method, $operator, $value) {
                    $q->{$method}('year', $operator, (int)$value);
                });
                break;
        }

        return $query;
    }

    /**
     * エクスポート用データ取得
     */
    public function getExportData(array $searchParams): array
    {
        $query = Student::with(['schoolClass', 'healthRecords']);

        // 検索条件適用
        if (!empty($searchParams['search'])) {
            $query = $this->applyBasicSearch($query, $searchParams['search']);
        }
        $query = $this->applyDetailedFilters($query, $searchParams);
        $query = $this->applySorting($query, $searchParams);

        return $query->get()->map(function ($student) {
            return [
                '生徒ID' => $student->student_id,
                '氏名' => $student->name,
                'ふりがな' => $student->kana,
                '出席番号' => $student->student_number,
                '性別' => $student->sex === 'male' ? '男' : '女',
                'クラス' => $student->schoolClass->class_name ?? '',
                '学年' => $student->schoolClass->grade ?? '',
                '年度' => $student->schoolClass->year ?? '',
                '健康記録数' => $student->healthRecords->count(),
                '最新健康記録年度' => $student->healthRecords->max('year') ?? '',
            ];
        })->toArray();
    }
}