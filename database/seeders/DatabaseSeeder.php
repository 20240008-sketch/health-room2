<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\HealthRecord;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 既存のUserデータは残す
        // User::factory(10)->create();
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // 健康管理システムのテストデータ作成
        $this->call([
            SchoolClassSeeder::class,
        ]);

        // 各クラスに生徒を作成（1クラスあたり20-30人）
        SchoolClass::all()->each(function ($class) {
            $studentCount = rand(20, 30);
            $students = Student::factory($studentCount)->create([
                'class_id' => $class->id
            ]);

            // 各生徒に健康記録を作成（現在年度のみ、重複回避）
            $students->each(function ($student) {
                $currentYear = date('Y');
                
                // 現在年度の記録を作成
                HealthRecord::factory()->create([
                    'student_id' => $student->id,
                    'year' => $currentYear,
                ]);
                
                // 50%の確率で前年度の記録も作成
                if (rand(0, 1)) {
                    HealthRecord::factory()->create([
                        'student_id' => $student->id,
                        'year' => $currentYear - 1,
                    ]);
                }
            });
        });

        $this->command->info('健康管理システムのテストデータを作成しました');
        $this->command->info('クラス数: ' . SchoolClass::count());
        $this->command->info('生徒数: ' . Student::count());
        $this->command->info('健康記録数: ' . HealthRecord::count());
    }
}
