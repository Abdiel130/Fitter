<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JointDiscomfortLog extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'joint_discomfort_logs';
    public $timestamps = false;

    protected $fillable = [
        'daily_habit_log_id',
        'joint_area',
        'pain_intensity',
        'notes',
    ];

    public function dailyHabitLog()
    {
        return $this->belongsTo(DailyHabitLog::class);
    }
}
