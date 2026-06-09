<?php

namespace Botble\Auction\Services;

use Botble\Auction\Models\Auction;
use Botble\Auction\Models\AuctionBid;
use Botble\Auction\Models\AuctionNotification;

class AuctionNotificationService
{
    public function notifyNewAuction(Auction $auction): void
    {
        if (! in_array($auction->status, ['published', 'scheduled'], true)) {
            return;
        }

        $message = $auction->status === 'scheduled'
            ? __(':title is scheduled to go live on :time.', [
                'title' => $auction->title,
                'time' => optional($auction->start_time)->format('M d, Y h:i A') ?: __('the scheduled time'),
            ])
            : __(':title is now available for bidding.', ['title' => $auction->title]);

        $this->firstOrCreate(null, $auction, 'new_auction', [
            'title' => __('New auction item added'),
            'message' => $message,
        ]);
    }

    public function notifyAuctionLive(Auction $auction): void
    {
        $this->firstOrCreate(null, $auction, 'auction_live', [
            'title' => __('Auction is now live'),
            'message' => __(':title is now live. You can place your bid.', ['title' => $auction->title]),
        ]);
    }

    public function notifyAuctionClosed(Auction $auction): void
    {
        $this->bidderCustomerIds($auction)->each(function (int $customerId) use ($auction): void {
            $this->firstOrCreate($customerId, $auction, 'auction_closed', [
                'title' => __('Auction closed'),
                'message' => __(':title has closed. Please wait for the winner result.', ['title' => $auction->title]),
            ]);
        });
    }

    public function notifyWinnerSelected(Auction $auction, AuctionBid $winningBid, bool $automatic = false): void
    {
        $winnerCustomerId = (int) $winningBid->customer_id;

        if (! $winnerCustomerId) {
            return;
        }

        $this->bidderCustomerIds($auction)->each(function (int $customerId) use ($auction, $winnerCustomerId, $automatic): void {
            $won = $customerId === $winnerCustomerId;

            $this->firstOrCreate($customerId, $auction, $won ? 'auction_won' : 'auction_lost', [
                'title' => $automatic
                    ? ($won ? __('Automatic auction won') : __('Automatic auction result'))
                    : ($won ? __('Auction won') : __('Auction result')),
                'message' => $won
                    ? __('Congratulations! You got the auction item: :title.', ['title' => $auction->title])
                    : __('You did not get the auction item: :title.', ['title' => $auction->title]),
            ]);
        });
    }

    protected function bidderCustomerIds(Auction $auction)
    {
        return $auction->bids()
            ->whereNotNull('customer_id')
            ->distinct()
            ->pluck('customer_id')
            ->map(fn ($customerId) => (int) $customerId)
            ->filter()
            ->unique()
            ->values();
    }

    protected function firstOrCreate(?int $customerId, Auction $auction, string $type, array $data): AuctionNotification
    {
        $query = AuctionNotification::query()
            ->where('auction_id', $auction->getKey())
            ->where('type', $type);

        $customerId
            ? $query->where('customer_id', $customerId)
            : $query->whereNull('customer_id');

        $notification = $query->first();

        if ($notification) {
            return $notification;
        }

        return AuctionNotification::query()->create([
            'customer_id' => $customerId,
            'auction_id' => $auction->getKey(),
            'type' => $type,
            'title' => $data['title'],
            'message' => $data['message'],
            'is_read' => false,
        ]);
    }
}
