<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PhpDeveloper extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'bio',
        'avatar_url',
        'special_abilities',
        'popularity_rating',
        'is_featured',
        'is_locked',
        'unlock_order'
    ];

    protected $casts = [
        'special_abilities' => 'array',
        'is_featured' => 'boolean',
        'is_locked' => 'boolean',
        'unlock_order' => 'integer'
    ];

    public function raceParticipants(): HasMany
    {
        return $this->hasMany(RaceParticipant::class);
    }

    public function getSpeedBoostAttribute(): int
    {
        return $this->special_abilities['speed_boost'] ?? 0;
    }

    public function getHandlingBoostAttribute(): int
    {
        return $this->special_abilities['handling_boost'] ?? 0;
    }

    public function getAccelerationBoostAttribute(): int
    {
        return $this->special_abilities['acceleration_boost'] ?? 0;
    }
}
