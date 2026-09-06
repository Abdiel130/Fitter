<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkoutSet extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'workout_sets';
    public $timestamps = false;

    protected $fillable = [
        'work_exercise_id',
        'set_order',
        'type',
        'range_rep_ini',
        'range_rep_end',
        'target_rpe',
        'rest_second',
    ];

    protected $casts = [
        'target_rpe' => 'float',
    ];

    public function workoutExercise()
    {
        return $this->belongsTo(WorkoutExercise::class, 'work_exercise_id');
    }
}
