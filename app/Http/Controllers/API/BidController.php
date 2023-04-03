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
        $bid = $this->bidService->makeBid($bid->toArray());

        return response()->json(['data' => $bid]);
    }
}
