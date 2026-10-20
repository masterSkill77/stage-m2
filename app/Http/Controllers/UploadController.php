<?php

namespace App\Http\Controllers;

use App\Services\ImageUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UploadController extends Controller
{
    public function __construct(protected ImageUploadService $images)
    {
    }

    // Image d'un NFT : 2 Mo maximum, formats courants uniquement
    public function nftImage(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,webp,gif|max:2048',
        ]);

        return response()->json(['url' => $this->images->store($request->file('image'), 'nfts')]);
    }
}
