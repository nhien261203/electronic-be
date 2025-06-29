<?php

use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProductImageController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Intervention\Image\Facades\Image;


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
Route::get('/hello', function () {
    return response()->json(['message' => 'Hello từ Backend Laravel!']);
});

// Thêm route để lấy danh sách các quốc gia
Route::get('/brands/countries', [BrandController::class, 'getCountries']);
Route::apiResource('brands', BrandController::class);

Route::apiResource('categories', CategoryController::class);

Route::apiResource('products', ProductController::class);

Route::prefix('product-images')->group(function () {
    Route::get('/product/{productId}', [ProductImageController::class, 'getByProduct']);
    Route::post('/product/{productId}', [ProductImageController::class, 'store']);
    Route::put('/{id}/thumbnail', [ProductImageController::class, 'setThumbnail']);
    Route::delete('/{id}', [ProductImageController::class, 'destroy']);
});



