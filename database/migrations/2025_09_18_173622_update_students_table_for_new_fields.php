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
        Schema::table('students', function (Blueprint $table) {
            // Rename columns and add new fields
            $table->renameColumn('kana', 'name_kana');
            $table->renameColumn('sex', 'gender');
            
            // Add new fields
            $table->date('birth_date')->nullable()->after('gender');
            $table->string('status', 20)->default('active')->after('class_id');
            $table->string('phone', 20)->nullable()->after('status');
            $table->string('emergency_contact', 100)->nullable()->after('phone');
            $table->text('address')->nullable()->after('emergency_contact');
            $table->text('allergies')->nullable()->after('address');
            $table->text('medical_conditions')->nullable()->after('allergies');
            $table->text('medications')->nullable()->after('medical_conditions');
            $table->text('notes')->nullable()->after('medications');
            
            // Modify class_id to be string instead of foreign key
            $table->dropForeign(['class_id']);
            $table->dropIndex(['class_id']);
            $table->string('class_id', 50)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            // Reverse the changes
            $table->renameColumn('name_kana', 'kana');
            $table->renameColumn('gender', 'sex');
            
            // Drop added columns
            $table->dropColumn([
                'birth_date', 'status', 'phone', 'emergency_contact', 
                'address', 'allergies', 'medical_conditions', 'medications', 'notes'
            ]);
            
            // Restore foreign key constraint
            $table->unsignedBigInteger('class_id')->change();
            $table->foreign('class_id')->references('id')->on('school_classes')->onDelete('cascade');
            $table->index('class_id');
        });
    }
};
