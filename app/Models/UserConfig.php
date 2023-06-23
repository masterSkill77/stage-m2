<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserConfig extends Model
{
    use HasFactory;
    protected $fillable = ['profile_image', 'card_number', 'card_expires_month', 'card_expires_year', 'cvc', 'user_id'];
}
