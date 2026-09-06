<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyFoodLog extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'daily_food_logs';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'food_item_id',
        'recipe_id',
        'log_date',
        'meal_type',
        'amount_in_grams',
        'calculated_calories',
        'calculated_protein',
        'calculated_carbs',
        'calculated_fat',
    ];

    protected $casts = [
        'log_date' => 'date',
        'amount_in_grams' => 'float',
        'calculated_calories' => 'float',
        'calculated_protein' => 'float',
        'calculated_carbs' => 'float',
        'calculated_fat' => 'float',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function foodItem()
    {
        return $this->belongsTo(FoodItem::class);
    }

    public function recipe()
    {
        return $this->belongsTo(Recipe::class);
    }
}
