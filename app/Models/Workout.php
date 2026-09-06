<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Workout extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'workout';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'name',
        'notes',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function workoutExercises()
    {
        return $this->hasMany(WorkoutExercise::class, 'workout_id')->orderBy('order_in_routine');
    }
}
