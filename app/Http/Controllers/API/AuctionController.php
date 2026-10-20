<?php

namespace App\Http\Controllers\API;

use App\Exceptions\NotYourNftException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auction\CreateAuctionRequest;
use App\Services\AuctionService;
use App\Services\NftService;
use App\Services\PackForUserService;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AuctionController extends Controller
{
    public function __construct(public AuctionService $auctionService, public NftService $nftService, public PackForUserService $packForUserService)
    {
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = $request->query('perPage');
        $auctions = $this->auctionService->lists($perPage);
        return response()->json(['data' => $auctions]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateAuctionRequest $request)
    {
        $user = auth()->user();
        $nft = $this->nftService->getNft($request->nft_id);
        abort_unless($nft, 404);
        if ($nft->owner_id != $user->id)
            throw new NotYourNftException();
        // Liste blanche : le client ne fixe ni le propriétaire, ni l'offre courante, ni le statut de paiement
        $data = $request->only(['nft_id', 'start_date', 'end_date', 'start_price', 'payment']);
        $data['owner_id'] = $user->id;
        $data['current_bid'] = 0;
        $data['is_paid'] = 0;
        $auction = $this->auctionService->store($data);
        $value = $this->packForUserService->auctioned($user);
        return response()->json(['data' => $auction]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $auction = $this->auctionService->getAuction($id);
        if (!$auction) {
            throw new NotFoundHttpException();
        }
        return response()->json(['data' => $auction]);
    }
    public function showMyAuctions()
    {
        $allAuctions = $this->auctionService->myAuctions(auth()->user()->id);
        return response()->json(['data' => $allAuctions]);
    }

    /**
     * Remove the specified resource from storage.
     */
}
