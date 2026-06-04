<?php

use Botble\Auction\Http\Controllers\Customer\AuctionController;
use Botble\Theme\Facades\Theme;
use Illuminate\Support\Facades\Route;

Theme::registerRoutes(function (): void {
    Route::middleware(['web', 'core', 'customer'])
        ->prefix('customer/auctions')
        ->name('auction.customer.')
        ->group(function (): void {
            Route::get('/', [AuctionController::class, 'index'])->name('index');
            Route::get('{auction}', [AuctionController::class, 'show'])->name('show')->wherePrimaryKey('auction');
            Route::post('{auction}/bid', [AuctionController::class, 'bid'])->name('bid')->wherePrimaryKey('auction');
        });
});
