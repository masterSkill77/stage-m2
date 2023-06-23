<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Nft\TransfertNftRequest;
use App\Models\Nft;
use App\Models\User;
use Illuminate\Http\Request;

class TransfertController extends Controller
{
    public function transfert(TransfertNftRequest $request)
    {
        
        return response()->json();
    }
}
