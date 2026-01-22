<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('pos.index');
});

Route::resource('products', ProductController::class);

Route::get('/pos', [TransactionController::class, 'index'])->name('pos.index');
Route::get('/pos/search', [TransactionController::class, 'search'])->name('pos.search');
Route::post('/pos/store', [TransactionController::class, 'store'])->name('pos.store');
Route::get('/transactions/{transaction}/print', [TransactionController::class, 'print'])->name('pos.print');
Route::post('/pos/products/quick-store', [ProductController::class, 'quickStore'])->name('pos.quickStore');



