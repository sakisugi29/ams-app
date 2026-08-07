<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceCorrectionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'clock_in' => 'nullable|date_format:H:i|before:clock_out',
            'clock_out' => 'nullable|date_format:H:i|after:clock_in',

            'break_start1' => 'nullable|date_format:H:i|after:clock_in|before:clock_out',
            'break_end1' => 'nullable|date_format:H:i|after:break_start1|before:clock_out',

            'break_start2' => 'nullable|date_format:H:i|after:clock_in|before:clock_out',
            'break_end2' => 'nullable|date_format:H:i|after:break_start2|before:clock_out',

            'comment' => 'required|string',
        ];
    }

    public function messages()
    {
        return [
            'clock_in.before' => '出勤時間もしくは退勤時間が不適切な値です',
            'clock_out.after' => '出勤時間もしくは退勤時間が不適切な値です',

            'break_start1.after' => '休憩時間が不適切な値です',
            'break_start1.before' => '休憩時間が不適切な値です',
            'break_end1.after' => '休憩時間もしくは退勤時間が不適切な値です',
            'break_end1.before' => '休憩時間もしくは退勤時間が不適切な値です',

            'break_start2.after' => '休憩時間が不適切な値です',
            'break_start2.before' => '休憩時間が不適切な値です',
            'break_end2.after' => '休憩時間もしくは退勤時間が不適切な値です',
            'break_end2.before' => '休憩時間もしくは退勤時間が不適切な値です',

            'comment.required' => '備考を記入してください',
        ];

    }
}
