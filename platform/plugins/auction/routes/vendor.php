<?php

use Botble\Auction\Http\Controllers\Vendor\AuctionController;
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
        Route::post('/', [AuctionController::class, 'store'])->name('store');
        Route::get('{auction}/edit', [AuctionController::class, 'edit'])->name('edit')->wherePrimaryKey('auction');
        Route::put('{auction}', [AuctionController::class, 'update'])->name('update')->wherePrimaryKey('auction');
        Route::delete('{auction}', [AuctionController::class, 'destroy'])->name('destroy')->wherePrimaryKey('auction');
        Route::get('{auction}/bidders', [AuctionController::class, 'bidders'])->name('bidders')->wherePrimaryKey('auction');
        Route::post('{auction}/winner/{bid}', [AuctionController::class, 'chooseWinner'])->name('choose-winner')->wherePrimaryKey('auction')->wherePrimaryKey('bid');
    });
});
