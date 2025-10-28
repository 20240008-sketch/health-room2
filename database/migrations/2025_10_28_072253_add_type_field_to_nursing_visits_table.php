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
            // student_idをVARCHARに変更（学籍番号用）
            if (Schema::hasColumn('nursing_visits', 'student_id')) {
                $columnType = Schema::getColumnType('nursing_visits', 'student_id');
                if ($columnType !== 'string') {
                    $table->string('student_id')->change();
                }
            }
            
            // typeフィールド（illness, injury, consultation, other）
            if (!Schema::hasColumn('nursing_visits', 'type')) {
                $table->string('type')->default('illness')->after('student_id')->comment('状態: illness, injury, consultation, other');
            }
            
            // その他の必須フィールド
            if (!Schema::hasColumn('nursing_visits', 'absence_reason')) {
                $table->string('absence_reason', 50)->nullable()->comment('欠席理由');
            }
            if (!Schema::hasColumn('nursing_visits', 'subject')) {
                $table->string('subject', 50)->nullable()->comment('教科');
            }
            if (!Schema::hasColumn('nursing_visits', 'club')) {
                $table->string('club', 50)->nullable()->comment('部活');
            }
            if (!Schema::hasColumn('nursing_visits', 'event')) {
                $table->string('event', 50)->nullable()->comment('行事');
            }
            if (!Schema::hasColumn('nursing_visits', 'breakfast')) {
                $table->string('breakfast', 50)->nullable()->comment('朝食');
            }
            if (!Schema::hasColumn('nursing_visits', 'bowel_movement')) {
                $table->string('bowel_movement', 50)->nullable()->comment('便通');
            }
            if (!Schema::hasColumn('nursing_visits', 'treatment')) {
                $table->string('treatment', 100)->nullable()->comment('処置');
            }
            if (!Schema::hasColumn('nursing_visits', 'injury_location')) {
                $table->string('injury_location', 50)->nullable()->comment('けがの部位');
            }
            if (!Schema::hasColumn('nursing_visits', 'injury_place')) {
                $table->string('injury_place', 50)->nullable()->comment('けがをした場所');
            }
            if (!Schema::hasColumn('nursing_visits', 'surgical_treatment')) {
                $table->string('surgical_treatment', 100)->nullable()->comment('外科的処置');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nursing_visits', function (Blueprint $table) {
            $table->dropColumn([
                'type',
                'absence_reason',
                'subject',
                'club',
                'event',
                'breakfast',
                'bowel_movement',
                'treatment',
                'injury_location',
                'injury_place',
                'surgical_treatment',
            ]);
        });
    }
};
