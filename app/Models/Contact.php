<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'friend_id'];

    public function scopeMyFriend(Builder $query, int $userId): void
    {
        $query->where('friend_id', $userId)->orWhere('user_id', $userId);
    }
    public function user()
    {
        return $this->belongsTo(User::class, "user_id");
    }
    public function friend()
    {
        return $this->belongsTo(User::class, "friend_id");
    }
}
