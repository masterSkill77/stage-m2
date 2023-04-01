<?php

use App\Http\Controllers\API\AuctionController;
use App\Http\Controllers\API\NftController;
use App\Http\Controllers\API\TransfertController;
use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
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
        'auction' => AuctionController::class
    ],
    [
        'middleware' => ['auth:sanctum']
    ]
);
Route::post('/login', [AuthController::class, 'login']);

Route::prefix('/mine')->middleware('auth:sanctum')->group(function () {
    Route::get('nft', [NftController::class, 'mine']);
});

Route::prefix('transfert')->middleware('auth:sanctum')->group(function () {
    Route::post('/', [TransfertController::class, 'transfert']);
});
