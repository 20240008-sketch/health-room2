<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // SQLiteで外部キーを有効化
        if (DB::getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON');
        }
        
        Schema::create('attendance_records', function (Blueprint $table) {
            $table->id();
            $table->string('student_id');
            $table->date('date');
            $table->enum('status', ['present', 'absent', 'late', 'early_leave'])->default('present');
            $table->time('arrival_time')->nullable();
            $table->time('departure_time')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // 外部キー制約
            $table->foreign('student_id')
                  ->references('student_id')
                  ->on('students')
                  ->onDelete('cascade');
            
            // 同じ学生が同じ日に複数の記録を持たないようにする
            $table->unique(['student_id', 'date'], 'unique_student_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_records');
    }
};
