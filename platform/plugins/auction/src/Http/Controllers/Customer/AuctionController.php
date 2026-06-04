<?php

namespace Botble\Auction\Http\Controllers\Customer;

use Botble\Auction\Http\Requests\PlaceBidRequest;
use Botble\Auction\Models\Auction;
use Botble\Auction\Models\AuctionBid;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Theme\Facades\Theme;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AuctionController extends BaseController
{
    public function index()
    {
        $this->refreshAuctionStatuses();

        Theme::breadcrumb()
            ->add(__('Home'), route('public.index'))
            ->add(__('Auction'));

        $auctions = Auction::query()
            ->withCount('bids')
            ->where(function ($query): void {
                $query
                    ->where(function ($query): void {
                        $query
                            ->where('status', 'published')
                            ->where('start_time', '<=', Carbon::now())
                            ->where('end_time', '>', Carbon::now());
                    })
                    ->orWhere(function ($query): void {
                        $query
                            ->where('status', 'scheduled')
                            ->where('end_time', '>', Carbon::now());
                    })
                    ->orWhere('status', 'closed');
            })
            ->latest()
            ->paginate(12);

        $myBids = AuctionBid::query()
            ->with('auction')
            ->where('customer_id', auth('customer')->id())
            ->latest()
            ->limit(10)
            ->get();

        return view('plugins/auction::customer.index', compact('auctions', 'myBids'));
    }

    public function show(Auction $auction)
    {
        $this->refreshAuctionStatuses();

        abort_if(! $auction->isVisibleToCustomers(), 404);

        Theme::breadcrumb()
            ->add(__('Home'), route('public.index'))
            ->add(__('Auction'), route('auction.customer.index'))
            ->add($auction->title);

        $auction->load(['bids.customer', 'store', 'winner', 'winningBid']);
        $myBids = $auction->bids()
            ->where('customer_id', auth('customer')->id())
            ->latest()
            ->get();

        return view('plugins/auction::customer.show', compact('auction', 'myBids'));
    }

    public function bid(PlaceBidRequest $request, Auction $auction)
    {
        $customer = auth('customer')->user();

        try {
            DB::transaction(function () use ($request, $auction, $customer): void {
                $lockedAuction = Auction::query()->lockForUpdate()->findOrFail($auction->getKey());

                $this->syncAuctionStatus($lockedAuction);

                if (! $lockedAuction->canBid($customer)) {
                    throw ValidationException::withMessages([
                        'amount' => __('This auction is not available for bidding.'),
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

    protected function refreshAuctionStatuses(): void
    {
        Auction::query()
            ->whereIn('status', ['published', 'scheduled'])
            ->where('end_time', '<', Carbon::now())
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
