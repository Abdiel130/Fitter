<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyHabitLog extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'daily_habit_logs';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'log_date',
        'sleep_hours',
        'sleep_quality',
        'energy_level',
        'water_intake_ml',
        'water_target_ml',
    ];

    protected $casts = [
        'log_date' => 'date',
        'sleep_hours' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function supplementLogs()
    {
        return $this->hasMany(DailySupplementLog::class, 'daily_habit_log_id');
    }

    public function jointDiscomfortLogs()
    {
        return $this->hasMany(JointDiscomfortLog::class, 'daily_habit_log_id');
    }
}
