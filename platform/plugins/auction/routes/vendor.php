<?php

use Botble\Auction\Http\Controllers\Vendor\AuctionController;
use Botble\Auction\Http\Controllers\Vendor\AuctionAiController;
use Botble\Marketplace\Http\Middleware\LocaleMiddleware;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix' => config('plugins.marketplace.general.vendor_panel_dir', 'vendor'),
    'as' => 'marketplace.vendor.',
    'middleware' => ['web', 'core', 'vendor', LocaleMiddleware::class],
], function (): void {
    Route::prefix('auctions')->name('auctions.')->group(function (): void {
        Route::get('/', [AuctionController::class, 'index'])->name('index');
        Route::get('create', [AuctionController::class, 'create'])->name('create');
        Route::get('statuses', [AuctionController::class, 'statuses'])->name('statuses');
        Route::post('/', [AuctionController::class, 'store'])->name('store');
        Route::prefix('ai')->name('ai.')->middleware('throttle:10,1')->group(function (): void {
            Route::post('suggest-price', [AuctionAiController::class, 'suggestPrice'])->name('suggest-price');
            Route::post('generate-description', [AuctionAiController::class, 'generateDescription'])->name('generate-description');
        });
        Route::get('{auction}/status', [AuctionController::class, 'status'])->name('status')->wherePrimaryKey('auction');
        Route::get('{auction}/edit', [AuctionController::class, 'edit'])->name('edit')->wherePrimaryKey('auction');
        Route::put('{auction}', [AuctionController::class, 'update'])->name('update')->wherePrimaryKey('auction');
        Route::delete('{auction}', [AuctionController::class, 'destroy'])->name('destroy')->wherePrimaryKey('auction');
        Route::get('{auction}/bidders', [AuctionController::class, 'bidders'])->name('bidders')->wherePrimaryKey('auction');
        Route::post('{auction}/winner/{bid}', [AuctionController::class, 'chooseWinner'])->name('choose-winner')->wherePrimaryKey('auction')->wherePrimaryKey('bid');
    });
});
