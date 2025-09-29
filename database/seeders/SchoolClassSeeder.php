<?php

namespace Database\Seeders;

use App\Models\SchoolClass;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SchoolClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currentYear = date('Y');
        $classes = [];

        // 1年生から3年生までのクラスデータを生成
        for ($grade = 1; $grade <= 3; $grade++) {
            for ($kumi = 1; $kumi <= 4; $kumi++) { // 各学年4クラスまで
                $classId = sprintf('%d-%d', $grade, $kumi);
                $className = sprintf('%d年%d組', $grade, $kumi);

                $classes[] = [
                    'class_id' => $classId,
                    'class_name' => $className,
                    'grade' => $grade,
                    'kumi' => $kumi,
                    'year' => $currentYear,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        SchoolClass::insert($classes);
    }
}
