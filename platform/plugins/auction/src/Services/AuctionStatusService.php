<?php

namespace Botble\Auction\Services;

use Botble\Auction\Models\Auction;
use Carbon\Carbon;

class AuctionStatusService
{
    public function __construct(protected AuctionNotificationService $notificationService)
    {
    }

    public function syncStatuses(): array
    {
        $now = Carbon::now(config('app.timezone'));

        $publishedCount = 0;

        Auction::query()
            ->where('status', 'scheduled')
            ->whereNotNull('start_time')
            ->where('start_time', '<=', $now)
            ->where('end_time', '>', $now)
            ->chunkById(50, function ($auctions) use (&$publishedCount): void {
                foreach ($auctions as $auction) {
                    $auction->update(['status' => 'published']);
                    $this->notificationService->notifyAuctionLive($auction);
                    $publishedCount++;
                }
            });

        $closedCount = 0;

        Auction::query()
            ->whereIn('status', ['scheduled', 'published'])
            ->where('end_time', '<=', $now)
            ->whereHas('bids')
            ->chunkById(50, function ($auctions) use (&$closedCount): void {
                foreach ($auctions as $auction) {
                    $auction->update(['status' => 'closed']);
                    $this->notificationService->notifyAuctionClosed($auction);
                    $closedCount++;
                }
            });

        Auction::query()
            ->whereIn('status', ['scheduled', 'published'])
            ->where('end_time', '<=', $now)
            ->whereDoesntHave('bids')
            ->chunkById(50, function ($auctions) use (&$closedCount): void {
                foreach ($auctions as $auction) {
                    $auction->update(['status' => 'closed']);
                    $closedCount++;
                }
            });

        return [
            'published' => $publishedCount,
            'closed' => $closedCount,
        ];
    }
}
