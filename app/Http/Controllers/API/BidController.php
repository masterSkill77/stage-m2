<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateBidRequest;
use App\Services\BidService;
use Illuminate\Http\Request;

class BidController extends Controller
{
    public function __construct(public BidService $bidService)
    {
    }
    public function store(CreateBidRequest $bid)
    {
        // L'enchérisseur est toujours l'utilisateur connecté, jamais une valeur envoyée par le client
        $bid = $this->bidService->makeBid(array_merge($bid->toArray(), ['bidder_id' => auth()->id()]));

        return response()->json(['data' => $bid]);
    }
    public function getBidsForUser()
    {
        $user = auth()->user();

        $bids = $this->bidService->getAllBidForUser($user->id);
        return response()->json($bids);
    }
}
