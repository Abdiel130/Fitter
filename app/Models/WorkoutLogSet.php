<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkoutLogSet extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'workout_log_sets';
    public $timestamps = false;

    protected $fillable = [
        'workout_log_id',
        'exercise_id',
        'set_order',
        'set_type',
        'weight_input',
        'weight_unit',
        'weight_kg',
        'reps_completed',
        'rpe',
        'calculated_1rm',
        'is_personal_record',
    ];

    protected $casts = [
        'weight_input' => 'float',
        'weight_kg' => 'float',
        'rpe' => 'float',
        'calculated_1rm' => 'float',
        'is_personal_record' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function workoutLog()
    {
        return $this->belongsTo(WorkoutLog::class);
    }

    public function exercise()
    {
        return $this->belongsTo(Exercise::class);
    }
}
