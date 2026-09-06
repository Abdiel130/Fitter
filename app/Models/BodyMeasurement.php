<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BodyMeasurement extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'body_measurement';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'log_date',
        'weight_kg',
        'body_fat_percentage',
        'neck_cm',
        'chest_cm',
        'waist_navel_cm',
        'hips_cm',
        'bicep_left_cm',
        'bicep_right_cm',
        'thigh_left_cm',
        'thigh_right_cm',
        'calf_cm',
        'notes',
    ];

    protected $casts = [
        'log_date' => 'date',
        'weight_kg' => 'float',
        'body_fat_percentage' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function progressPhotos()
    {
        return $this->hasMany(ProgressPhoto::class, 'measurement_id');
    }
}
