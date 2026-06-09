<?php

namespace Botble\Auction\Services;

use Botble\Auction\Models\Auction;
use Botble\Auction\Models\AuctionBid;
use Botble\Auction\Models\AuctionNotification;
use Illuminate\Support\Facades\Schema;

class AuctionNotificationService
{
    public function notifyNewAuction(Auction $auction): void
    {
        if (! in_array($auction->status, ['published', 'scheduled'], true)) {
            return;
        }

        $message = $auction->status === 'scheduled'
            ? __(':title has been added and is scheduled to go live on :time.', [
                'title' => $auction->title,
                'time' => optional($auction->start_time)->format('M d, Y h:i A') ?: __('the scheduled time'),
            ])
            : __(':title has been added and is scheduled to go live on :time.', [
                'title' => $auction->title,
                'time' => optional($auction->start_time ?: $auction->created_at)->format('M d, Y h:i A') ?: __('the scheduled time'),
            ]);

        $this->firstOrCreate(null, $auction, 'new_auction', [
            'title' => __('New Auction Item Added'),
            'message' => $message,
        ]);
    }

    public function notifyAuctionLive(Auction $auction): void
    {
        $this->firstOrCreate(null, $auction, 'auction_live', [
            'title' => __('Auction Is Now Live'),
            'message' => __(':title is now live. You can place your bid until :time.', [
                'title' => $auction->title,
                'time' => optional($auction->end_time)->format('M d, Y h:i A') ?: __('the auction ends'),
            ]),
        ]);
    }

    public function notifyAuctionClosed(Auction $auction): void
    {
        $this->bidderCustomerIds($auction)->each(function (int $customerId) use ($auction): void {
            $this->firstOrCreate($customerId, $auction, 'auction_closed', [
                'title' => __('Waiting for Winner Selection'),
                'message' => __(':title has closed. Winner selection is in progress.', ['title' => $auction->title]),
            ]);
        });
    }

    public function notifyWinnerSelected(Auction $auction, AuctionBid $winningBid, bool $automatic = false): void
    {
        $winnerCustomerId = (int) $winningBid->customer_id;

        if (! $winnerCustomerId) {
            return;
        }

        $this->firstOrCreate(null, $auction, 'auction_result', [
            'title' => __('Auction Result Announced'),
            'message' => __('The auction for :title has been completed and moved to Closed Auctions.', ['title' => $auction->title]),
        ]);

        $this->bidderCustomerIds($auction)->each(function (int $customerId) use ($auction, $winnerCustomerId, $automatic): void {
            $won = $customerId === $winnerCustomerId;

            $this->firstOrCreate($customerId, $auction, $won ? 'auction_won' : 'auction_lost', [
                'title' => $won ? __('Congratulations! You Won the Auction 🎉') : __('Auction Result Available'),
                'message' => $won
                    ? __('Congratulations! You have won the auction for :title.', ['title' => $auction->title])
                    : __('Your bid for :title was not selected. Better luck in the next auction.', ['title' => $auction->title]),
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

        if (Schema::hasColumn('auction_notifications', 'customer_id')) {
            $customerId
                ? $query->where('customer_id', $customerId)
                : $query->whereNull('customer_id');
        } elseif (Schema::hasColumn('auction_notifications', 'user_id')) {
            $query->where('user_id', $customerId ?: 0);
        }

        $notification = $query->first();

        if ($notification) {
            return $notification;
        }

        $notificationData = [
            'auction_id' => $auction->getKey(),
            'type' => $type,
            'title' => $data['title'],
            'message' => $data['message'],
            'is_read' => false,
        ];

        if (Schema::hasColumn('auction_notifications', 'customer_id')) {
            $notificationData['customer_id'] = $customerId;
        }

        if (Schema::hasColumn('auction_notifications', 'user_id')) {
            $notificationData['user_id'] = $customerId ?: 0;
        }

        return AuctionNotification::query()->create($notificationData);
    }
}
