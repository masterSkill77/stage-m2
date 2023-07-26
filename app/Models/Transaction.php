<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    const AUCTION_PAYMENT = "auction_paiment";
    const PACK_PAYMENT = "pack_paiment";
    use HasFactory;
    public $fillable =  [
        'transaction_type',
        'transaction_uuid',
        'transaction_group',
        'type_id',
        'user_id'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
