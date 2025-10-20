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
        Schema::create('nursing_visits', function (Blueprint $table) {
            $table->id();
            $table->date('date'); // 日付
            $table->time('time'); // 時間
            $table->foreignId('student_id')->constrained()->onDelete('cascade'); // 学生ID
            $table->enum('type', ['injury', 'illness', 'other'])->default('illness'); // 種別（けが、病気、その他）
            $table->string('occurrence_time')->nullable(); // 発生時（休み時間、授業中など）
            $table->text('treatment_notes')->nullable(); // 処置・原因・備考
            $table->timestamps();
            
            // インデックス
            $table->index('date');
            $table->index('student_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nursing_visits');
    }
};
