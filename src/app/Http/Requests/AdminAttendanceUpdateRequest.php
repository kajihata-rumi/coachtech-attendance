<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class AdminAttendanceUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'clock_in' => ['required', 'date_format:H:i'],
            'clock_out' => ['required', 'date_format:H:i'],
            'breaks' => ['nullable', 'array'],
            'breaks.*.start' => ['nullable', 'date_format:H:i'],
            'breaks.*.end' => ['nullable', 'date_format:H:i'],
            'note' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'clock_in.required' => '出勤時間もしくは退勤時間が不適切な値です',
            'clock_out.required' => '出勤時間もしくは退勤時間が不適切な値です',
            'clock_in.date_format' => '出勤時間もしくは退勤時間が不適切な値です',
            'clock_out.date_format' => '出勤時間もしくは退勤時間が不適切な値です',
            'breaks.*.start.date_format' => '休憩時間が不適切な値です',
            'breaks.*.end.date_format' => '休憩時間もしくは退勤時間が不適切な値です',
            'note.required' => '備考を記入してください',
        ];
    }

public function withValidator(Validator $validator): void
{
    $validator->after(function (Validator $validator) {
        $clockIn = $this->input('clock_in');
        $clockOut = $this->input('clock_out');
        $breaks = $this->input('breaks', []);

        if ($clockIn && $clockOut && $clockIn >= $clockOut) {
            $validator->errors()->add(
                'clock_in',
                '出勤時間もしくは退勤時間が不適切な値です'
            );
        }

        foreach ($breaks as $index => $break) {
            $start = $break['start'] ?? null;
            $end = $break['end'] ?? null;

            if (!$start && !$end) {
                continue;
            }

            if ($start && $end && $start >= $end) {
                $validator->errors()->add(
                    "breaks.$index.start",
                    '休憩時間が不適切な値です'
                );
            }

            if ($start && ($start < $clockIn || $start > $clockOut)) {
                $validator->errors()->add(
                    "breaks.$index.start",
                    '休憩時間が不適切な値です'
                );
            }

            if ($end && ($end < $clockIn || $end > $clockOut)) {
                $validator->errors()->add(
                    "breaks.$index.end",
                    '休憩時間もしくは退勤時間が不適切な値です'
                );
            }
        }
    });
}
}