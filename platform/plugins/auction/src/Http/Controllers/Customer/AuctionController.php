<?php

namespace Botble\Auction\Http\Controllers\Customer;

use Botble\Auction\Http\Requests\PlaceBidRequest;
use Botble\Auction\Models\Auction;
use Botble\Auction\Models\AuctionBid;
use Botble\Auction\Models\AuctionNotification;
use Botble\Base\Http\Controllers\BaseController;
use Botble\SeoHelper\Facades\SeoHelper;
use Botble\Theme\Facades\Theme;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AuctionController extends BaseController
{
    public function index(Request $request)
    {
        $this->refreshAuctionStatuses();

        $customer = auth('customer')->user();
        $customerId = $customer->getKey();
        $now = Carbon::now();
        $closedSince = $now->copy()->subHours((int) config('plugins.auction.auction.closed_visible_hours', 8));
        $activeTab = $request->query('tab', 'live');

        Theme::breadcrumb()
            ->add(__('Home'), route('public.index'))
            ->add(__('Auction'));
        SeoHelper::setTitle(__('Auction'));

        $auctionQuery = fn () => Auction::query()
            ->withCount('bids')
            ->withCustomerBid($customerId)
            ->latest();

        $liveAuctions = $auctionQuery()
            ->whereIn('status', ['published', 'scheduled'])
            ->where('start_time', '<=', $now)
            ->where('end_time', '>', $now)
            ->whereDoesntHave('bids', fn ($query) => $query->where('customer_id', $customerId))
            ->limit(60)
            ->get();

        $upcomingAuctions = $auctionQuery()
            ->where(function ($query) use ($now): void {
                $query->where('status', 'scheduled')->orWhere('start_time', '>', $now);
            })
            ->where('end_time', '>', $now)
            ->limit(60)
            ->get();

        $pendingAuctions = $auctionQuery()
            ->whereHas('bids', fn ($query) => $query->where('customer_id', $customerId))
            ->where(function ($query) use ($now): void {
                $query
                    ->where(function ($query) use ($now): void {
                        $query->whereIn('status', ['published', 'scheduled'])
                            ->where('start_time', '<=', $now)
                            ->where('end_time', '>', $now);
                    })
                    ->orWhere(function ($query) use ($now): void {
                        $query->where('end_time', '<=', $now)->whereNull('winner_customer_id');
                    });
            })
            ->limit(60)
            ->get();

        $closedAuctions = $auctionQuery()
            ->where('end_time', '<=', $now)
            ->where('end_time', '>=', $closedSince)
            ->whereNull('winner_customer_id')
            ->limit(60)
            ->get();

        $waitingAuctions = $auctionQuery()
            ->whereHas('bids', fn ($query) => $query->where('customer_id', $customerId))
            ->where('end_time', '<=', $now)
            ->whereNull('winner_customer_id')
            ->limit(60)
            ->get();

        $wonAuctions = $auctionQuery()
            ->where('winner_customer_id', $customerId)
            ->limit(60)
            ->get();

        $notifications = AuctionNotification::query()
            ->with('auction')
            ->where('customer_id', $customerId)
            ->latest()
            ->limit(60)
            ->get();

        $tabs = [
            'live' => [__('Live Auctions'), $liveAuctions],
            'pending' => [__('Pending Auctions'), $pendingAuctions],
            'closed' => [__('Closed Auctions'), $closedAuctions],
            'upcoming' => [__('Upcoming Auctions'), $upcomingAuctions],
            'won' => [__('Won Auctions'), $wonAuctions],
            'waiting' => [__('Waiting For Result'), $waitingAuctions],
            'notifications' => [__('Notifications'), $notifications],
        ];

        return Theme::scope('auction.customer.index', compact(
            'activeTab',
            'closedAuctions',
            'liveAuctions',
            'notifications',
            'pendingAuctions',
            'tabs',
            'upcomingAuctions',
            'waitingAuctions',
            'wonAuctions'
        ), 'plugins/auction::customer.index')->render();
    }

    public function show(Auction $auction)
    {
        $this->refreshAuctionStatuses();

        $customer = auth('customer')->user();
        $customerId = $customer->getKey();

        abort_if(! $auction->isVisibleToCustomers() && ! $auction->hasBidFrom($customerId) && ! $auction->isWonBy($customerId), 404);

        Theme::breadcrumb()
            ->add(__('Home'), route('public.index'))
            ->add(__('Auction'), route('auction.customer.index'))
            ->add($auction->title);
        SeoHelper::setTitle($auction->title);

        $auction->load([
            'category',
            'store',
            'bids' => fn ($query) => $query->where('customer_id', $customerId),
        ]);
        $auction->loadCount('bids');

        $myBid = $auction->getMyBid($customerId);

        return Theme::scope('auction.customer.show', compact('auction', 'myBid'), 'plugins/auction::customer.show')->render();
    }

    public function bid(PlaceBidRequest $request, Auction $auction)
    {
        $customer = auth('customer')->user();

        try {
            DB::transaction(function () use ($request, $auction, $customer): void {
                $lockedAuction = Auction::query()->lockForUpdate()->findOrFail($auction->getKey());

                $this->syncAuctionStatus($lockedAuction);

                if (! $lockedAuction->canCustomerBid($customer)) {
                    throw ValidationException::withMessages([
                        'amount' => $lockedAuction->hasBidFrom($customer->getKey())
                            ? __('You have already placed a bid on this auction.')
                            : __('This auction is not available for bidding.'),
                    ]);
                }

                $amount = (float) $request->input('amount');
                $highestBid = $lockedAuction->bids()->max('amount');
                $minimumBid = $highestBid ? (float) $highestBid : (float) $lockedAuction->starting_bid;

                if (! $highestBid && $amount < $minimumBid) {
                    throw ValidationException::withMessages([
                        'amount' => __('Your bid must be at least :amount.', ['amount' => format_price($minimumBid)]),
                    ]);
                }

                if ($highestBid && $amount <= $minimumBid) {
                    throw ValidationException::withMessages([
                        'amount' => __('Your bid must be greater than :amount.', ['amount' => format_price($minimumBid)]),
                    ]);
                }

                AuctionBid::query()->create([
                    'auction_id' => $lockedAuction->getKey(),
                    'customer_id' => $customer->getKey(),
                    'amount' => $amount,
                ]);
            });
        } catch (ValidationException $exception) {
            throw $exception;
        }

        return back()->with('success_msg', __('Your bid has been placed successfully.'));
    }

    public function readNotification(AuctionNotification $notification)
    {
        abort_if((int) $notification->customer_id !== (int) auth('customer')->id(), 404);

        $notification->update(['is_read' => true]);

        return back()->with('success_msg', __('Notification marked as read.'));
    }

    protected function refreshAuctionStatuses(): void
    {
        Auction::query()
            ->whereIn('status', ['published', 'scheduled'])
            ->where('end_time', '<', Carbon::now())
            ->whereHas('bids')
            ->chunkById(50, function ($auctions): void {
                foreach ($auctions as $auction) {
                    $auction->notifyAuctionEnded();
                    $auction->update(['status' => 'closed']);
                }
            });

        Auction::query()
            ->whereIn('status', ['published', 'scheduled'])
            ->where('end_time', '<', Carbon::now())
            ->whereDoesntHave('bids')
            ->update(['status' => 'closed']);
    }

    protected function syncAuctionStatus(Auction $auction): void
    {
        if (in_array($auction->status, ['draft', 'closed'])) {
            return;
        }

        if ($auction->end_time->lessThan(Carbon::now())) {
            $auction->status = 'closed';
            $auction->save();
        }
    }
}
