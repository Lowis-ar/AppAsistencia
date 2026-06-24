<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'date',
        'time',
        'latitude',
        'longitude',
        'qr_code',
    ];

    protected $casts = [
        'date' => 'date',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Devuelve un enlace de Google Maps para este registro.
     */
    public function getGoogleMapsUrlAttribute(): ?string
    {
        if ($this->latitude && $this->longitude) {
            return "https://maps.google.com/?q={$this->latitude},{$this->longitude}";
        }
        return null;
    }
}
