<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\HealthRecordController;
use App\Http\Controllers\StatisticsController;
use App\Http\Controllers\Api\V1\NursingLogController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// 認証不要のテスト用エンドポイント
Route::get('/health', function () {
    return response()->json([
        'status' => 'OK',
        'message' => '健康管理システムAPI稼働中',
        'timestamp' => now()->toISOString()
    ]);
});

// API v1 グループ
Route::group(['prefix' => 'v1'], function () {
    
    // 学生検索関連の追加エンドポイント
    Route::get('students/search/suggestions', [StudentController::class, 'searchSuggestions'])
        ->name('students.search.suggestions');
    Route::get('students/search/advanced', [StudentController::class, 'advancedSearch'])
        ->name('students.search.advanced');
    Route::get('students/export/data', [StudentController::class, 'exportData'])
        ->name('students.export.data');
    
    // 学生の健康記録取得
    Route::get('students/{id}/health-records', [StudentController::class, 'healthRecords'])
        ->name('students.health-records');
    
    // 学生管理API
    Route::apiResource('students', StudentController::class);
    
    // クラス管理API
    Route::get('classes/export-pdf', [ClassController::class, 'exportPdf'])
        ->name('classes.export-pdf');
    Route::apiResource('classes', ClassController::class);
    
    // 健康記録関連の追加エンドポイント（先に定義する必要がある）
    Route::get('health-records/export-pdf', [HealthRecordController::class, 'exportPdf'])
        ->name('health-records.export-pdf');
    Route::post('health-records/bulk', [HealthRecordController::class, 'bulkStore'])
        ->name('health-records.bulk-store');
    Route::get('health-records/peer-comparison', [HealthRecordController::class, 'peerComparison'])
        ->name('health-records.peer-comparison');
    Route::get('health-records/statistics', [HealthRecordController::class, 'statistics'])
        ->name('health-records.statistics');
    
    // 健康記録管理API
    Route::apiResource('health-records', HealthRecordController::class);
    
    // 養護日誌PDF生成API
    Route::get('nursing-log/test-pdf', [NursingLogController::class, 'test'])
        ->name('nursing-log.test-pdf');
    Route::post('nursing-log/generate-pdf', [NursingLogController::class, 'generatePdf'])
        ->name('nursing-log.generate-pdf');
    
    // 統計データAPI
    Route::prefix('statistics')->name('statistics.')->group(function () {
        Route::get('system', [StatisticsController::class, 'systemStats'])
            ->name('system');
        Route::get('grade', [StatisticsController::class, 'gradeStats'])
            ->name('grade');
        Route::get('class/{classId}', [StatisticsController::class, 'classStats'])
            ->name('class');
        Route::get('health-records', [StatisticsController::class, 'healthStats'])
            ->name('health');
        Route::get('grade-averages', [StatisticsController::class, 'gradeAverages'])
            ->name('grade-averages');
        Route::get('class-averages', [StatisticsController::class, 'classAverages'])
            ->name('class-averages');
    });
    
});

// 開発環境でのみ利用可能なデバッグエンドポイント
if (app()->environment('local', 'testing')) {
    Route::group(['prefix' => 'debug'], function () {
        
        // データベース接続確認
        Route::get('/database', function () {
            try {
                DB::connection()->getPdo();
                $studentCount = \App\Models\Student::count();
                $classCount = \App\Models\SchoolClass::count();
                $healthRecordCount = \App\Models\HealthRecord::count();
                
                return response()->json([
                    'database_status' => 'connected',
                    'counts' => [
                        'students' => $studentCount,
                        'classes' => $classCount,
                        'health_records' => $healthRecordCount,
                    ],
                    'message' => 'データベース接続確認済み'
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'database_status' => 'error',
                    'error' => $e->getMessage()
                ], 500);
            }
        });
        
        // ルート一覧表示
        Route::get('/routes', function () {
            $routes = [];
            foreach (Route::getRoutes() as $route) {
                if (str_starts_with($route->uri(), 'api/')) {
                    $routes[] = [
                        'method' => implode('|', $route->methods()),
                        'uri' => $route->uri(),
                        'name' => $route->getName(),
                        'action' => $route->getActionName()
                    ];
                }
            }
            
            return response()->json([
                'message' => 'API Routes一覧',
                'routes' => $routes,
                'count' => count($routes)
            ]);
        });
        
    });
}