<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Nft\CreateNftRequest;
use App\Services\NftService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpFoundation\Response;

class NftController extends Controller
{
    public function __construct(protected NftService $nftService)
    {
    }
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->query('perPage');
        $nfts = $this->nftService->lists($perPage);
        return response()->json(['data' => $nfts]);
    }
    public function store(CreateNftRequest $request): JsonResponse | null
    {
        $user = auth()->user();
        $nft = $this->nftService->store($request->toArray(), $user->id);

        return response()->json(['data' => $nft], Response::HTTP_CREATED);
    }
}
