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
            Route::get('status-feed', [AuctionController::class, 'statusFeed'])->name('status-feed');
            Route::post('notifications/{notification}/read', [AuctionController::class, 'readNotification'])->name('notifications.read')->wherePrimaryKey('notification');
            Route::get('{auction}', [AuctionController::class, 'show'])->name('show')->wherePrimaryKey('auction');
            Route::post('{auction}/bid', [AuctionController::class, 'bid'])->name('bid')->wherePrimaryKey('auction');
        });
});
