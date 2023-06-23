<?php

use App\Http\Controllers\API\AuctionController;
use App\Http\Controllers\API\BidController;
use App\Http\Controllers\API\FormController;
use App\Http\Controllers\API\NftController;
use App\Http\Controllers\API\PackController;
use App\Http\Controllers\API\PaiementController;
use App\Http\Controllers\API\TransfertController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\NewsController;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
    return $request->user();
});

Route::apiResource('form', FormController::class);
Route::put('/profile/{id}', [AuthController::class, 'updateProfile'])->middleware('auth:sanctum');


Route::apiResources(
    [
        'nft' => NftController::class,
        'auction' => AuctionController::class,
        'bid' => BidController::class,
    ],
    [
        'middleware' => ['auth:sanctum']
    ]
);

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
});

Route::apiResource('pack', PackController::class);

Route::prefix('nft')->middleware(['auth:sanctum'])->group(function () {
    Route::get('/category/{idCategory}', [NftController::class, 'getByCategory']);
});

Route::get('news', [NewsController::class, 'index']);


Route::get('/check-exists/{emailOrUsername}', function ($emailOrUsername) {
    $exists =  User::where('email', $emailOrUsername)->orWhere('username', $emailOrUsername)->get();
    return count($exists) != 0 ? 1 : 0;
});


Route::get("ticker", function () {
    $response = Http::get('https://api.binance.com/api/v3/ticker/24hr');
    $array_response = array_slice([...$response->json()], 0, 10);

    return $array_response;
});
