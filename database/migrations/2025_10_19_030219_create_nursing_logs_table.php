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
        Schema::create('nursing_logs', function (Blueprint $table) {
            $table->id();
            $table->date('date')->unique(); // 日付（ユニーク）
            $table->integer('year')->nullable(); // 令和年
            $table->integer('month')->nullable(); // 月
            $table->integer('day')->nullable(); // 日
            $table->string('weather')->nullable(); // 天候
            $table->string('temperature')->nullable(); // 温度
            $table->string('humidity')->nullable(); // 湿度
            $table->json('absence')->nullable(); // 欠席者数（JSON形式）
            $table->json('visits')->nullable(); // 来室者記録（JSON形式）
            $table->text('school_events')->nullable(); // 学校行事
            $table->string('water_quality')->nullable(); // プール水質
            $table->string('chlorine')->nullable(); // 塩素濃度
            $table->text('notes')->nullable(); // 特記事項
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nursing_logs');
    }
};
