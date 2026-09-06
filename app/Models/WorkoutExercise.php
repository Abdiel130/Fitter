<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkoutExercise extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'workout_exercises';
    public $timestamps = false;

    protected $fillable = [
        'workout_id',
        'exercise_id',
        'order_in_routine',
        'rest_seconds',
        'notes',
    ];

    public function workout()
    {
        return $this->belongsTo(Workout::class);
    }

    public function exercise()
    {
        return $this->belongsTo(Exercise::class);
    }

    public function sets()
    {
        return $this->hasMany(WorkoutSet::class, 'work_exercise_id')->orderBy('set_order');
    }
}
