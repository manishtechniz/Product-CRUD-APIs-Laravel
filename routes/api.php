<?php

use App\Http\Controllers\API\ProductController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'products'], function () {
    Route::get('/', [ProductController::class, 'index']);

    Route::get('/{id}', [ProductController::class, 'product']);

    Route::delete('/{id}', [ProductController::class, 'destroy']);

    Route::post('/', [ProductController::class, 'store']);
});


