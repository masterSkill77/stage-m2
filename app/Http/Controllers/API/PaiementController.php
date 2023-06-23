<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Services\PaymentService;

class PaiementController extends Controller
{
    public function __construct(public PaymentService $paiementService)
    {
    }

    public function paiement(Request $request)
    {
        $auction = Auction::findOrFail($request->input('auction_id'));
        $paiement = $this->paiementService->paiement(auth()->user(), $auction);
        return response()->json($paiement);
    }
}
