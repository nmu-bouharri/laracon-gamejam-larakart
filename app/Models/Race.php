<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Race extends Model
{
    protected $fillable = [
        'name',
        'track_name',
        'status',
        'max_players',
        'current_players',
        'laps',
        'track_data',
        'started_at',
        'finished_at',
        'winner_id'
    ];

    protected $casts = [
        'track_data' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime'
    ];

    public function participants(): HasMany
    {
        return $this->hasMany(RaceParticipant::class);
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'winner_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeWaiting($query)
    {
        return $query->where('status', 'waiting');
    }

    public function canJoin(): bool
    {
        return $this->status === 'waiting' && $this->current_players < $this->max_players;
    }
}
