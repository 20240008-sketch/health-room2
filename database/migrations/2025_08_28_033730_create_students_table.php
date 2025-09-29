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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('kana', 100);
            $table->string('student_id', 20)->unique();
            $table->integer('student_number');
            $table->enum('sex', ['male', 'female']);
            $table->foreignId('class_id')->constrained('school_classes')->onDelete('cascade');
            $table->timestamps();
            
            $table->index('student_id');
            $table->index('class_id');
            $table->index('name');
            $table->index('kana');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};