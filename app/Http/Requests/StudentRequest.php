<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StudentRequest extends FormRequest
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
        $studentId = $this->route('student'); // ルートパラメータから取得
        
        return [
            'name' => [
                'required',
                'string',
                'max:100',
            ],
            'name_kana' => [
                'required',
                'string',
                'max:100',
                'regex:/^[ぁ-ん\s]+$/', // ひらがなとスペースのみ許可
            ],
            'student_id' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^[A-Za-z0-9]+$/', // 英数字のみ
                Rule::unique('students', 'student_id')->ignore($studentId),
            ],
            'student_number' => [
                'required',
                'integer',
                'min:1',
                'max:99',
            ],
            'gender' => [
                'required',
                Rule::in(['male', 'female']),
            ],
            'class_id' => [
                'required',
                'string',
                Rule::in(['特進', '進学', '調理', '福祉', '情会', '総合１', '総合２', '総合３']),
            ],
            'grade_id' => [
                'required',
                'integer',
                Rule::in([1, 2, 3]),
            ],
            'birth_date' => [
                'required',
                'date',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => '氏名は必須です。',
            'name.string' => '氏名は文字列で入力してください。',
            'name.max' => '氏名は100文字以内で入力してください。',
            
            'name_kana.required' => 'ふりがなは必須です。',
            'name_kana.string' => 'ふりがなは文字列で入力してください。',
            'name_kana.max' => 'ふりがなは100文字以内で入力してください。',
            'name_kana.regex' => 'ふりがなはひらがなで入力してください。',
            
            'student_id.required' => '生徒IDは必須です。',
            'student_id.string' => '生徒IDは文字列で入力してください。',
            'student_id.max' => '生徒IDは20文字以内で入力してください。',
            'student_id.regex' => '生徒IDは英数字で入力してください。',
            'student_id.unique' => 'この生徒IDは既に使用されています。',
            
            'student_number.required' => '出席番号は必須です。',
            'student_number.integer' => '出席番号は数値で入力してください。',
            'student_number.min' => '出席番号は1以上で入力してください。',
            'student_number.max' => '出席番号は99以下で入力してください。',
            
            'gender.required' => '性別は必須です。',
            'gender.in' => '性別は男性または女性を選択してください。',
            
            'class_id.required' => 'クラスは必須です。',
            'class_id.string' => 'クラスは正しい形式で入力してください。',
            'class_id.in' => '指定されたクラスから選択してください。',
            
            'grade_id.required' => '学年は必須です。',
            'grade_id.integer' => '学年は数値で入力してください。',
            'grade_id.in' => '学年は1、2、3のいずれかを選択してください。',
            
            'birth_date.required' => '生年月日は必須です。',
            'birth_date.date' => '生年月日は正しい日付を入力してください。',
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
            'name' => '氏名',
            'name_kana' => 'ふりがな',
            'student_id' => '生徒ID',
            'student_number' => '出席番号',
            'gender' => '性別',
            'class_id' => 'クラス',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // ふりがなのスペースを正規化（全角→半角）
        if ($this->has('name_kana')) {
            $this->merge([
                'name_kana' => mb_convert_kana($this->name_kana, 's', 'UTF-8'),
            ]);
        }
        
        // 生徒IDを大文字に統一
        if ($this->has('student_id')) {
            $this->merge([
                'student_id' => strtoupper($this->student_id),
            ]);
        }
    }
}
