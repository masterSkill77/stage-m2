<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\NftService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class NftController extends Controller
{
    public function __construct(protected NftService $nftService)
    {
    }
    public function index(Request $request)
    {
        $perPage = $request->query('perPage');
        $nfts = $this->nftService->lists($perPage);

        return response()->json(['data' => $nfts]);
    }
}
