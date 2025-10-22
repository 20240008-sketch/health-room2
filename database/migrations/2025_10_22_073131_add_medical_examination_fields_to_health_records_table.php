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
            $table->text('ophthalmology_result')->nullable()->after('vision_right_corrected')->comment('眼科検診結果');
            $table->text('otolaryngology_result')->nullable()->after('ophthalmology_result')->comment('耳鼻科検診結果');
            $table->text('internal_medicine_result')->nullable()->after('otolaryngology_result')->comment('内科検診結果');
            $table->text('hearing_test_result')->nullable()->after('internal_medicine_result')->comment('聴力検査結果');
            $table->text('tuberculosis_test_result')->nullable()->after('hearing_test_result')->comment('結核検査結果');
            $table->text('urine_test_result')->nullable()->after('tuberculosis_test_result')->comment('尿検査結果');
            $table->text('ecg_result')->nullable()->after('urine_test_result')->comment('心電図検査結果');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('health_records', function (Blueprint $table) {
            $table->dropColumn([
                'ophthalmology_result',
                'otolaryngology_result',
                'internal_medicine_result',
                'hearing_test_result',
                'tuberculosis_test_result',
                'urine_test_result',
                'ecg_result'
            ]);
        });
    }
};
