<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\PaymentService;

class PaiementController extends Controller
{
    public function __construct(public PaymentService $paiementService)
    {
    }

    public function paiement(Request $request)
    {
        $paiement = $this->paiementService->paiement(auth()->user());
        return response()->json($paiement);
    }
}
