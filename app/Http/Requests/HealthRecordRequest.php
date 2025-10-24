<?php

namespace App\Http\Requests;

use App\Models\HealthRecord;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HealthRecordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // 認証機能は不要のため常にtrue
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $healthRecordId = $this->route('health_record'); // ルートパラメータから取得
        $currentYear = date('Y');
        
        $rules = [
            'year' => [
                'required',
                'integer',
                'min:2020',
                'max:' . ($currentYear + 1),
            ],
            'academic_year' => [
                'sometimes',
                'integer',
                'min:2020',
                'max:' . ($currentYear + 1),
            ],
            'measured_date' => [
                'sometimes',
                'date',
            ],
            'height' => [
                'nullable',
                'numeric',
                'min:0',
                'max:300',
                'regex:/^\d+(\.\d{1,2})?$/', // 小数点以下2桁まで
            ],
            'weight' => [
                'nullable',
                'numeric',
                'min:0',
                'max:200',
                'regex:/^\d+(\.\d{1,2})?$/', // 小数点以下2桁まで
            ],
            'bmi' => [
                'sometimes',
                'nullable',
                'numeric',
                'min:0',
                'max:100',
            ],
            'notes' => [
                'sometimes',
                'nullable',
                'string',
                'max:1000',
            ],
            'edit_reason' => [
                'sometimes',
                'nullable',
                'string',
                'max:500',
            ],
            'vision_left' => [
                'sometimes',
                'nullable',
                'numeric',
                'min:0',
                'max:2',
            ],
            'vision_right' => [
                'sometimes',
                'nullable',
                'numeric',
                'min:0',
                'max:2',
            ],
            'vision_left_corrected' => [
                'sometimes',
                'nullable',
                'numeric',
                'min:0',
                'max:3',
            ],
            'vision_right_corrected' => [
                'sometimes',
                'nullable',
                'numeric',
                'min:0',
                'max:3',
            ],
            'hearing_left' => [
                'sometimes',
                'nullable',
                'boolean',
            ],
            'hearing_right' => [
                'sometimes',
                'nullable',
                'boolean',
            ],
            'ophthalmology_result' => [
                'sometimes',
                'nullable',
                'string',
                'max:1000',
            ],
            'otolaryngology_result' => [
                'sometimes',
                'nullable',
                'string',
                'max:1000',
            ],
            'internal_medicine_result' => [
                'sometimes',
                'nullable',
                'string',
                'max:1000',
            ],
            'hearing_test_result' => [
                'sometimes',
                'nullable',
                'string',
                'max:1000',
            ],
            'tuberculosis_test_result' => [
                'sometimes',
                'nullable',
                'string',
                'max:1000',
            ],
            'urine_test_result' => [
                'sometimes',
                'nullable',
                'string',
                'max:1000',
            ],
            'ecg_result' => [
                'sometimes',
                'nullable',
                'string',
                'max:1000',
            ],
        ];

        // 新規作成時のみstudent_idを必須とする
        if ($this->isMethod('POST')) {
            $rules['student_id'] = [
                'required',
                'string',
                'exists:students,student_id',
            ];
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'student_id.required' => '生徒は必須です。',
            'student_id.string' => '生徒IDは文字列で入力してください。',
            'student_id.exists' => '指定された生徒は存在しません。',
            
            'year.required' => '年度は必須です。',
            'year.integer' => '年度は数値で入力してください。',
            'year.min' => '年度は2020年以降で入力してください。',
            'year.max' => '年度は来年度までで入力してください。',
            
            'height.numeric' => '身長は数値で入力してください。',
            'height.min' => '身長は0以上で入力してください。',
            'height.max' => '身長は300cm以下で入力してください。',
            'height.regex' => '身長は小数点以下2桁までで入力してください。',
            
            'weight.numeric' => '体重は数値で入力してください。',
            'weight.min' => '体重は0以上で入力してください。',
            'weight.max' => '体重は200kg以下で入力してください。',
            'weight.regex' => '体重は小数点以下2桁までで入力してください。',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'student_id' => '生徒',
            'year' => '年度',
            'height' => '身長',
            'weight' => '体重',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // 少なくとも1つの測定項目が入力されているか確認
            $hasAnyMeasurement = !empty($this->height) || 
                                !empty($this->weight) || 
                                !empty($this->vision_left) || 
                                !empty($this->vision_right) ||
                                !empty($this->vision_left_corrected) ||
                                !empty($this->vision_right_corrected);
            
            if (!$hasAnyMeasurement) {
                $validator->addFailure('height', 'no_measurements', '少なくとも1つの測定項目を入力してください。');
            }

            // 既存レコードの重複チェック（同じ生徒・年度・測定日）
            // measured_dateが同じ場合のみ重複とみなす（個別測定で複数回保存可能にするため）
            if ($this->isMethod('POST')) {
                $query = HealthRecord::where('student_id', $this->student_id)
                                    ->where('year', $this->year);
                
                // measured_dateが指定されている場合のみ、同じ日付の重複をチェック
                if ($this->measured_date) {
                    $query->where('measured_date', $this->measured_date);
                }
                
                $existingRecord = $query->first();

                if ($existingRecord) {
                    $validator->addFailure('measured_date', 'unique_student_year_date', 'この生徒の指定年度・測定日の健康記録は既に存在します。');
                }
            } elseif ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
                $healthRecordId = $this->route('health_record');
                $healthRecord = HealthRecord::find($healthRecordId);

                if ($healthRecord) {
                    $query = HealthRecord::where('student_id', $healthRecord->student_id)
                                        ->where('year', $this->year)
                                        ->where('id', '!=', $healthRecordId);
                    
                    // measured_dateが指定されている場合のみ、同じ日付の重複をチェック
                    if ($this->measured_date) {
                        $query->where('measured_date', $this->measured_date);
                    }
                    
                    $existingRecord = $query->first();

                    if ($existingRecord) {
                        $validator->addFailure('measured_date', 'unique_student_year_date', 'この生徒の指定年度・測定日の健康記録は既に存在します。');
                    }
                }
            }

            // BMI計算による妥当性チェック
            if ($this->height && $this->weight) {
                $heightM = $this->height / 100;
                $bmi = $this->weight / ($heightM * $heightM);

                // 極端に低いBMI（10未満）または高いBMI（50以上）の場合は警告
                if ($bmi < 10) {
                    $validator->addFailure('weight', 'bmi_too_low', '身長と体重の組み合わせが適切ではありません（BMIが低すぎます）。');
                } elseif ($bmi > 50) {
                    $validator->addFailure('weight', 'bmi_too_high', '身長と体重の組み合わせが適切ではありません（BMIが高すぎます）。');
                }
            }
        });
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // 数値の正規化（全角→半角）
        if ($this->has('height') && !empty($this->height)) {
            $this->merge([
                'height' => mb_convert_kana($this->height, 'n', 'UTF-8'),
            ]);
        }
        
        if ($this->has('weight') && !empty($this->weight)) {
            $this->merge([
                'weight' => mb_convert_kana($this->weight, 'n', 'UTF-8'),
            ]);
        }

        if ($this->has('year') && !empty($this->year)) {
            $this->merge([
                'year' => mb_convert_kana($this->year, 'n', 'UTF-8'),
            ]);
        }
    }
}
