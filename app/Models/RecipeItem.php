<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecipeItem extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'recipe_items';
    public $timestamps = false;

    protected $fillable = [
        'recipe_id',
        'food_item_id',
        'amount_in_grams',
    ];

    protected $casts = [
        'amount_in_grams' => 'float',
    ];

    public function recipe()
    {
        return $this->belongsTo(Recipe::class);
    }

    public function foodItem()
    {
        return $this->belongsTo(FoodItem::class);
    }
}
