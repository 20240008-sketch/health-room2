<?php

namespace Database\Factories;

use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\HealthRecord>
 */
class HealthRecordFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $currentYear = date('Y');
        
        // 年齢別の身長・体重の平均値（日本の学生データを参考）
        $ageData = [
            12 => ['height' => [145, 155], 'weight' => [35, 45]], // 中1
            13 => ['height' => [150, 160], 'weight' => [40, 50]], // 中2  
            14 => ['height' => [155, 165], 'weight' => [45, 55]], // 中3
            15 => ['height' => [160, 170], 'weight' => [50, 60]], // 高1
            16 => ['height' => [162, 172], 'weight' => [52, 62]], // 高2
            17 => ['height' => [164, 174], 'weight' => [54, 64]], // 高3
        ];

        // ランダムな年齢を選択（中学生・高校生相当）
        $age = $this->faker->numberBetween(12, 17);
        $ageInfo = $ageData[$age];

        // 性別による身長・体重の調整（男子は平均的に高く、重い）
        $isMale = $this->faker->boolean();

        if ($isMale) {
            $height = $this->faker->numberBetween($ageInfo['height'][0], $ageInfo['height'][1]);
            $weight = $this->faker->numberBetween($ageInfo['weight'][0], $ageInfo['weight'][1]);
        } else {
            // 女子は平均的に少し低く、軽い
            $height = $this->faker->numberBetween($ageInfo['height'][0] - 5, $ageInfo['height'][1] - 5);
            $weight = $this->faker->numberBetween($ageInfo['weight'][0] - 3, $ageInfo['weight'][1] - 3);
        }

        // 小数点以下の値を追加してリアルさを演出
        $height += $this->faker->numberBetween(0, 99) / 100;
        $weight += $this->faker->numberBetween(0, 99) / 100;

        return [
            'year' => $currentYear, // デフォルトは現在年度
            'height' => round($height, 2),
            'weight' => round($weight, 2),
        ];
    }
}