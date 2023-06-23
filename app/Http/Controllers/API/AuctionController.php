<?php

namespace App\Http\Controllers\API;

use App\Exceptions\NotYourNftException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auction\CreateAuctionRequest;
use App\Services\AuctionService;
use App\Services\NftService;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AuctionController extends Controller
{
    public function __construct(public AuctionService $auctionService, public NftService $nftService)
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
        if ($nft->owner_id != $user->id)
            throw new NotYourNftException();
        $request['owner_id'] = $user->id;
        $request['current_bid'] = $request['current_bid'] ?? 0;
        $auction = $this->auctionService->store($request->toArray());

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
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
