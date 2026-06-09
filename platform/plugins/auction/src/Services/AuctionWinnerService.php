<?php

namespace Botble\Auction\Services;

use Botble\Auction\Models\Auction;
use Carbon\Carbon;

class AuctionWinnerService
{
    public function __construct(protected AuctionNotificationService $notificationService)
    {
    }

    public function selectDueAutomaticWinners(): int
    {
        $now = Carbon::now(config('app.timezone'));
        $count = 0;

        Auction::query()
            ->where('status', 'closed')
            ->whereNotNull('end_time')
            ->where('end_time', '<=', $now)
            ->whereNull('winner_customer_id')
            ->whereHas('bids')
            ->with('bids')
            ->chunkById(50, function ($auctions) use (&$count, $now): void {
                foreach ($auctions as $auction) {
                    $autoSelectAt = $auction->autoSelectAt();

                    if (! $autoSelectAt || $autoSelectAt->greaterThan($now)) {
                        continue;
                    }

                    if ($winningBid = $auction->selectAutomaticWinner()) {
                        $this->notificationService->notifyWinnerSelected($auction, $winningBid, true);
                        $count++;
                    }
                }
            });

        return $count;
    }
}
