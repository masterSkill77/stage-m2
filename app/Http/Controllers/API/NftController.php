<?php

namespace App\Http\Controllers\API;

use App\Exceptions\NotYourNftException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Nft\CreateNftRequest;
use App\Models\Nft;
use App\Services\NftService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
        $nft = $this->nftService->store($request->toArray(), $user);

        return response()->json(['data' => $nft], Response::HTTP_CREATED);
    }

    public function update(CreateNftRequest $request, Nft $nft)
    {
        if ($nft->owner_id != auth()->user()->id) throw new NotYourNftException();

        $update = $this->nftService->update($request->toArray(), $nft);

        return response()->json(['data' => $update]);
    }
    public function mine(Request $request)
    {
        $userId = auth()->user()->id;
        $perPage = $request->query('perPage');
        $myNfts = $this->nftService->myNfts($userId, $perPage);
        return response()->json(['data' => $myNfts]);
    }

    public function myAvailableNfts()
    {
        $userId = auth()->user()->id;
        $myNfts = $this->nftService->getMyAvailableNft($userId);
        return response()->json(['data' => $myNfts]);
    }
    public function show(int $nftId)
    {
        $nft = $this->nftService->getNft($nftId);
        if (!$nft) {
            return response()->json(['message' => 'NFT_NO_FOUND'], Response::HTTP_NOT_FOUND);
        }
        return response()->json(['data' => $nft]);
    }

    public function getByCategory(int $idCategory)
    {
        $nfts = $this->nftService->getByCategory($idCategory);
        return response()->json($nfts);
    }
}
