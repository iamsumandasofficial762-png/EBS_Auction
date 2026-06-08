<?php

namespace Botble\Auction\Models;

use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\ProductCategory;
use Botble\Marketplace\Models\Store;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\File;

class Auction extends Model
{
    protected $table = 'auction_items';

    protected $fillable = [
        'vendor_id',
        'store_id',
        'title',
        'slug',
        'short_description',
        'description',
        'images',
        'category_id',
        'condition',
        'brand',
        'model',
        'starting_bid',
        'bid_increment',
        'start_time',
        'end_time',
        'start_at',
        'end_at',
        'status',
        'winner_customer_id',
        'winning_bid_id',
        'winner_selected_at',
        'auto_select_at',
        'auto_winner_delay_hours',
    ];

    protected $casts = [
        'images' => 'array',
        'starting_bid' => 'decimal:2',
        'bid_increment' => 'decimal:2',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'winner_selected_at' => 'datetime',
        'auto_select_at' => 'datetime',
        'auto_winner_delay_hours' => 'integer',
    ];

    public function bids(): HasMany
    {
        return $this->hasMany(AuctionBid::class, 'auction_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'vendor_id')->withDefault();
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'store_id')->withDefault();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'category_id')->withDefault();
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'winner_customer_id')->withDefault();
    }

    public function winningBid(): BelongsTo
    {
        return $this->belongsTo(AuctionBid::class, 'winning_bid_id')->withDefault();
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(AuctionNotification::class, 'auction_id');
    }

    public function getCurrentBidAmountAttribute(): float
    {
        return $this->currentBidAmount();
    }

    public function getMinimumNextBidAttribute(): float
    {
        return $this->minimumNextBid();
    }

    public function getPrimaryImageAttribute(): ?string
    {
        $image = $this->images[0] ?? null;

        return $image ?: null;
    }

    public function getImagesAttribute($value): array
    {
        $images = $this->fromJson($value) ?: [];

        $resolvedImages = collect($images)
            ->map(fn ($image) => $this->resolveImagePath($image))
            ->filter()
            ->values()
            ->all();

        return $resolvedImages ?: $this->fallbackImagesFromMatchingAuction();
    }

    public function normalizeImagePath(string $image): string
    {
        $image = str_replace('\\', '/', trim($image));

        if (filter_var($image, FILTER_VALIDATE_URL)) {
            $path = parse_url($image, PHP_URL_PATH);
            $image = $path ? ltrim($path, '/') : $image;
        }

        $image = ltrim($image, '/');

        return str($image)
            ->replaceStart('storage/', '')
            ->replaceStart('public/storage/', '')
            ->toString();
    }

    public function resolveImagePath(string $image): ?string
    {
        $image = $this->normalizeImagePath($image);

        if ($image === '') {
            return null;
        }

        if (filter_var($image, FILTER_VALIDATE_URL)) {
            return $image;
        }

        if ($this->imageExists($image)) {
            return $image;
        }

        $basename = basename($image);

        foreach (array_filter([
            $this->store?->upload_folder ? $this->store->upload_folder . '/' . $basename : null,
            $this->vendor?->upload_folder ? $this->vendor->upload_folder . '/' . $basename : null,
            'stores/' . $basename,
            'customers/' . $basename,
        ]) as $candidate) {
            if ($this->imageExists($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    protected function imageExists(string $image): bool
    {
        $image = str_replace('\\', '/', ltrim($image, '/'));

        return File::isFile(public_path($image))
            || File::isFile(public_path('storage/' . $image))
            || File::isFile(storage_path('app/public/' . $image));
    }

    protected function fallbackImagesFromMatchingAuction(): array
    {
        if (! $this->exists || ! $this->title) {
            return [];
        }

        $rawImages = static::query()
            ->whereKeyNot($this->getKey())
            ->where('vendor_id', $this->vendor_id)
            ->where('store_id', $this->store_id)
            ->where('title', $this->title)
            ->when($this->brand, fn ($query) => $query->where('brand', $this->brand))
            ->when($this->model, fn ($query) => $query->where('model', $this->model))
            ->whereNotNull('images')
            ->where('images', '!=', '[]')
            ->oldest('id')
            ->value('images');

        if (! $rawImages) {
            return [];
        }

        $images = is_array($rawImages) ? $rawImages : ($this->fromJson($rawImages) ?: []);

        return collect($images)
            ->map(fn ($image) => $this->resolveImagePath($image))
            ->filter()
            ->values()
            ->all();
    }

    public function getStatusLabelAttribute(): string
    {
        if ($this->isLive()) {
            return __('Live Auction');
        }

        if ($this->isScheduled()) {
            return __('Upcoming');
        }

        if ($this->isClosed()) {
            return __('Closed');
        }

        return __(str($this->status)->headline()->toString());
    }

    public function getStatusBadgeClassAttribute(): string
    {
        if ($this->isLive()) {
            return 'live';
        }

        if ($this->isScheduled()) {
            return 'scheduled';
        }

        if ($this->isClosed()) {
            return 'closed';
        }

        return 'draft';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function isScheduled(): bool
    {
        return $this->status === 'scheduled' && $this->start_time && Carbon::now()->lessThan($this->start_time);
    }

    public function isUpcoming(): bool
    {
        return $this->start_time && Carbon::now()->lessThan($this->start_time);
    }

    public function isLive(): bool
    {
        $now = Carbon::now();

        return $this->status === 'published'
            && $this->end_time
            && (! $this->start_time || $now->greaterThanOrEqualTo($this->start_time))
            && $now->lessThan($this->end_time);
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed' || ($this->end_time && Carbon::now()->greaterThan($this->end_time));
    }

    public function isEnded(): bool
    {
        return $this->end_time && Carbon::now()->greaterThanOrEqualTo($this->end_time);
    }

    public function isWaitingForResult(): bool
    {
        return $this->isEnded() && ! $this->winner_customer_id;
    }

    public function hasBids(): bool
    {
        if (array_key_exists('bids_count', $this->attributes)) {
            return (int) $this->attributes['bids_count'] > 0;
        }

        return $this->relationLoaded('bids') ? $this->bids->isNotEmpty() : $this->bids()->exists();
    }

    public function currentBid(): ?AuctionBid
    {
        return $this->bids()
            ->orderByDesc('amount')
            ->orderBy('created_at')
            ->orderBy('id')
            ->first();
    }

    public function currentBidAmount(): float
    {
        return (float) ($this->bids()->max('amount') ?: $this->starting_bid);
    }

    public function getMyBid(int|string|null $customerId): ?AuctionBid
    {
        return $this->getBidFrom($customerId);
    }

    public function getBidFrom(int|string|null $customerId): ?AuctionBid
    {
        if (! $customerId) {
            return null;
        }

        if ($this->relationLoaded('bids')) {
            return $this->bids
                ->where('customer_id', (int) $customerId)
                ->where('auction_id', $this->getKey())
                ->sortByDesc('created_at')
                ->first();
        }

        return $this->bids()
            ->where('customer_id', $customerId)
            ->latest()
            ->first();
    }

    public function hasBidFrom(int|string|null $customerId): bool
    {
        if (! $customerId) {
            return false;
        }

        if ($this->relationLoaded('bids')) {
            return $this->bids
                ->where('customer_id', (int) $customerId)
                ->where('auction_id', $this->getKey())
                ->isNotEmpty();
        }

        return $this->bids()->where('customer_id', $customerId)->exists();
    }

    public function minimumNextBid(): float
    {
        $highestBid = $this->bids()->max('amount');

        return (float) ($highestBid ? $highestBid + 0.01 : $this->starting_bid);
    }

    public function canVendorEditCriticalFields(): bool
    {
        return in_array($this->status, ['draft', 'published', 'scheduled'])
            && ! $this->isClosed()
            && ! $this->hasBids();
    }

    public function canVendorDelete(): bool
    {
        return ! $this->hasBids();
    }

    public function canChooseWinner(): bool
    {
        return ! $this->winner_customer_id
            && $this->hasBids()
            && $this->end_time
            && $this->end_time->lessThanOrEqualTo(Carbon::now());
    }

    public function isWonBy(int|string|null $customerId): bool
    {
        return $customerId && (int) $this->winner_customer_id === (int) $customerId;
    }

    public function customerDisplayStatus(int|string|null $customerId): string
    {
        if ($this->isWonBy($customerId)) {
            return 'won';
        }

        if ($this->isWaitingForResult()) {
            return $this->hasBidFrom($customerId) ? 'waiting' : 'closed';
        }

        if ($this->isUpcoming()) {
            return 'upcoming';
        }

        if ($this->isLive()) {
            return $this->hasBidFrom($customerId) ? 'pending' : 'live';
        }

        return 'closed';
    }

    public function canCustomerBid(?Customer $customer): bool
    {
        return $this->canBid($customer) && ! $this->hasBidFrom($customer?->getKey());
    }

    public function isVisibleToCustomers(): bool
    {
        if ($this->isDraft()) {
            return false;
        }

        if ($this->isPublished()) {
            return $this->isLive() || $this->isClosed();
        }

        if ($this->status === 'scheduled') {
            return ! $this->isClosed();
        }

        return $this->isClosed();
    }

    public function canBid(?Customer $customer): bool
    {
        if (! $customer || ! $this->isLive()) {
            return false;
        }

        if ((int) $this->vendor_id === (int) $customer->getKey()) {
            return false;
        }

        if ($customer->is_vendor && $this->store_id && (int) $customer->store?->id === (int) $this->store_id) {
            return false;
        }

        return true;
    }

    public function notifyAuctionEnded(): void
    {
        $this->bids()
            ->select('customer_id')
            ->distinct()
            ->each(function (AuctionBid $bid): void {
                AuctionNotification::query()->firstOrCreate([
                    'customer_id' => $bid->customer_id,
                    'auction_id' => $this->getKey(),
                    'type' => 'auction_ended',
                ], [
                    'title' => __('Auction ended'),
                    'message' => __('Auction ended for ":title". Waiting for winner allocation.', ['title' => $this->title]),
                ]);
            });
    }

    public function notifyWinnerSelected(string $type = 'winner_selected'): void
    {
        if (! $this->winner_customer_id) {
            return;
        }

        $this->bids()
            ->select('customer_id')
            ->distinct()
            ->each(function (AuctionBid $bid) use ($type): void {
                $won = (int) $bid->customer_id === (int) $this->winner_customer_id;

                AuctionNotification::query()->firstOrCreate([
                    'customer_id' => $bid->customer_id,
                    'auction_id' => $this->getKey(),
                    'type' => $won ? 'auction_won' : 'auction_lost',
                ], [
                    'title' => $won ? __('Auction won') : __('Auction result declared'),
                    'message' => $won
                        ? __('Congratulations! You won ":title".', ['title' => $this->title])
                        : __('Auction result declared for ":title". You did not win this auction.', ['title' => $this->title]),
                ]);

                AuctionNotification::query()->firstOrCreate([
                    'customer_id' => $bid->customer_id,
                    'auction_id' => $this->getKey(),
                    'type' => $type,
                ], [
                    'title' => $type === 'auto_winner_selected' ? __('Automatic winner selected') : __('Winner selected'),
                    'message' => __('Winner has been selected for ":title".', ['title' => $this->title]),
                ]);
            });
    }

    public function scopeWithCustomerBid(Builder $query, int|string|null $customerId): Builder
    {
        return $query->with([
            'bids' => fn ($query) => $query->where('customer_id', $customerId),
        ]);
    }

    public function selectAutomaticWinner(): ?AuctionBid
    {
        $winningBid = $this->bids()
            ->orderByDesc('amount')
            ->orderBy('created_at')
            ->orderBy('id')
            ->first();

        if (! $winningBid) {
            return null;
        }

        $this->forceFill([
            'winner_customer_id' => $winningBid->customer_id,
            'winning_bid_id' => $winningBid->getKey(),
            'winner_selected_at' => Carbon::now(),
            'status' => 'closed',
        ])->save();

        $this->notifyWinnerSelected('auto_winner_selected');

        return $winningBid;
    }
}
