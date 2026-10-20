<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GatewayController extends Controller
{
    public function chat(Request $request, string $path = '')
    {
        $this->authorizeChat($request, $path);

        return $this->forward($request, config('services.chat.url'), $path);
    }

    public function blockchain(Request $request, string $path = '')
    {
        $this->authorizeBlockchain($request, $path);

        return $this->forward($request, config('services.blockchain.url'), $path);
    }

    /**
     * Liste blanche des routes du chat. Toute autre route est refusée.
     *  - POST /chat        : envoi d'un message, l'expéditeur doit être l'utilisateur connecté
     *  - GET  /chat/{me}/{user} : historique, {me} doit être l'utilisateur connecté
     */
    private function authorizeChat(Request $request, string $path): void
    {
        $me = (string) $request->user()->id;
        $segments = $this->segments($path);

        if ($request->isMethod('POST') && $segments === []) {
            abort_unless((string) $request->input('from') === $me, 403, 'Expéditeur non autorisé.');

            return;
        }

        if ($request->isMethod('GET') && count($segments) === 2) {
            abort_unless($segments[0] === $me, 403, 'Accès à cette conversation refusé.');

            return;
        }

        abort(404);
    }

    /**
     * Liste blanche des routes blockchain. Un utilisateur n'agit que sur son adresse.
     *  - GET  /blockchain/me/{address}        : solde de sa propre adresse
     *  - GET  /blockchain/nft/{id}            : lecture d'un NFT
     *  - POST /blockchain/nft                 : création, owner = son adresse
     *  - POST /blockchain/nft/transfer        : transfert, from = son adresse
     *  - POST /blockchain/nft/pay-with-eth    : paiement, from = son adresse
     */
    private function authorizeBlockchain(Request $request, string $path): void
    {
        $own = strtolower((string) $request->user()->etherum_adress);
        $segments = $this->segments($path);
        $method = $request->method();

        if ($method === 'GET' && count($segments) === 2 && $segments[0] === 'me') {
            abort_unless(strtolower($segments[1]) === $own, 403, 'Adresse non autorisée.');

            return;
        }

        if ($method === 'GET' && count($segments) === 2 && $segments[0] === 'nft') {
            return;
        }

        if ($method === 'POST' && $segments === ['nft']) {
            $this->ensureAddress($request->input('owner'), $own);

            return;
        }

        if ($method === 'POST' && in_array($segments, [['nft', 'transfer'], ['nft', 'pay-with-eth']], true)) {
            $this->ensureAddress($request->input('from'), $own);

            return;
        }

        abort(404);
    }

    private function ensureAddress($address, string $own): void
    {
        abort_unless(is_string($address) && strtolower($address) === $own, 403, 'Adresse non autorisée.');
    }

    private function segments(string $path): array
    {
        return array_values(array_filter(explode('/', $path), fn ($segment) => $segment !== ''));
    }

    /**
     * Relaie la requête vers un microservice en ajoutant le secret interne
     * et l'identité de l'utilisateur authentifié.
     */
    private function forward(Request $request, string $baseUrl, string $path)
    {
        $options = $request->isMethod('GET')
            ? ['query' => $request->query()]
            : ['json' => $request->all()];

        /** @var Response $response */
        $response = Http::withHeaders([
            'X-Internal-Token' => config('services.internal.token'),
            'X-User-Id' => (string) $request->user()->id,
            'Accept' => 'application/json',
        ])->send($request->method(), rtrim($baseUrl, '/') . '/' . ltrim($path, '/'), $options);

        return response($response->body(), $response->status())
            ->header('Content-Type', $response->header('Content-Type', 'application/json'));
    }
}
