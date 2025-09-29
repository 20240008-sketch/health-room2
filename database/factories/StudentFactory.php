<?php

namespace Database\Factories;

use App\Models\SchoolClass;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Student>
 */
class StudentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        static $studentIdCounter = 1;
        static $classIdCounter = [];

        // 日本の名前のサンプル
        $maleNames = [
            '田中太郎', '佐藤次郎', '高橋三郎', '伊藤四郎', '渡辺五郎',
            '山本六郎', '中村七郎', '小林八郎', '加藤九郎', '吉田十郎'
        ];
        
        $femaleNames = [
            '田中花子', '佐藤美咲', '高橋愛', '伊藤優', '渡辺恵',
            '山本さくら', '中村美香', '小林由美', '加藤香織', '吉田美穂'
        ];

        $maleKana = [
            'たなかたろう', 'さとうじろう', 'たかはしさぶろう', 'いとうしろう', 'わたなべごろう',
            'やまもとろくろう', 'なかむらしちろう', 'こばやしはちろう', 'かとうくろう', 'よしだじゅうろう'
        ];

        $femaleKana = [
            'たなかはなこ', 'さとうみさき', 'たかはしあい', 'いとうゆう', 'わたなべめぐみ',
            'やまもとさくら', 'なかむらみか', 'こばやしゆみ', 'かとうかおり', 'よしだみほ'
        ];

        $sex = $this->faker->randomElement(['male', 'female']);
        $nameIndex = $this->faker->numberBetween(0, 9);

        $name = $sex === 'male' ? $maleNames[$nameIndex] : $femaleNames[$nameIndex];
        $kana = $sex === 'male' ? $maleKana[$nameIndex] : $femaleKana[$nameIndex];

        // クラスIDを取得（存在する場合のみ）
        $classId = SchoolClass::inRandomOrder()->first()?->id ?? 1;

        // 各クラスの出席番号をカウント
        if (!isset($classIdCounter[$classId])) {
            $classIdCounter[$classId] = 1;
        }
        $studentNumber = $classIdCounter[$classId]++;

        return [
            'name' => $name,
            'name_kana' => $kana,
            'student_id' => sprintf('ST%04d', $studentIdCounter++),
            'student_number' => $studentNumber,
            'sex' => $sex,
            'class_id' => $classId,
        ];
    }
}
