<?php

namespace Botble\Auction\Services;

use Botble\Auction\Models\Auction;
use Carbon\Carbon;

class AuctionStatusService
{
    public function syncStatuses(): array
    {
        $now = Carbon::now(config('app.timezone'));

        $publishedCount = Auction::query()
            ->where('status', 'scheduled')
            ->whereNotNull('start_time')
            ->where('start_time', '<=', $now)
            ->where('end_time', '>', $now)
            ->update(['status' => 'published']);

        $closedCount = 0;

        Auction::query()
            ->whereIn('status', ['scheduled', 'published'])
            ->where('end_time', '<=', $now)
            ->whereHas('bids')
            ->chunkById(50, function ($auctions) use (&$closedCount): void {
                foreach ($auctions as $auction) {
                    $auction->notifyAuctionEnded();
                    $auction->update(['status' => 'closed']);
                    $closedCount++;
                }
            });

        $closedCount += Auction::query()
            ->whereIn('status', ['scheduled', 'published'])
            ->where('end_time', '<=', $now)
            ->whereDoesntHave('bids')
            ->update(['status' => 'closed']);

        return [
            'published' => $publishedCount,
            'closed' => $closedCount,
        ];
    }
}
