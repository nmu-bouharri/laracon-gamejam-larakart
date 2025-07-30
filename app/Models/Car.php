<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Car extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'brand',
        'model',
        'description',
        'image_url',
        'speed_rating',
        'acceleration_rating',
        'handling_rating',
        'is_lambo',
        'is_premium',
        'unlock_level'
    ];

    protected $casts = [
        'is_lambo' => 'boolean',
        'is_premium' => 'boolean'
    ];

    public function raceParticipants(): HasMany
    {
        return $this->hasMany(RaceParticipant::class);
    }

    public function scopeLambos($query)
    {
        return $query->where('is_lambo', true);
    }

    public function scopePremium($query)
    {
        return $query->where('is_premium', true);
    }

    public function getOverallRatingAttribute(): float
    {
        return ($this->speed_rating + $this->acceleration_rating + $this->handling_rating) / 3;
    }
}
