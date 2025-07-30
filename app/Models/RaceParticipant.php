<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RaceParticipant extends Model
{
    protected $fillable = [
        'race_id',
        'user_id',
        'php_developer_id',
        'car_id',
        'position',
        'current_lap',
        'lap_times',
        'position_data',
        'finished',
        'finish_time'
    ];

    protected $casts = [
        'lap_times' => 'array',
        'position_data' => 'array',
        'finished' => 'boolean',
        'finish_time' => 'datetime'
    ];

    public function race(): BelongsTo
    {
        return $this->belongsTo(Race::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function phpDeveloper(): BelongsTo
    {
        return $this->belongsTo(PhpDeveloper::class);
    }

    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class);
    }

    public function getBestLapTimeAttribute(): ?float
    {
        return $this->lap_times ? min($this->lap_times) : null;
    }

    public function getTotalRaceTimeAttribute(): ?float
    {
        return $this->lap_times ? array_sum($this->lap_times) : null;
    }
}
