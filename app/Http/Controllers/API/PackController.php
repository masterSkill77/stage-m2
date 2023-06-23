<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreatePackRequest;
use App\Services\PackService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PackController extends Controller
{
    public function __construct(public PackService $packService)
    {
    }
    public function index()
    {
        return response()->json($this->packService->listsOfPack(), Response::HTTP_OK);
    }
    public function store(CreatePackRequest $request)
    {
        $pack = $this->packService->store($request->toArray());
        return response()->json($pack, Response::HTTP_CREATED);
    }
}
