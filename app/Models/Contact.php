<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'friend_id', 'accepted_at'];

    protected $casts = [
        'accepted_at' => 'datetime',
    ];

    // Contacts où l'utilisateur est impliqué, dans un sens ou dans l'autre (les parenthèses évitent les OU mal groupés)
    public function scopeMyFriend(Builder $query, int $userId): void
    {
        $query->where(function (Builder $query) use ($userId) {
            $query->where('friend_id', $userId)->orWhere('user_id', $userId);
        });
    }

    public function scopeAccepted(Builder $query): void
    {
        $query->whereNotNull('accepted_at');
    }

    public function scopeBetweenUsers(Builder $query, int $first, int $second): void
    {
        $query->where(function (Builder $query) use ($first, $second) {
            $query->where(function (Builder $query) use ($first, $second) {
                $query->where('user_id', $first)->where('friend_id', $second);
            })->orWhere(function (Builder $query) use ($first, $second) {
                $query->where('user_id', $second)->where('friend_id', $first);
            });
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, "user_id");
    }

    public function friend(): BelongsTo
    {
        return $this->belongsTo(User::class, "friend_id");
    }
}
