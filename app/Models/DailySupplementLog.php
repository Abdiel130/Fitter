<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailySupplementLog extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'daily_supplement_logs';
    public $timestamps = false;

    protected $fillable = [
        'daily_habit_log_id',
        'supplement_id',
        'is_taken',
        'taken_at',
    ];

    protected $casts = [
        'is_taken' => 'boolean',
        'taken_at' => 'datetime',
    ];

    public function dailyHabitLog()
    {
        return $this->belongsTo(DailyHabitLog::class);
    }

    public function supplement()
    {
        return $this->belongsTo(Supplement::class);
    }
}
