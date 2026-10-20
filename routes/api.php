<?php

use App\Http\Controllers\API\AuctionController;
use App\Http\Controllers\API\BidController;
use App\Http\Controllers\API\ContactController;
use App\Http\Controllers\API\FormController;
use App\Http\Controllers\API\NftController;
use App\Http\Controllers\API\PackController;
use App\Http\Controllers\API\PaiementController;
use App\Http\Controllers\API\TransfertController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GatewayController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\SettingsController;
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
    return User::with("pack", "configuration")->where('id', $request->user()->id)->first();
});

Route::apiResource('form', FormController::class);
Route::put('/profile/{id}', [AuthController::class, 'updateProfile'])->middleware('auth:sanctum');
Route::put('/settings', [SettingsController::class, 'update'])->middleware('auth:sanctum');

Route::apiResource('auction', AuctionController::class)->middleware('auth:sanctum')->except(['store']);
Route::post('auction', [AuctionController::class, 'store'])->middleware(['auth:sanctum', 'canCreateAuction']);
Route::apiResource('nft', NftController::class)->middleware('auth:sanctum')->except(['store']);
Route::post('nft', [NftController::class, 'store'])->middleware(['auth:sanctum', 'canCreateNft']);
// Route::apiResource('bid', BidController::class)->middleware('auth:sanctum')->except(['store']);
Route::post('bid', [BidController::class, 'store'])->middleware(['auth:sanctum', 'canMakeBid']);
Route::get('bid', [BidController::class, 'getBidsForUser'])->middleware(['auth:sanctum']);

// Auth
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::get('verify/{userId}/{token}', [AuthController::class, 'verifyMail']);

Route::prefix('/mine')->middleware(['auth:sanctum', 'verified'])->group(function () {
    Route::get('nft', [NftController::class, 'mine']);
    Route::get('nft/available', [NftController::class, 'myAvailableNfts']);
    Route::get('/auctions', [AuctionController::class, 'showMyAuctions']);
});

Route::prefix('transfert')->middleware('auth:sanctum')->group(function () {
    Route::post('/', [TransfertController::class, 'transfert']);
});


Route::prefix('/paiement')->middleware(['auth:sanctum'])->group(function () {
    Route::post('/', [PaiementController::class, 'paiement']);
    Route::post('/upgrade-pack', [PaiementController::class, 'upgradePack']);
});

Route::apiResource('pack', PackController::class);

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


Route::get('/check-exists/{emailOrUsername}', function ($emailOrUsername) {
    $exists =  User::where('email', $emailOrUsername)->orWhere('username', $emailOrUsername)->get();
    return count($exists) != 0 ? 1 : 0;
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
        $user = User::with("configuration")->where("id", $user->id)->first();
        return response()->json($user);
    });

    Route::apiResource("/friends", ContactController::class);
    Route::post("/notation", []);
});
