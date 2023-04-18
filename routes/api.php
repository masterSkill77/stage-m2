<?php

use App\Http\Controllers\API\AuctionController;
use App\Http\Controllers\API\BidController;
use App\Http\Controllers\API\NftController;
use App\Http\Controllers\API\TransfertController;
use App\Http\Controllers\AuthController;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

Route::apiResources(
    [
        'nft' => NftController::class,
        'auction' => AuctionController::class,
        'bid' => BidController::class
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
});

Route::prefix('transfert')->middleware('auth:sanctum')->group(function () {
    Route::post('/', [TransfertController::class, 'transfert']);
});


Route::get('/test-paypal', function () {
    $paymentService = new PaymentService();
    return $paymentService->test();
});

Route::prefix('nft')->group(function () {
    Route::get('/category/{idCategory}', [NftController::class, 'getByCategory']);
})->middleware(['auth:sanctum']);
