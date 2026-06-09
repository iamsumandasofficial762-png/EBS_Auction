<?php

namespace Botble\Auction\Http\Controllers\Vendor;

use Botble\Auction\Http\Requests\CreateAuctionRequest;
use Botble\Auction\Http\Requests\UpdateAuctionRequest;
use Botble\Auction\Models\Auction;
use Botble\Auction\Models\AuctionBid;
use Botble\Auction\Services\AuctionNotificationService;
use Botble\Auction\Services\AuctionStatusService;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Ecommerce\Models\ProductCategory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AuctionController extends BaseController
{
    public function __construct(
        protected AuctionStatusService $auctionStatusService,
        protected AuctionNotificationService $notificationService
    )
    {
    }

    public function index()
    {
        $this->pageTitle(__('Auction'));
        $this->auctionStatusService->syncStatuses();

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
            'status' => 'draft',
            'start_time' => Carbon::now()->addHour(),
            'end_time' => Carbon::now()->addDay(),
            'auto_winner_delay_hours' => 8,
            'condition' => 'new',
        ]);
        $categories = ProductCategory::query()->orderBy('name')->pluck('name', 'id');

        return view('plugins/auction::vendor.create', compact('auction', 'categories'));
    }

    public function store(CreateAuctionRequest $request)
    {
        $auction = new Auction($this->prepareData($request));
        $auction->vendor_id = auth('customer')->id();
        $auction->store_id = auth('customer')->user()->store?->id;
        if ($auction->end_time) {
            $auction->auto_select_at = Carbon::parse($auction->end_time)->addHours((int) $auction->auto_winner_delay_hours);
        }

        $auction->status = $this->normalizeStatus($auction->status, $auction->start_time, $auction->end_time);
        $auction->save();
        $this->notificationService->notifyNewAuction($auction);
        $this->auctionStatusService->syncStatuses();

        return redirect()
            ->route('marketplace.vendor.auctions.index')
            ->with('success_msg', __('Auction has been created successfully.'));
    }

    public function edit(Auction $auction)
    {
        $this->auctionStatusService->syncStatuses();
        $auction->refresh();
        $this->authorizeAuction($auction);
        $this->pageTitle(__('Edit auction'));

        $categories = ProductCategory::query()->orderBy('name')->pluck('name', 'id');

        return view('plugins/auction::vendor.edit', compact('auction', 'categories'));
    }

    public function status(Auction $auction)
    {
        $this->authorizeAuction($auction);
        $this->auctionStatusService->syncStatuses();
        $auction->refresh();

        return response()->json([
            'success' => true,
            'status' => $auction->status,
            'status_label' => $auction->status_label,
            'status_badge_class' => $auction->status_badge_class,
            'is_live' => $auction->isLive(),
            'is_closed' => $auction->isClosed(),
            'can_edit_critical_fields' => $auction->canVendorEditCriticalFields(),
            'start_time' => optional($auction->start_time)->toIso8601String(),
            'end_time' => optional($auction->end_time)->toIso8601String(),
        ]);
    }

    public function statuses(Request $request)
    {
        $this->auctionStatusService->syncStatuses();

        $ids = collect(explode(',', (string) $request->query('ids')))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return response()->json([
                'success' => true,
                'auctions' => [],
            ]);
        }

        $auctions = Auction::query()
            ->where('vendor_id', auth('customer')->id())
            ->whereIn('id', $ids)
            ->withCount('bids')
            ->get()
            ->mapWithKeys(fn (Auction $auction) => [
                $auction->getKey() => [
                    'id' => $auction->getKey(),
                    'status' => $auction->status,
                    'status_label' => $auction->status_label,
                    'status_badge_class' => $auction->status_badge_class,
                    'is_live' => $auction->isLive(),
                    'is_closed' => $auction->isClosed(),
                    'current_bid' => format_price($auction->current_bid_amount),
                    'bids_count' => $auction->bids_count,
                    'start_time' => optional($auction->start_time)->toIso8601String(),
                    'end_time' => optional($auction->end_time)->toIso8601String(),
                    'end_time_label' => optional($auction->end_time)->translatedFormat('M d, Y H:i'),
                ],
            ]);

        return response()->json([
            'success' => true,
            'auctions' => $auctions,
        ]);
    }

    public function update(UpdateAuctionRequest $request, Auction $auction)
    {
        $this->authorizeAuction($auction);

        $canEditCriticalFields = $auction->canVendorEditCriticalFields();
        $originalStatus = $auction->status;

        $auction->fill($this->prepareData($request, $auction, $canEditCriticalFields));

        if ($canEditCriticalFields) {
            if ($auction->end_time) {
                $auction->auto_select_at = Carbon::parse($auction->end_time)->addHours((int) $auction->auto_winner_delay_hours);
            }

            $auction->status = $this->normalizeStatus($auction->status, $auction->start_time, $auction->end_time);
        }

        $auction->save();

        if (! in_array($originalStatus, ['published', 'scheduled'], true) && in_array($auction->status, ['published', 'scheduled'], true)) {
            $this->notificationService->notifyNewAuction($auction);
        } elseif ($originalStatus === 'scheduled' && $auction->status === 'published') {
            $this->notificationService->notifyAuctionLive($auction);
        }

        $this->auctionStatusService->syncStatuses();

        return back()->with('success_msg', __('Auction has been updated successfully.'));
    }

    public function destroy(Auction $auction)
    {
        $this->authorizeAuction($auction);

        if (! $auction->canVendorDelete()) {
            return back()->with('error_msg', __('This auction already has bids and cannot be deleted.'));
        }

        $auction->delete();

        return redirect()
            ->route('marketplace.vendor.auctions.index')
            ->with('success_msg', __('Auction has been deleted successfully.'));
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

        if (! $auction->canChooseWinner()) {
            return back()->with('error_msg', __('Winner can only be selected after auction closing time.'));
        }

        $auction->forceFill([
            'winner_customer_id' => $bid->customer_id,
            'winning_bid_id' => $bid->getKey(),
            'winner_selected_at' => Carbon::now(),
            'status' => 'closed',
        ])->save();

        $this->notificationService->notifyWinnerSelected($auction, $bid);

        return back()->with('success_msg', __('Winner has been selected successfully.'));
    }

    protected function authorizeAuction(Auction $auction): void
    {
        abort_if((int) $auction->vendor_id !== (int) auth('customer')->id(), 404);
    }

    protected function prepareData(Request $request, ?Auction $auction = null, bool $includeCriticalFields = true): array
    {
        $data = [
            ...$request->only([
                'short_description',
                'description',
            ]),
            'images' => $this->parseImages($request->input('images', [])),
        ];

        if (! $includeCriticalFields) {
            return $data;
        }

        $data = [
            ...$data,
            ...$request->only([
                'title',
                'condition',
                'brand',
                'model',
                'starting_bid',
                'start_time',
                'end_time',
                'status',
                'auto_winner_delay_hours',
            ]),
            'category_id' => $request->input('category_id') ?: null,
            'bid_increment' => 1,
            'slug' => $request->input('slug') ?: $this->generateUniqueSlug($request->input('title'), $auction),
        ];

        if (($data['status'] ?? null) === 'published') {
            $data['start_time'] = Carbon::now(config('app.timezone'));
        }

        if (Schema::hasColumn('auction_items', 'start_at')) {
            $data['start_at'] = $data['start_time'];
        }

        if (Schema::hasColumn('auction_items', 'end_at')) {
            $data['end_at'] = $data['end_time'];
        }

        return $data;
    }

    protected function parseImages(mixed $images): array
    {
        $normalizer = new Auction();

        if (is_array($images)) {
            return collect($images)
                ->map(fn ($image) => $normalizer->normalizeImagePath((string) $image))
                ->filter()
                ->values()
                ->all();
        }

        return collect(preg_split('/[\r\n,]+/', (string) $images))
            ->map(fn ($image) => $normalizer->normalizeImagePath((string) $image))
            ->filter()
            ->values()
            ->all();
    }

    protected function normalizeStatus(string $status, mixed $startTime, mixed $endTime): string
    {
        if (in_array($status, ['draft', 'closed'])) {
            return $status;
        }

        if (! $endTime) {
            return $status === 'scheduled' ? 'scheduled' : 'draft';
        }

        $end = Carbon::parse($endTime);

        if ($end->lessThanOrEqualTo(Carbon::now(config('app.timezone')))) {
            return 'closed';
        }

        return in_array($status, ['published', 'scheduled']) ? $status : 'draft';
    }

    protected function generateUniqueSlug(string $title, ?Auction $auction = null): string
    {
        $slug = Str::slug($title) ?: Str::lower(Str::random(8));
        $originalSlug = $slug;
        $counter = 2;

        while (Auction::query()
            ->where('slug', $slug)
            ->when($auction?->exists, fn ($query) => $query->where($auction->getKeyName(), '!=', $auction->getKey()))
            ->exists()) {
            $slug = "{$originalSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
