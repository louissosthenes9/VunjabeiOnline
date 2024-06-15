<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\ProductController;
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
Route::middleware(['auth:sanctum'])->group(
    function () {
        Route::get('/product/{product:slug}', [ProductController::class, 'index']);


        Route::prefix('/cart')->name('cart.')->group(function () {
              Route::get('/', [CartController::class,'index']);
              Route::get('/add/{product:slug}', [CartController::class,'add'])->name('add');
              Route::get('/remove/{product:slug}', [CartController::class,'remove'])->name('remove');
              Route::get('/updated-quantity/{product:slug}', [CartController::class,'updateQuantity'])->name('update-quantity');


        });

        }
);
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
