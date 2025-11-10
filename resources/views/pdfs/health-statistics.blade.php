<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>健康記録統計レポート</title>
    @php
        $seieiFontPath = null;
        foreach ([
            'seieiIPexMincho.ttf',
            'seieiIPAexMincho.ttf',
        ] as $candidate) {
            $path = public_path($candidate);
            if (file_exists($path)) {
                $seieiFontPath = $path;
                break;
            }
        }
    @endphp
    <style>
        @if($seieiFontPath)
        @font-face {
            font-family: 'seieiIPexMincho';
            src: url('{{ $seieiFontPath }}') format('truetype');
            font-weight: normal;
            font-style: normal;
        }
        @endif
        @page {
            margin: 20mm 15mm;
        }
        
        body {
            font-family: 'seieiIPexMincho', serif;
            font-size: 10pt;
            line-height: 1.6;
            color: #333;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 15px;
        }
        
        .header h1 {
            font-size: 20pt;
            margin: 0 0 10px 0;
            color: #1e40af;
        }
        
        .header .subtitle {
            font-size: 12pt;
            color: #666;
            margin: 5px 0;
        }
        
        .info-section {
            background-color: #f3f4f6;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            border-left: 4px solid #2563eb;
        }
        
        .info-section h2 {
            font-size: 14pt;
            margin: 0 0 10px 0;
            color: #1e40af;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }
        
        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
        }
        
        .info-label {
            font-weight: bold;
            color: #4b5563;
        }
        
        .info-value {
            color: #1f2937;
        }
        
        .section {
            margin-bottom: 25px;
        }
        
        .section-title {
            font-size: 14pt;
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #e5e7eb;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 9pt;
        }
        
        th {
            background-color: #2563eb;
            color: white;
            padding: 10px 8px;
            text-align: center;
            font-weight: bold;
        }
        
        td {
            padding: 8px;
            border-bottom: 1px solid #e5e7eb;
            text-align: center;
        }
        
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
        
        .stat-box {
            display: inline-block;
            background-color: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            padding: 15px;
            margin: 5px;
            width: 23%;
            text-align: center;
            vertical-align: top;
        }
        
        .stat-box-title {
            font-size: 9pt;
            color: #1e40af;
            margin-bottom: 8px;
            font-weight: bold;
        }
        
        .stat-box-value {
            font-size: 18pt;
            color: #1e3a8a;
            font-weight: bold;
        }
        
        .stat-box-unit {
            font-size: 9pt;
            color: #6b7280;
            margin-top: 3px;
        }
        
        .bmi-distribution {
            margin: 20px 0;
        }
        
        .bmi-bar {
            display: flex;
            height: 40px;
            margin: 15px 0;
            border-radius: 5px;
            overflow: hidden;
        }
        
        .bmi-segment {
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 9pt;
        }
        
        .bmi-underweight {
            background-color: #60a5fa;
        }
        
        .bmi-normal {
            background-color: #34d399;
        }
        
        .bmi-overweight {
            background-color: #fbbf24;
        }
        
        .bmi-obese {
            background-color: #f87171;
        }
        
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8pt;
            color: #9ca3af;
            padding: 10px 0;
            border-top: 1px solid #e5e7eb;
        }
        
        .page-number:after {
            content: counter(page);
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .mt-1 {
            margin-top: 5px;
        }
        
        .mt-2 {
            margin-top: 10px;
        }
        
        .mb-2 {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>健康記録統計レポート</h1>
        <div class="subtitle">{{ $statistics['academic_year'] }}年度
            @if(isset($statistics['grade']) && $statistics['grade'])
                {{ $statistics['grade'] }}年生
            @endif
            @if(isset($statistics['class_id']) && $statistics['class_id'])
                {{ $statistics['class_id'] }}
            @endif
        </div>
        <div class="subtitle">発行日: {{ date('Y年m月d日') }}</div>
    </div>

    <!-- Summary Section -->
    <div class="info-section">
        <h2>サマリー</h2>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">対象年度:</span>
                <span class="info-value">{{ $statistics['academic_year'] }}年度</span>
            </div>
            <div class="info-item">
                <span class="info-label">記録総数:</span>
                <span class="info-value">{{ $statistics['total_records'] }}件</span>
            </div>
            @if(isset($statistics['grade']) && $statistics['grade'])
            <div class="info-item">
                <span class="info-label">対象学年:</span>
                <span class="info-value">{{ $statistics['grade'] }}年生</span>
            </div>
            @endif
            <div class="info-item">
                <span class="info-label">生成日時:</span>
                <span class="info-value">{{ date('Y-m-d H:i') }}</span>
            </div>
        </div>
    </div>

    <!-- Overall Averages -->
    <div class="section">
        <div class="section-title">全体平均値</div>
        <div style="text-align: center;">
            <div class="stat-box">
                <div class="stat-box-title">身長</div>
                <div class="stat-box-value">{{ number_format($statistics['averages']['height'], 1) }}</div>
                <div class="stat-box-unit">cm</div>
            </div>
            <div class="stat-box">
                <div class="stat-box-title">体重</div>
                <div class="stat-box-value">{{ number_format($statistics['averages']['weight'], 1) }}</div>
                <div class="stat-box-unit">kg</div>
            </div>
            <div class="stat-box">
                <div class="stat-box-title">BMI</div>
                <div class="stat-box-value">{{ number_format($statistics['averages']['bmi'], 1) }}</div>
                <div class="stat-box-unit"></div>
            </div>
            <div class="stat-box">
                <div class="stat-box-title">視力(平均)</div>
                <div class="stat-box-value" style="font-size: 14pt;">
                    L: {{ number_format($statistics['averages']['vision_left'], 2) }}<br>
                    R: {{ number_format($statistics['averages']['vision_right'], 2) }}
                </div>
                <div class="stat-box-unit"></div>
            </div>
        </div>
    </div>

    <!-- BMI Distribution -->
    <div class="section">
        <div class="section-title">BMI分布</div>
        <div class="bmi-distribution">
            <div class="bmi-bar">
                @if($statistics['bmi_distribution']['underweight'] > 0)
                <div class="bmi-segment bmi-underweight" style="width: {{ $statistics['bmi_percentages']['underweight'] }}%">
                    やせ<br>{{ $statistics['bmi_percentages']['underweight'] }}%
                </div>
                @endif
                @if($statistics['bmi_distribution']['normal'] > 0)
                <div class="bmi-segment bmi-normal" style="width: {{ $statistics['bmi_percentages']['normal'] }}%">
                    普通<br>{{ $statistics['bmi_percentages']['normal'] }}%
                </div>
                @endif
                @if($statistics['bmi_distribution']['overweight'] > 0)
                <div class="bmi-segment bmi-overweight" style="width: {{ $statistics['bmi_percentages']['overweight'] }}%">
                    やや肥満<br>{{ $statistics['bmi_percentages']['overweight'] }}%
                </div>
                @endif
                @if($statistics['bmi_distribution']['obese'] > 0)
                <div class="bmi-segment bmi-obese" style="width: {{ $statistics['bmi_percentages']['obese'] }}%">
                    肥満<br>{{ $statistics['bmi_percentages']['obese'] }}%
                </div>
                @endif
            </div>
            <table>
                <tr>
                    <th>分類</th>
                    <th>人数</th>
                    <th>割合</th>
                </tr>
                <tr>
                    <td>やせ (BMI < 18.5)</td>
                    <td>{{ $statistics['bmi_distribution']['underweight'] }}人</td>
                    <td>{{ $statistics['bmi_percentages']['underweight'] }}%</td>
                </tr>
                <tr>
                    <td>普通 (18.5 ≤ BMI < 25)</td>
                    <td>{{ $statistics['bmi_distribution']['normal'] }}人</td>
                    <td>{{ $statistics['bmi_percentages']['normal'] }}%</td>
                </tr>
                <tr>
                    <td>やや肥満 (25 ≤ BMI < 30)</td>
                    <td>{{ $statistics['bmi_distribution']['overweight'] }}人</td>
                    <td>{{ $statistics['bmi_percentages']['overweight'] }}%</td>
                </tr>
                <tr>
                    <td>肥満 (BMI ≥ 30)</td>
                    <td>{{ $statistics['bmi_distribution']['obese'] }}人</td>
                    <td>{{ $statistics['bmi_percentages']['obese'] }}%</td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Grade Statistics -->
    @if(count($statistics['by_grade']) > 0)
    <div class="section">
        <div class="section-title">学年別統計</div>
        <table>
            <thead>
                <tr>
                    <th>学年</th>
                    <th>人数</th>
                    <th>平均身長<br>(cm)</th>
                    <th>平均体重<br>(kg)</th>
                    <th>平均BMI</th>
                    <th>視力(左)</th>
                    <th>視力(右)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($statistics['by_grade'] as $grade)
                <tr>
                    <td>{{ $grade['grade'] }}年生</td>
                    <td>{{ $grade['count'] }}人</td>
                    <td>{{ number_format($grade['avg_height'], 1) }}</td>
                    <td>{{ number_format($grade['avg_weight'], 1) }}</td>
                    <td>{{ number_format($grade['avg_bmi'], 1) }}</td>
                    <td>{{ number_format($grade['avg_vision_left'], 2) }}</td>
                    <td>{{ number_format($grade['avg_vision_right'], 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Class Statistics -->
    @if(count($statistics['by_class']) > 0)
    <div class="section" style="page-break-before: always;">
        <div class="section-title">クラス別統計</div>
        <table>
            <thead>
                <tr>
                    <th>クラス</th>
                    <th>人数</th>
                    <th>平均身長<br>(cm)</th>
                    <th>平均体重<br>(kg)</th>
                    <th>平均BMI</th>
                    <th>視力(左)</th>
                    <th>視力(右)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($statistics['by_class'] as $class)
                <tr>
                    <td>{{ $class['class_name'] }}</td>
                    <td>{{ $class['count'] }}人</td>
                    <td>{{ number_format($class['avg_height'], 1) }}</td>
                    <td>{{ number_format($class['avg_weight'], 1) }}</td>
                    <td>{{ number_format($class['avg_bmi'], 1) }}</td>
                    <td>{{ number_format($class['avg_vision_left'], 2) }}</td>
                    <td>{{ number_format($class['avg_vision_right'], 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Ranges -->
    <div class="section">
        <div class="section-title">測定値の範囲</div>
        <table>
            <thead>
                <tr>
                    <th>項目</th>
                    <th>最小値</th>
                    <th>最大値</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>身長</td>
                    <td>{{ $statistics['ranges']['height']['min'] }} cm</td>
                    <td>{{ $statistics['ranges']['height']['max'] }} cm</td>
                </tr>
                <tr>
                    <td>体重</td>
                    <td>{{ $statistics['ranges']['weight']['min'] }} kg</td>
                    <td>{{ $statistics['ranges']['weight']['max'] }} kg</td>
                </tr>
                <tr>
                    <td>BMI</td>
                    <td>{{ $statistics['ranges']['bmi']['min'] }}</td>
                    <td>{{ $statistics['ranges']['bmi']['max'] }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>このレポートは健康記録システムにより自動生成されました</p>
        <p>ページ <span class="page-number"></span></p>
    </div>
</body>
</html>
