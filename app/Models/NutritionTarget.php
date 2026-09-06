<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NutritionTarget extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'nutrition_targets';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'name',
        'calories_kcal',
        'protein_g',
        'carbs_g',
        'fat_g',
        'is_default',
    ];

    protected $casts = [
        'calories_kcal' => 'float',
        'protein_g' => 'float',
        'carbs_g' => 'float',
        'fat_g' => 'float',
        'is_default' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
