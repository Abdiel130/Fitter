<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplement extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'supplements';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'name',
        'target_dosage',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function dailyLogs()
    {
        return $this->hasMany(DailySupplementLog::class);
    }
}
