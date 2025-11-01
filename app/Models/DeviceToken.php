<?php

namespace App\Models;

use App\Enums\DevicePlatform;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeviceToken extends Model
{
    /** @use HasFactory<\Database\Factories\DeviceTokensFactory> */
    use HasFactory;


    protected function casts(): array
    {
        return [
            'platform' => DevicePlatform::class,
            'laset_seen_at' => 'datetime',
        ];
    }


    protected $fillable = [
        'user_id',
        'token',
        'platform',
        'last_seen_at',
    ];


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function notificationLogs(): HasMany
    {
        return $this->hasMany(NotificationLog::class);
    }
}
