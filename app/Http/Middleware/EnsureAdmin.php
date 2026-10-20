<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

// Réservé aux comptes administrateurs (colonne users.is_admin)
class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user()?->is_admin) {
            return response()->json(['error' => 'ADMIN_ONLY'], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
