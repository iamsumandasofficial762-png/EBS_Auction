<?php

namespace Botble\Auction\Http\Controllers\Vendor;

use Botble\Auction\Http\Requests\CreateAuctionRequest;
use Botble\Auction\Models\Auction;
use Botble\Auction\Models\AuctionBid;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Ecommerce\Models\ProductCategory;
use Botble\Media\Facades\RvMedia;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AuctionController extends BaseController
{
    public function index()
    {
        $this->pageTitle(__('Auction'));
        $this->refreshAuctionStatuses();

        $auctions = Auction::query()
            ->where('vendor_id', auth('customer')->id())
            ->withCount('bids')
            ->latest()
            ->paginate(15);

        return view('plugins/auction::vendor.index', compact('auctions'));
    }

    public function create()
    {
        $this->pageTitle(__('Create auction'));

        $auction = new Auction([
            'status' => 'scheduled',
            'bid_increment' => 1,
            'start_time' => Carbon::now()->addHour(),
            'end_time' => Carbon::now()->addDay(),
        ]);
        $categories = ProductCategory::query()->orderBy('name')->pluck('name', 'id');

        return view('plugins/auction::vendor.create', compact('auction', 'categories'));
    }

    public function store(CreateAuctionRequest $request)
    {
        $auction = new Auction($this->prepareData($request));
        $auction->vendor_id = auth('customer')->id();
        $auction->store_id = auth('customer')->user()->store?->id;
        $auction->auto_select_at = Carbon::parse($auction->end_time)->addHours(8);
        $auction->status = $this->normalizeStatus($auction->status, $auction->start_time, $auction->end_time);
        $auction->save();

        return redirect()
            ->route('marketplace.vendor.auctions.edit', $auction)
            ->with('success_msg', __('Auction has been created successfully.'));
    }

    public function edit(Auction $auction)
    {
        $this->authorizeAuction($auction);
        $this->pageTitle(__('Edit auction'));

        $categories = ProductCategory::query()->orderBy('name')->pluck('name', 'id');

        return view('plugins/auction::vendor.edit', compact('auction', 'categories'));
    }

    public function update(CreateAuctionRequest $request, Auction $auction)
    {
        $this->authorizeAuction($auction);

        if ($auction->start_time && $auction->start_time->lessThanOrEqualTo(Carbon::now())) {
            return back()->with('error_msg', __('Auction cannot be edited after it starts.'));
        }

        $auction->fill($this->prepareData($request));
        $auction->auto_select_at = Carbon::parse($auction->end_time)->addHours(8);
        $auction->status = $this->normalizeStatus($auction->status, $auction->start_time, $auction->end_time);
        $auction->save();

        return back()->with('success_msg', __('Auction has been updated successfully.'));
    }

    public function destroy(Auction $auction)
    {
        $this->authorizeAuction($auction);

        if ($auction->status === 'live' && $auction->bids()->exists()) {
            return back()->with('error_msg', __('A live auction with bids cannot be cancelled.'));
        }

        if ($auction->bids()->exists()) {
            $auction->update(['status' => 'cancelled']);
        } else {
            $auction->delete();
        }

        return redirect()
            ->route('marketplace.vendor.auctions.index')
            ->with('success_msg', __('Auction has been cancelled successfully.'));
    }

    public function bidders(Auction $auction)
    {
        $this->authorizeAuction($auction);
        $this->pageTitle(__('Auction bidders'));

        $auction->load(['winner', 'winningBid']);
        $bids = $auction->bids()
            ->with('customer')
            ->orderByDesc('amount')
            ->orderBy('created_at')
            ->paginate(30);

        return view('plugins/auction::vendor.bidders', compact('auction', 'bids'));
    }

    public function chooseWinner(Auction $auction, AuctionBid $bid)
    {
        $this->authorizeAuction($auction);

        abort_if((int) $bid->auction_id !== (int) $auction->getKey(), 404);

        if ($auction->end_time->greaterThan(Carbon::now())) {
            return back()->with('error_msg', __('Winner can only be selected after auction closing time.'));
        }

        $auction->forceFill([
            'winner_customer_id' => $bid->customer_id,
            'winning_bid_id' => $bid->getKey(),
            'winner_selected_at' => Carbon::now(),
            'status' => 'closed',
        ])->save();

        return back()->with('success_msg', __('Winner has been selected successfully.'));
    }

    protected function authorizeAuction(Auction $auction): void
    {
        abort_if((int) $auction->vendor_id !== (int) auth('customer')->id(), 404);
    }

    protected function prepareData(Request $request): array
    {
        return [
            ...$request->only([
                'title',
                'description',
                'category_id',
                'starting_bid',
                'bid_increment',
                'start_time',
                'end_time',
                'status',
            ]),
            'slug' => $request->input('slug') ?: Str::slug($request->input('title')) . '-' . Str::lower(Str::random(6)),
            'images' => $this->parseImages($request->input('images')),
        ];
    }

    protected function parseImages(mixed $images): array
    {
        if (is_array($images)) {
            return array_values(array_filter($images));
        }

        return collect(preg_split('/[\r\n,]+/', (string) $images))
            ->map(fn ($image) => trim($image))
            ->filter()
            ->values()
            ->all();
    }

    protected function normalizeStatus(string $status, mixed $startTime, mixed $endTime): string
    {
        if (in_array($status, ['draft', 'cancelled', 'closed'])) {
            return $status;
        }

        $start = Carbon::parse($startTime);
        $end = Carbon::parse($endTime);

        if ($end->lessThan(Carbon::now())) {
            return 'closed';
        }

        return $start->lessThanOrEqualTo(Carbon::now()) ? 'live' : 'scheduled';
    }

    protected function refreshAuctionStatuses(): void
    {
        Auction::query()
            ->where('vendor_id', auth('customer')->id())
            ->where('status', 'scheduled')
            ->where('start_time', '<=', Carbon::now())
            ->where('end_time', '>', Carbon::now())
            ->update(['status' => 'live']);

        Auction::query()
            ->where('vendor_id', auth('customer')->id())
            ->whereIn('status', ['scheduled', 'live'])
            ->where('end_time', '<', Carbon::now())
            ->update(['status' => 'closed']);
    }
}
