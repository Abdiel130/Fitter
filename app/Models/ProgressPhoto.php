<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgressPhoto extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'progress_photos';
    public $timestamps = false;

    protected $fillable = [
        'measurement_id',
        'pose_type',
        'photo_path',
        'taken_at',
    ];

    protected $casts = [
        'taken_at' => 'datetime',
    ];

    public function bodyMeasurement()
    {
        return $this->belongsTo(BodyMeasurement::class, 'measurement_id');
    }
}
