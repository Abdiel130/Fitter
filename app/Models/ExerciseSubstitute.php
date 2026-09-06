<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExerciseSubstitute extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'exercise_substitutes';
    public $timestamps = false;

    protected $fillable = [
        'exercise_id',
        'substitute_exercise_id',
        'notes',
    ];

    public function exercise()
    {
        return $this->belongsTo(Exercise::class, 'exercise_id');
    }

    public function substituteExercise()
    {
        return $this->belongsTo(Exercise::class, 'substitute_exercise_id');
    }
}
