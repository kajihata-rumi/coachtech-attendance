<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BreakCorrectionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'attendance_correction_request_id',
        'break_time_id',
        'requested_break_start',
        'requested_break_end',
    ];

    public function attendanceCorrectionRequest()
    {
        return $this->belongsTo(AttendanceCorrectionRequest::class);
    }

    public function breakTime()
    {
        return $this->belongsTo(BreakTime::class);
    }
}

