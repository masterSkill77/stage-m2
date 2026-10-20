<?php

use App\Http\Controllers\API\AdminPackController;
use App\Http\Controllers\API\AuctionController;
use App\Http\Controllers\API\BidController;
use App\Http\Controllers\API\ContactController;
use App\Http\Controllers\API\NftController;
use App\Http\Controllers\API\PackController;
use App\Http\Controllers\API\PaiementController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GatewayController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\SettingsController;
use App\Http\Resources\PublicUserResource;
use App\Services\FriendsService;
use App\Models\User;
use App\Services\TickerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return User::with("pack", "configuration")->where('id', $request->user()->id)->first()->makePrivateVisible();
});

Route::put('/settings', [SettingsController::class, 'update'])->middleware('auth:sanctum');
Route::post('/settings/avatar', [SettingsController::class, 'avatar'])->middleware('auth:sanctum');
Route::post('/upload/nft-image', [UploadController::class, 'nftImage'])->middleware('auth:sanctum');
Route::get('/notifications/summary', [NotificationController::class, 'summary'])->middleware('auth:sanctum');

Route::apiResource('auction', AuctionController::class)->middleware('auth:sanctum')->only(['index', 'show']);
Route::post('auction', [AuctionController::class, 'store'])->middleware(['auth:sanctum', 'canCreateAuction']);
Route::apiResource('nft', NftController::class)->middleware('auth:sanctum')->except(['store']);
Route::post('nft', [NftController::class, 'store'])->middleware(['auth:sanctum', 'canCreateNft']);
// Route::apiResource('bid', BidController::class)->middleware('auth:sanctum')->except(['store']);
Route::post('bid', [BidController::class, 'store'])->middleware(['auth:sanctum', 'canMakeBid']);
Route::get('bid', [BidController::class, 'getBidsForUser'])->middleware(['auth:sanctum']);

// Auth
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
Route::get('verify/{userId}', [AuthController::class, 'verifyMail'])->middleware('signed')->name('verify.email');

Route::prefix('/mine')->middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('nft', [NftController::class, 'mine']);
    Route::get('nft/available', [NftController::class, 'myAvailableNfts']);
    Route::get('/auctions', [AuctionController::class, 'showMyAuctions']);
});



Route::prefix('/paiement')->middleware(['auth:sanctum'])->group(function () {
    Route::post('/', [PaiementController::class, 'paiement']);
    Route::post('/upgrade-pack', [PaiementController::class, 'upgradePack']);
});

// Les packs sont créés côté serveur uniquement (seeder), jamais par un client
Route::get('pack', [PackController::class, 'index']);

// Administration des packs (superadmin uniquement)
Route::prefix('admin')->middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('packs', [AdminPackController::class, 'index']);
    Route::post('packs', [AdminPackController::class, 'store']);
    Route::put('packs/{pack}', [AdminPackController::class, 'update']);
});

Route::prefix('nft')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/category/{idCategory}', [NftController::class, 'getByCategory']);
});

Route::get('news', [NewsController::class, 'index']);

// Gateway : toutes les requêtes vers les microservices passent par Laravel
Route::prefix('chat')->middleware('auth:sanctum')->group(function () {
    Route::any('{path?}', [GatewayController::class, 'chat'])->where('path', '.*');
});

Route::prefix('blockchain')->middleware('auth:sanctum')->group(function () {
    Route::any('{path?}', [GatewayController::class, 'blockchain'])->where('path', '.*');
});


// Appel interne du service chat : vérifie que deux utilisateurs sont amis confirmés
Route::get('internal/friendship/{userId}/{otherId}', function (Request $request, int $userId, int $otherId) {
    $expected = (string) config('services.internal.token');
    abort_unless($expected !== '' && hash_equals($expected, (string) $request->header('X-Internal-Token')), 403);

    return response()->json(['friends' => app(FriendsService::class)->itExist($userId, $otherId)]);
});


Route::get("ticker", function () {

    $tickerService = new TickerService();
    $response = $tickerService->show();
    return response()->json($response);
});

Route::get("ticker/history", function (Request $request) {
    $validated = $request->validate([
        'symbol' => ['required', 'regex:/^[A-Z0-9]{2,20}$/'],
        'interval' => ['required', 'in:1h,4h,1d'],
        'limit' => ['required', 'integer', 'min:2', 'max:200'],
    ]);

    try {
        $candles = (new TickerService())->history($validated['symbol'], $validated['interval'], (int) $validated['limit']);
        return response()->json($candles);
    } catch (\Throwable $e) {
        return response()->json(['message' => 'Historique indisponible'], 502);
    }
});

Route::middleware('auth:sanctum')->prefix('users')->group(function () {
    Route::get("/info/{user}", function (Request $request, User $user) {
        $user->load("configuration");
        $data = (new PublicUserResource($user))->resolve();
        $data['friendship'] = app(FriendsService::class)->friendshipStatus(auth()->id(), $user->id);
        return response()->json($data);
    });

    Route::apiResource("/friends", ContactController::class);
    Route::get("/friend-requests", [ContactController::class, 'requests']);
    Route::post("/friends/{user}/accept", [ContactController::class, 'accept']);
});
