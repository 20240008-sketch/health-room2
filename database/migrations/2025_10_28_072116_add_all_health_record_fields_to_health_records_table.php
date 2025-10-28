<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('health_records', function (Blueprint $table) {
            // 視力フィールド（A, B, C, D）
            if (!Schema::hasColumn('health_records', 'vision_left')) {
                $table->string('vision_left', 1)->nullable()->after('weight');
            }
            if (!Schema::hasColumn('health_records', 'vision_right')) {
                $table->string('vision_right', 1)->nullable()->after('vision_left');
            }
            if (!Schema::hasColumn('health_records', 'vision_left_corrected')) {
                $table->string('vision_left_corrected', 1)->nullable()->after('vision_right');
            }
            if (!Schema::hasColumn('health_records', 'vision_right_corrected')) {
                $table->string('vision_right_corrected', 1)->nullable()->after('vision_left_corrected');
            }
            
            // 聴力フィールド
            if (!Schema::hasColumn('health_records', 'hearing_left')) {
                $table->string('hearing_left')->nullable()->after('vision_right_corrected');
            }
            if (!Schema::hasColumn('health_records', 'hearing_right')) {
                $table->string('hearing_right')->nullable()->after('hearing_left');
            }
            
            // 測定日
            if (!Schema::hasColumn('health_records', 'measured_date')) {
                $table->date('measured_date')->nullable()->after('hearing_right');
            }
            
            // 7つの医療検診フィールド
            if (!Schema::hasColumn('health_records', 'ophthalmology_result')) {
                $table->text('ophthalmology_result')->nullable()->after('measured_date')->comment('眼科検診備考');
            }
            if (!Schema::hasColumn('health_records', 'otolaryngology_result')) {
                $table->text('otolaryngology_result')->nullable()->after('ophthalmology_result')->comment('耳鼻科検診');
            }
            if (!Schema::hasColumn('health_records', 'internal_medicine_result')) {
                $table->text('internal_medicine_result')->nullable()->after('otolaryngology_result')->comment('内科検診');
            }
            if (!Schema::hasColumn('health_records', 'hearing_test_result')) {
                $table->text('hearing_test_result')->nullable()->after('internal_medicine_result')->comment('聴力検査');
            }
            if (!Schema::hasColumn('health_records', 'tuberculosis_test_result')) {
                $table->text('tuberculosis_test_result')->nullable()->after('hearing_test_result')->comment('結核検査');
            }
            if (!Schema::hasColumn('health_records', 'urine_test_result')) {
                $table->text('urine_test_result')->nullable()->after('tuberculosis_test_result')->comment('尿検査');
            }
            if (!Schema::hasColumn('health_records', 'ecg_result')) {
                $table->text('ecg_result')->nullable()->after('urine_test_result')->comment('心電図');
            }
            
            // 眼科検診の詳細フィールド
            if (!Schema::hasColumn('health_records', 'ophthalmology_exam_result')) {
                $table->string('ophthalmology_exam_result', 50)->nullable()->after('ecg_result')->comment('眼科検診結果');
            }
            if (!Schema::hasColumn('health_records', 'ophthalmology_diagnosis')) {
                $table->string('ophthalmology_diagnosis', 50)->nullable()->after('ophthalmology_exam_result')->comment('眼科診断結果');
            }
            if (!Schema::hasColumn('health_records', 'ophthalmology_treatment')) {
                $table->string('ophthalmology_treatment', 50)->nullable()->after('ophthalmology_diagnosis')->comment('眼科処置');
            }
        });
        
        // student_idカラムのデータ型を変更（もし必要なら）
        // 注意: 既存のデータがある場合は、先にデータを移行する必要があります
        if (Schema::hasColumn('health_records', 'student_id')) {
            $columnType = Schema::getColumnType('health_records', 'student_id');
            if ($columnType !== 'string') {
                // student_idをVARCHARに変更する場合は、データ移行が必要です
                // 本番環境では慎重に実行してください
                Schema::table('health_records', function (Blueprint $table) {
                    $table->string('student_id')->change();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('health_records', function (Blueprint $table) {
            $table->dropColumn([
                'vision_left',
                'vision_right',
                'vision_left_corrected',
                'vision_right_corrected',
                'hearing_left',
                'hearing_right',
                'measured_date',
                'ophthalmology_result',
                'otolaryngology_result',
                'internal_medicine_result',
                'hearing_test_result',
                'tuberculosis_test_result',
                'urine_test_result',
                'ecg_result',
                'ophthalmology_exam_result',
                'ophthalmology_diagnosis',
                'ophthalmology_treatment',
            ]);
        });
    }
};
