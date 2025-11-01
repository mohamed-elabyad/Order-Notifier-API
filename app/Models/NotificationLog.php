<?php

namespace App\Models;

use App\Enums\NotificationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationLog extends Model
{
    /** @use HasFactory<\Database\Factories\NotificationLogFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_id',
        'device_token_id',
        'payload',
        'response',
        'notification_status',
        'send_at',
    ];

    public function deviceToken():BelongsTo
    {
        return $this->belongsTo(DeviceToken::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }


    protected function casts(): array
    {
        return [
            'NotificationStatus' => NotificationStatus::class,
            'payload' => 'array',
            'response' => 'array',
        ];
    }
}
