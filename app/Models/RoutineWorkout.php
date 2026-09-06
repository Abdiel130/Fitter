<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoutineWorkout extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'routine_workouts';
    public $timestamps = false;

    protected $fillable = [
        'routine_id',
        'workout_id',
        'order_index',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function routine()
    {
        return $this->belongsTo(Routine::class);
    }

    public function workout()
    {
        return $this->belongsTo(Workout::class);
    }
}
