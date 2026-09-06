<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Muscle extends Model
{
    use HasFactory;

    protected $table = 'muscles';
    public $timestamps = false;

    protected $fillable = [
        'name',
    ];

    public function exercises()
    {
        return $this->belongsToMany(Exercise::class, 'exercise_muscles')
            ->withPivot('is_target');
    }
}
