<?php

namespace Botble\Auction\Commands;

use Botble\Auction\Models\Auction;
use Botble\Auction\Services\AuctionNotificationService;
use Botble\Auction\Services\AuctionStatusService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SelectAuctionWinnersCommand extends Command
{
    protected $signature = 'auction:select-winners';

    protected $description = 'Close expired auctions and automatically select winners after the selection grace period.';

    public function handle(AuctionStatusService $auctionStatusService, AuctionNotificationService $notificationService): int
    {
        $auctionStatusService->syncStatuses();

        $count = 0;

        Auction::query()
            ->where('end_time', '<=', Carbon::now())
            ->whereNull('winner_customer_id')
            ->whereHas('bids')
            ->with('bids')
            ->chunkById(50, function ($auctions) use (&$count): void {
                foreach ($auctions as $auction) {
                    $autoSelectAt = $auction->auto_select_at ?: $auction->end_time->copy()->addHours($auction->auto_winner_delay_hours ?: 8);

                    if ($autoSelectAt->greaterThan(Carbon::now())) {
                        continue;
                    }

                    if ($winningBid = $auction->selectAutomaticWinner()) {
                        $notificationService->notifyWinnerSelected($auction, $winningBid, true);
                        $count++;
                    }
                }
            });

        $this->info("Selected {$count} auction winner(s).");

        return self::SUCCESS;
    }
}
