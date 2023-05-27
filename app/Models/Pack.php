<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pack extends Model
{
    use HasFactory;

    protected $fillable = ['pack_name', 'pack_price', 'pack_max_nft_creation', 'pack_max_auction_creation', 'pack_max_bid'];
}
