<?php

namespace Botble\Auction\Commands;

use Botble\Auction\Services\AuctionStatusService;
use Illuminate\Console\Command;

class UpdateAuctionStatusesCommand extends Command
{
    protected $signature = 'auction:update-statuses';

    protected $description = 'Automatically publish started auctions and close ended auctions.';

    public function handle(AuctionStatusService $auctionStatusService): int
    {
        $result = $auctionStatusService->syncStatuses();

        $this->info("Scheduled auctions published: {$result['published']}");
        $this->info("Auctions closed: {$result['closed']}");

        return self::SUCCESS;
    }
}
