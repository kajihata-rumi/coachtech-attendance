<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceCorrectionStoreRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'clock_in' => ['required', 'date_format:H:i'],
            'clock_out' => ['required', 'date_format:H:i'],
            'breaks' => ['nullable', 'array'],
            'breaks.*.id' => ['nullable', 'integer'],
            'breaks.*.start' => ['nullable', 'date_format:H:i'],
            'breaks.*.end' => ['nullable', 'date_format:H:i'],
            'reason' => ['required', 'string'],
        ];
    }

    public function messages()
    {
        return [
            'clock_in.required' => '出勤時間もしくは退勤時間が不適切な値です',
            'clock_in.date_format' => '出勤時間もしくは退勤時間が不適切な値です',
            'clock_out.required' => '出勤時間もしくは退勤時間が不適切な値です',
            'clock_out.date_format' => '出勤時間もしくは退勤時間が不適切な値です',

            'breaks.*.start.date_format' => '休憩時間が不適切な値です',
            'breaks.*.end.date_format' => '休憩時間が不適切な値です',

            'reason.required' => '備考を記入してください',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $clockIn = $this->input('clock_in');
            $clockOut = $this->input('clock_out');
            $breaks = $this->input('breaks', []);

            if (!$this->isValidTime($clockIn) || !$this->isValidTime($clockOut)) {
                return;
            }

            if ($clockIn >= $clockOut) {
                $validator->errors()->add(
                    'clock_in',
                    '出勤時間もしくは退勤時間が不適切な値です'
                );
                return;
            }

            foreach ($breaks as $index => $break) {
                $start = $break['start'] ?? null;
                $end = $break['end'] ?? null;

                if (empty($start) && empty($end)) {
                    continue;
                }

                if (empty($start) || empty($end)) {
                    $validator->errors()->add(
                        "breaks.{$index}.start",
                        '休憩時間が不適切な値です'
                    );
                    continue;
                }

                if (!$this->isValidTime($start) || !$this->isValidTime($end)) {
                    continue;
                }

                if ($start >= $end) {
                    $validator->errors()->add(
                    "breaks.{$index}.start",
                    '休憩時間が不適切な値です'
                );

                continue;
                }

                if ($start < $clockIn || $start > $clockOut) {
                    $validator->errors()->add(
                    "breaks.{$index}.start",
                    '休憩時間が不適切な値です'
                );

                continue;
                }

                if ($end > $clockOut) {
                    $validator->errors()->add(
                    "breaks.{$index}.end",
                    '休憩時間もしくは退勤時間が不適切な値です'
                );

                }
            }
        });
    }

    private function isValidTime($time)
    {
        return is_string($time) && preg_match('/^\d{2}:\d{2}$/', $time);
    }
}