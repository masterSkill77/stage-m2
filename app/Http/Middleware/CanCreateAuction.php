<?php

namespace App\Http\Middleware;

use App\Services\PackForUserService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CanCreateAuction
{
    public function __construct(protected PackForUserService $packForUserService)
    {
    }
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        if ($this->packForUserService->isAllowedToCreateAuction($user))
            return $next($request);
        return response()->json(['error' => 'CREATE_AUCTION_NOT_ALLOWED'], Response::HTTP_FORBIDDEN);
    }
}
