<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Pack;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

// Gestion des packs par les administrateurs. Pas de suppression : un pack désactivé garde son historique.
class AdminPackController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Pack::orderBy('pack_price')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $pack = Pack::create($this->validated($request));

        return response()->json($pack, Response::HTTP_CREATED);
    }

    public function update(Request $request, Pack $pack): JsonResponse
    {
        $pack->update($this->validated($request, $pack));

        return response()->json($pack);
    }

    private function validated(Request $request, ?Pack $pack = null): array
    {
        return $request->validate([
            'pack_name' => ['required', 'string', 'max:50', Rule::unique('packs', 'pack_name')->ignore($pack?->id)],
            'pack_price' => ['required', 'integer', 'min:1', 'max:100000'],
            'pack_max_nft_creation' => ['required', 'integer', 'min:0'],
            'pack_max_auction_creation' => ['required', 'integer', 'min:0'],
            'pack_max_bid' => ['required', 'integer', 'min:0'],
            'is_active' => ['required', 'boolean'],
        ]);
    }
}
