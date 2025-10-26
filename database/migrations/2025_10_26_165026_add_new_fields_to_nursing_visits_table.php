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
            // 欠席・遅刻の原因・理由
            $table->string('absence_reason', 50)->nullable()->after('type_detail');
            
            // 発生時の詳細（教科、部活、行事）
            $table->string('subject', 50)->nullable()->after('occurrence_time');
            $table->string('club', 50)->nullable()->after('subject');
            $table->string('event', 50)->nullable()->after('club');
            
            // 内科関連
            $table->string('breakfast', 50)->nullable()->after('event');
            $table->string('bowel_movement', 50)->nullable()->after('breakfast');
            $table->string('treatment', 100)->nullable()->after('bowel_movement');
            
            // 外科関連
            $table->string('injury_location', 50)->nullable()->after('treatment');
            $table->string('injury_place', 50)->nullable()->after('injury_location');
            $table->string('surgical_treatment', 100)->nullable()->after('injury_place');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nursing_visits', function (Blueprint $table) {
            $table->dropColumn([
                'absence_reason',
                'subject',
                'club',
                'event',
                'breakfast',
                'bowel_movement',
                'treatment',
                'injury_location',
                'injury_place',
                'surgical_treatment'
            ]);
        });
    }
};
