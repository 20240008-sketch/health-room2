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
        Schema::create('school_classes', function (Blueprint $table) {
            $table->id();
            $table->string('class_id', 10)->unique();
            $table->string('class_name', 50);
            $table->integer('grade');
            $table->integer('kumi');
            $table->integer('year');
            $table->timestamps();
            
            $table->index('class_id');
            $table->index(['year', 'grade']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_classes');
    }
};
