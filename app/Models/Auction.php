<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Auction extends Model
{
    use HasFactory;
    protected $fillable = ['end_date', 'start_date', 'current_bid', 'start_price', 'owner_id', 'nft_id'];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function nft(): BelongsTo
    {
        return $this->belongsTo(Nft::class, 'nft_id');
    }
}
