<?php

namespace App\Http\Controllers;

use App\Models\Auction;
use App\Models\Bid;
use App\Services\FriendsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(protected FriendsService $friends)
    {
    }

    // Compteurs affichés dans le menu : demandes d'ami reçues et enchères gagnées non payées
    public function summary(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        $won = Auction::where('is_paid', 0)
            ->whereNotNull('end_date')
            ->where('end_date', '<=', now())
            ->whereIn('id', Bid::where('bidder_id', $userId)->select('auction_id'))
            ->get()
            ->filter(function (Auction $auction) use ($userId) {
                $top = Bid::where('auction_id', $auction->id)->orderByDesc('bid_amount')->first();
                return $top && (int) $top->bidder_id === (int) $userId;
            })
            ->count();

        return response()->json([
            'friend_requests' => count($this->friends->incomingRequests($userId)),
            'won_unpaid' => $won,
        ]);
    }
}
