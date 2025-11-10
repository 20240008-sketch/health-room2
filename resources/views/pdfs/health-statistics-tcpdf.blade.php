<style>
    body {
        font-family: 'seieiIPexMincho', serif;
    }
    h1 {
        font-size: 18pt;
        color: #1e40af;
        text-align: center;
        margin-bottom: 10px;
    }
    h2 {
        font-size: 14pt;
        color: #1e40af;
        margin-top: 15px;
        margin-bottom: 10px;
        border-bottom: 2px solid #e5e7eb;
        padding-bottom: 5px;
    }
    h3 {
        font-size: 12pt;
        color: #4b5563;
        margin-top: 10px;
        margin-bottom: 8px;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 15px;
    }
    th {
        background-color: #2563eb;
        color: #ffffff;
        padding: 8px;
        text-align: center;
        font-weight: bold;
    }
    td {
        padding: 6px;
        border: 1px solid #e5e7eb;
        text-align: center;
    }
    tr:nth-child(even) {
        background-color: #f9fafb;
    }
    .info-box {
        background-color: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 5px;
        padding: 10px;
        margin-bottom: 15px;
    }
    .stat-item {
        margin-bottom: 5px;
    }
    .stat-label {
        font-weight: bold;
        color: #4b5563;
    }
    .stat-value {
        color: #1f2937;
    }
</style>

<h1>健康記録統計レポート</h1>

<div class="info-box">
    <div class="stat-item">
        <span class="stat-label">対象年度:</span>
        <span class="stat-value">{{ $statistics['academic_year'] }}年度</span>
    </div>
    <div class="stat-item">
        <span class="stat-label">記録総数:</span>
        <span class="stat-value">{{ $statistics['total_records'] }}件</span>
    </div>
    @if(isset($statistics['grade']) && $statistics['grade'])
    <div class="stat-item">
        <span class="stat-label">対象学年:</span>
        <span class="stat-value">{{ $statistics['grade'] }}年生</span>
    </div>
    @endif
    @if(isset($statistics['class_id']) && $statistics['class_id'])
    <div class="stat-item">
        <span class="stat-label">対象クラス:</span>
        <span class="stat-value">{{ $statistics['class_id'] }}</span>
    </div>
    @endif
    <div class="stat-item">
        <span class="stat-label">発行日:</span>
        <span class="stat-value">{{ date('Y年m月d日') }}</span>
    </div>
</div>

<h2>全体平均</h2>
<table>
    <tr>
        <th>身長(cm)</th>
        <th>体重(kg)</th>
        <th>BMI</th>
        <th>視力(左)</th>
        <th>視力(右)</th>
    </tr>
    <tr>
        <td>{{ $statistics['averages']['height'] ?? '-' }}</td>
        <td>{{ $statistics['averages']['weight'] ?? '-' }}</td>
        <td>{{ $statistics['averages']['bmi'] ?? '-' }}</td>
        <td>{{ $statistics['averages']['vision_left'] ?? '-' }}</td>
        <td>{{ $statistics['averages']['vision_right'] ?? '-' }}</td>
    </tr>
</table>

<h2>範囲</h2>
<table>
    <tr>
        <th>項目</th>
        <th>最小値</th>
        <th>最大値</th>
    </tr>
    <tr>
        <td>身長(cm)</td>
        <td>{{ $statistics['ranges']['height']['min'] ?? '-' }}</td>
        <td>{{ $statistics['ranges']['height']['max'] ?? '-' }}</td>
    </tr>
    <tr>
        <td>体重(kg)</td>
        <td>{{ $statistics['ranges']['weight']['min'] ?? '-' }}</td>
        <td>{{ $statistics['ranges']['weight']['max'] ?? '-' }}</td>
    </tr>
    <tr>
        <td>BMI</td>
        <td>{{ $statistics['ranges']['bmi']['min'] ?? '-' }}</td>
        <td>{{ $statistics['ranges']['bmi']['max'] ?? '-' }}</td>
    </tr>
</table>

<h2>BMI分布</h2>
<table>
    <tr>
        <th>やせ(&lt;18.5)</th>
        <th>標準(18.5-24.9)</th>
        <th>肥満1度(25-29.9)</th>
        <th>肥満2度以上(≥30)</th>
    </tr>
    <tr>
        <td>{{ $statistics['bmi_distribution']['underweight'] }}人 ({{ $statistics['bmi_percentages']['underweight'] }}%)</td>
        <td>{{ $statistics['bmi_distribution']['normal'] }}人 ({{ $statistics['bmi_percentages']['normal'] }}%)</td>
        <td>{{ $statistics['bmi_distribution']['overweight'] }}人 ({{ $statistics['bmi_percentages']['overweight'] }}%)</td>
        <td>{{ $statistics['bmi_distribution']['obese'] }}人 ({{ $statistics['bmi_percentages']['obese'] }}%)</td>
    </tr>
</table>

@if(count($statistics['by_grade']) > 0)
<h2>学年平均</h2>
<table>
    <tr>
        <th>学年</th>
        <th>人数</th>
        <th>身長(cm)</th>
        <th>体重(kg)</th>
        <th>BMI</th>
        <th>視力(左)</th>
        <th>視力(右)</th>
    </tr>
    @foreach($statistics['by_grade'] as $gradeData)
    <tr>
        <td>{{ $gradeData['grade'] }}年生</td>
        <td>{{ $gradeData['count'] }}名</td>
        <td>{{ $gradeData['avg_height'] ?? '-' }}</td>
        <td>{{ $gradeData['avg_weight'] ?? '-' }}</td>
        <td>{{ $gradeData['avg_bmi'] ?? '-' }}</td>
        <td>{{ $gradeData['avg_vision_left'] ?? '-' }}</td>
        <td>{{ $gradeData['avg_vision_right'] ?? '-' }}</td>
    </tr>
    @endforeach
</table>
@endif

@if(count($statistics['by_class']) > 0)
<h2>クラス平均</h2>
<table>
    <tr>
        <th>クラス</th>
        <th>人数</th>
        <th>身長(cm)</th>
        <th>体重(kg)</th>
        <th>BMI</th>
        <th>視力(左)</th>
        <th>視力(右)</th>
    </tr>
    @foreach($statistics['by_class'] as $classData)
    <tr>
        <td>{{ $classData['class_name'] }}</td>
        <td>{{ $classData['count'] }}名</td>
        <td>{{ $classData['avg_height'] ?? '-' }}</td>
        <td>{{ $classData['avg_weight'] ?? '-' }}</td>
        <td>{{ $classData['avg_bmi'] ?? '-' }}</td>
        <td>{{ $classData['avg_vision_left'] ?? '-' }}</td>
        <td>{{ $classData['avg_vision_right'] ?? '-' }}</td>
    </tr>
    @endforeach
</table>
@endif
