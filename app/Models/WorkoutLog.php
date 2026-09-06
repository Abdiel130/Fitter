<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkoutLog extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'workout_logs';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'workout_id',
        'title',
        'started_at',
        'finished_at',
        'overall_rpe',
        'notes',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function workout()
    {
        return $this->belongsTo(Workout::class);
    }

    public function sets()
    {
        return $this->hasMany(WorkoutLogSet::class, 'workout_log_id')->orderBy('set_order');
    }
}
