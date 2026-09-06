<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FoodItem extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'food_items';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'barcode',
        'name',
        'brand',
        'serving_size_g',
        'calories_per_100g',
        'protein_per_100g',
        'carbs_per_100g',
        'fat_per_100g',
        'fiber_per_100g',
    ];

    protected $casts = [
        'serving_size_g' => 'float',
        'calories_per_100g' => 'float',
        'protein_per_100g' => 'float',
        'carbs_per_100g' => 'float',
        'fat_per_100g' => 'float',
        'fiber_per_100g' => 'float',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function recipeItems()
    {
        return $this->hasMany(RecipeItem::class);
    }
}
