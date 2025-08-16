<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class AttendanceCorrectionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'clock_in' => 'required|date_format:H:i',
            'clock_out' => 'required|date_format:H:i',
            'break_in' => 'nullable|date_format:H:i',
            'break_out' => 'nullable|date_format:H:i',
            'reason' => 'required|string',
            'target_date' => 'required|date',
        ];
    }

    public function messages()
    {
        return [
            'clock_in.required' => '出勤時間は必須です',
            'clock_out.required' => '退勤時間は必須です',
            'break_in.date_format' => '休憩開始時間の形式が正しくありません',
            'break_out.date_format' => '休憩終了時間の形式が正しくありません',
            'reason.required' => '備考を記入してください',
            'target_date.required' => '対象日は必須です',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $clockIn = Carbon::createFromFormat('H:i', $this->clock_in);
            $clockOut = Carbon::createFromFormat('H:i', $this->clock_out);

            if ($clockIn->gte($clockOut)) {
                $validator->errors()->add('clock_in', '出勤時間もしくは退勤時間が不適切な値です');
            }

            if ($this->break_in) {
                $startTime = Carbon::createFromFormat('H:i', $this->break_in);
                if ($startTime->lt($clockIn) || $startTime->gt($clockOut)) {
                    $validator->errors()->add('break_in', '休憩時間が勤務時間外です');
                }
            }
            if ($this->break_out) {
                $endTime = Carbon::createFromFormat('H:i', $this->break_out);
                if ($endTime->lt($clockIn) || $endTime->gt($clockOut)) {
                    $validator->errors()->add('break_out', '休憩時間が勤務時間外です');
                }
            }
        });
    }
}
