<?php

namespace Botble\Auction\Commands;

use Botble\Auction\Services\AuctionStatusService;
use Botble\Auction\Services\AuctionWinnerService;
use Illuminate\Console\Command;

class SelectAuctionWinnersCommand extends Command
{
    protected $signature = 'auction:select-winners';

    protected $description = 'Close expired auctions and automatically select winners after the selection grace period.';

    public function handle(AuctionStatusService $auctionStatusService, AuctionWinnerService $auctionWinnerService): int
    {
        $auctionStatusService->syncStatuses();
        $count = $auctionWinnerService->selectDueAutomaticWinners();

        $this->info("Selected {$count} auction winner(s).");

        return self::SUCCESS;
    }
}
