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
        Schema::table('nursing_visits', function (Blueprint $table) {
            // 分類カラムを追加（内科、外科、その他、欠席）
            $table->string('category', 20)->default('internal')->after('student_id');
            
            // 種別の詳細カラムを追加（症状など）
            $table->string('type_detail', 50)->nullable()->after('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nursing_visits', function (Blueprint $table) {
            $table->dropColumn(['category', 'type_detail']);
        });
    }
};
