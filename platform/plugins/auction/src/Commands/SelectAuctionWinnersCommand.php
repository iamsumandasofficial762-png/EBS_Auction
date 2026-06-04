<?php

namespace Botble\Auction\Commands;

use Botble\Auction\Models\Auction;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SelectAuctionWinnersCommand extends Command
{
    protected $signature = 'auction:select-winners';

    protected $description = 'Close expired auctions and automatically select winners after the selection grace period.';

    public function handle(): int
    {
        Auction::query()
            ->whereIn('status', ['scheduled', 'live'])
            ->where('end_time', '<', Carbon::now())
            ->update(['status' => 'closed']);

        $count = 0;

        Auction::query()
            ->whereIn('status', ['closed', 'live'])
            ->where('auto_select_at', '<=', Carbon::now())
            ->whereNull('winner_customer_id')
            ->whereHas('bids')
            ->with('bids')
            ->chunkById(50, function ($auctions) use (&$count): void {
                foreach ($auctions as $auction) {
                    if ($auction->selectAutomaticWinner()) {
                        $count++;
                    }
                }
            });

        $this->info("Selected {$count} auction winner(s).");

        return self::SUCCESS;
    }
}
