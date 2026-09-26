<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasUuids, Notifiable;

    protected $table = 'users';
    public $timestamps = false;

    protected $fillable = [
        'name',
        'email',
        'password',
        'current_routine',
        'active_sequence_index',
        'setup_completed_at',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'active_sequence_index' => 'integer',
        'setup_completed_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    /**
     * El asistente de configuración inicial se considera completado cuando existe setup_completed_at.
     */
    public function isSetupCompleted(): bool
    {
        return $this->setup_completed_at !== null;
    }

    /**
     * Datos básicos del perfil expuestos al cliente (login, /auth/me, /profile).
     */
    public function toProfileArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'current_routine' => $this->current_routine,
            'active_sequence_index' => $this->active_sequence_index,
            'is_setup_completed' => $this->isSetupCompleted(),
            'setup_completed_at' => $this->setup_completed_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }

    public function currentRoutine()
    {
        return $this->belongsTo(Routine::class, 'current_routine');
    }

    public function routines()
    {
        return $this->hasMany(Routine::class);
    }

    public function workouts()
    {
        return $this->hasMany(Workout::class);
    }

    public function workoutLogs()
    {
        return $this->hasMany(WorkoutLog::class);
    }

    public function bodyMeasurements()
    {
        return $this->hasMany(BodyMeasurement::class);
    }

    public function dailyHabitLogs()
    {
        return $this->hasMany(DailyHabitLog::class);
    }

    public function nutritionTargets()
    {
        return $this->hasMany(NutritionTarget::class);
    }

    public function foodItems()
    {
        return $this->hasMany(FoodItem::class);
    }

    public function recipes()
    {
        return $this->hasMany(Recipe::class);
    }

    public function dailyFoodLogs()
    {
        return $this->hasMany(DailyFoodLog::class);
    }

    public function supplements()
    {
        return $this->hasMany(Supplement::class);
    }
}
