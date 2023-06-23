<?php

namespace App\Http\Middleware;

use App\Services\PackForUserService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CanBid
{
    public function __construct(protected PackForUserService $packForUserService)
    {
    }
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        if ($this->packForUserService->isAllowedToBid($user))
            return $next($request);
        return response()->json(['error' => 'BID_NOT_ALLOWED'], Response::HTTP_FORBIDDEN);
    }
}
