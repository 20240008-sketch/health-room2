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
            $table->decimal('vision_left_corrected', 3, 1)->nullable()->after('vision_right');
            $table->decimal('vision_right_corrected', 3, 1)->nullable()->after('vision_left_corrected');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('health_records', function (Blueprint $table) {
            $table->dropColumn(['vision_left_corrected', 'vision_right_corrected']);
        });
    }
};
