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
            $table->date('measured_date')->nullable()->after('year');
            
            // unique制約を削除して、同じ生徒・年度でも測定日が違えば保存可能にする
            $table->dropUnique(['student_id', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('health_records', function (Blueprint $table) {
            $table->dropColumn('measured_date');
            $table->unique(['student_id', 'year']);
        });
    }
};
