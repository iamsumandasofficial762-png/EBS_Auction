<?php

namespace Botble\Auction\Models;

use Botble\Ecommerce\Models\Customer;
use Botble\Ecommerce\Models\ProductCategory;
use Botble\Marketplace\Models\Store;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        return $this->images[0] ?? null;
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

    public function isLive(): bool
    {
        return in_array($this->status, ['published', 'scheduled'])
            && $this->start_time
            && $this->end_time
            && Carbon::now()->between($this->start_time, $this->end_time);
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed' || ($this->end_time && Carbon::now()->greaterThan($this->end_time));
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

        return $winningBid;
    }
}
