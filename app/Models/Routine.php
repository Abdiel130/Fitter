<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Routine extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'routine';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function routineWorkouts()
    {
        return $this->hasMany(RoutineWorkout::class, 'routine_id')->orderBy('order_index');
    }
}
