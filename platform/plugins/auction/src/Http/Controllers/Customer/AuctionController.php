<?php

namespace Botble\Auction\Http\Controllers\Customer;

use Botble\Auction\Http\Requests\PlaceBidRequest;
use Botble\Auction\Models\Auction;
use Botble\Auction\Models\AuctionBid;
use Botble\Auction\Models\AuctionNotification;
use Botble\Auction\Services\AuctionStatusService;
use Botble\Auction\Services\AuctionWinnerService;
use Botble\Base\Http\Controllers\BaseController;
use Botble\SeoHelper\Facades\SeoHelper;
use Botble\Theme\Facades\Theme;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class AuctionController extends BaseController
{
    public function __construct(
        protected AuctionStatusService $auctionStatusService,
        protected AuctionWinnerService $auctionWinnerService
    )
    {
    }

    public function index(Request $request)
    {
        Theme::breadcrumb()
            ->add(__('Home'), route('public.index'))
            ->add(__('Auction'));
        SeoHelper::setTitle(__('Auction'));

        $data = $this->buildDashboardData($request);

        return Theme::scope('auction.customer.index', $data, 'plugins/auction::customer.index')->render();
    }

    public function statusFeed(Request $request)
    {
        $data = $this->buildDashboardData($request);

        return response()->json([
            'success' => true,
            'active_tab' => $data['activeTab'],
            'counts' => collect($data['tabs'])
                ->map(fn (array $tab, string $key) => $key === 'notifications' ? $data['unreadNotificationCount'] : $tab[1]->count())
                ->all(),
            'html' => collect($data['tabs'])
                ->map(fn (array $tab, string $key) => $this->renderTabHtml($key, $tab[1]))
                ->all(),
        ]);
    }

    protected function buildDashboardData(Request $request): array
    {
        $this->auctionStatusService->syncStatuses();
        $this->auctionWinnerService->selectDueAutomaticWinners();

        $customer = auth('customer')->user();
        $customerId = $customer->getKey();
        $now = Carbon::now(config('app.timezone'));
        $closedSince = $now->copy()->subHours((int) config('plugins.auction.auction.closed_visible_hours', 8));
        $activeTab = $request->query('tab', 'live');

        $auctionQuery = fn () => Auction::query()
            ->withCount('bids')
            ->withCustomerBid($customerId)
            ->latest();

        $liveAuctions = $auctionQuery()
            ->where('status', 'published')
            ->where(function ($query) use ($now): void {
                $query->whereNull('start_time')->orWhere('start_time', '<=', $now);
            })
            ->where('end_time', '>', $now)
            ->whereDoesntHave('bids', fn ($query) => $query->where('customer_id', $customerId))
            ->limit(60)
            ->get();

        $upcomingAuctions = $auctionQuery()
            ->where('status', 'scheduled')
            ->where('start_time', '>', $now)
            ->where('end_time', '>', $now)
            ->limit(60)
            ->get();

        $pendingAuctions = $auctionQuery()
            ->whereHas('bids', fn ($query) => $query->where('customer_id', $customerId))
            ->where('status', 'published')
            ->where(function ($query) use ($now): void {
                $query->whereNull('start_time')->orWhere('start_time', '<=', $now);
            })
            ->where('end_time', '>', $now)
            ->whereNull('winner_customer_id')
            ->limit(60)
            ->get();

        $closedAuctions = $auctionQuery()
            ->where('status', 'closed')
            ->where('end_time', '<=', $now)
            ->where('end_time', '>=', $closedSince)
            ->where(function ($query) use ($customerId): void {
                $query
                    ->where(function ($query) use ($customerId): void {
                        $query
                            ->whereNull('winner_customer_id')
                            ->whereDoesntHave('bids', fn ($query) => $query->where('customer_id', $customerId));
                    })
                    ->orWhere(function ($query) use ($customerId): void {
                        $query
                            ->whereNotNull('winner_customer_id')
                            ->where('winner_customer_id', '!=', $customerId);
                    });
            })
            ->limit(60)
            ->get();

        $waitingAuctions = $auctionQuery()
            ->whereHas('bids', fn ($query) => $query->where('customer_id', $customerId))
            ->where('status', 'closed')
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
            ->where(function ($query) use ($customerId): void {
                $query->where('customer_id', $customerId)
                    ->orWhereNull('customer_id');
            })
            ->latest()
            ->limit(60)
            ->get();

        $unreadNotificationCount = $this->unreadNotificationsCount();

        $tabs = [
            'live' => [__('Live Auctions'), $liveAuctions],
            'pending' => [__('Pending Auctions'), $pendingAuctions],
            'closed' => [__('Closed Auctions'), $closedAuctions],
            'upcoming' => [__('Upcoming Auctions'), $upcomingAuctions],
            'won' => [__('Won Auctions'), $wonAuctions],
            'waiting' => [__('Waiting For Result'), $waitingAuctions],
            'notifications' => [__('Notifications'), $notifications],
        ];

        if (! array_key_exists($activeTab, $tabs)) {
            $activeTab = 'live';
        }

        return compact(
            'activeTab',
            'closedAuctions',
            'liveAuctions',
            'notifications',
            'unreadNotificationCount',
            'pendingAuctions',
            'tabs',
            'upcomingAuctions',
            'waitingAuctions',
            'wonAuctions'
        );
    }

    protected function renderTabHtml(string $key, $items): string
    {
        return view('plugins/auction::customer.partials.tab-panel', compact('key', 'items'))->render();
    }

    public function show(Auction $auction)
    {
        $this->auctionStatusService->syncStatuses();
        $this->auctionWinnerService->selectDueAutomaticWinners();
        $auction->refresh();

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
        $hasBid = $myBid !== null;

        return Theme::scope('auction.customer.show', compact('auction', 'myBid', 'hasBid'), 'plugins/auction::customer.show')->render();
    }

    public function bid(PlaceBidRequest $request, Auction $auction)
    {
        $customer = auth('customer')->user();

        try {
            $bid = DB::transaction(function () use ($request, $auction, $customer): AuctionBid {
                $this->auctionStatusService->syncStatuses();

                $lockedAuction = Auction::query()->lockForUpdate()->findOrFail($auction->getKey());
                $lockedAuction->refresh();

                if (! $lockedAuction->canCustomerBid($customer)) {
                    throw ValidationException::withMessages([
                        'amount' => $lockedAuction->hasBidFrom($customer->getKey())
                            ? __('You have already placed a bid on this auction.')
                            : __('This auction is not available for bidding.'),
                    ]);
                }

                $amount = (float) $request->input('amount');
                $setAmount = (float) $lockedAuction->starting_bid;

                if ($amount < $setAmount) {
                    throw ValidationException::withMessages([
                        'amount' => __('Your bid must be at least :amount.', ['amount' => format_price($setAmount)]),
                    ]);
                }

                $bidData = [
                    'auction_id' => $lockedAuction->getKey(),
                    'customer_id' => $customer->getKey(),
                    'amount' => $amount,
                ];

                if (Schema::hasColumn('auction_bids', 'auction_item_id')) {
                    $bidData['auction_item_id'] = $lockedAuction->getKey();
                }

                if (Schema::hasColumn('auction_bids', 'user_id')) {
                    $bidData['user_id'] = $customer->getKey();
                }

                if (Schema::hasColumn('auction_bids', 'bid_amount')) {
                    $bidData['bid_amount'] = $amount;
                }

                return AuctionBid::query()->create($bidData);
            });
        } catch (ValidationException $exception) {
            throw $exception;
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Your bid has been placed successfully.'),
                'auction_id' => $auction->getKey(),
                'my_bid' => format_price($bid->amount),
                'button_text' => __('Bid Placed'),
                'status_label' => __('Bid Placed'),
            ]);
        }

        return back()->with('success_msg', __('Your bid has been placed successfully.'));
    }

    public function readNotification(AuctionNotification $notification)
    {
        $this->authorizeNotification($notification);

        $notification->update(['is_read' => true]);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Notification marked as read.'),
                'notification_id' => $notification->getKey(),
                'unread_count' => $this->unreadNotificationsCount(),
            ]);
        }

        return back()->with('success_msg', __('Notification marked as read.'));
    }

    public function deleteNotification(AuctionNotification $notification)
    {
        $this->authorizeNotification($notification);

        $notification->delete();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => __('Notification deleted.'),
                'notification_id' => $notification->getKey(),
                'unread_count' => $this->unreadNotificationsCount(),
                'notifications_count' => $this->visibleNotificationsQuery()->count(),
            ]);
        }

        return back()->with('success_msg', __('Notification deleted.'));
    }

    protected function authorizeNotification(AuctionNotification $notification): void
    {
        abort_if($notification->customer_id && (int) $notification->customer_id !== (int) auth('customer')->id(), 404);
    }

    protected function unreadNotificationsCount(): int
    {
        return (clone $this->visibleNotificationsQuery())->where('is_read', false)->count();
    }

    protected function visibleNotificationsQuery()
    {
        $customerId = auth('customer')->id();

        return AuctionNotification::query()
            ->where(function ($query) use ($customerId): void {
                $query->where('customer_id', $customerId)
                    ->orWhereNull('customer_id');
            });
    }

    protected function syncAuctionStatus(Auction $auction): void
    {
        if (in_array($auction->status, ['draft', 'closed'])) {
            return;
        }

        if ($auction->end_time && $auction->end_time->lessThan(Carbon::now())) {
            $auction->status = 'closed';
            $auction->save();
        }
    }
}
