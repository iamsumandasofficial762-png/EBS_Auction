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
        'description',
        'images',
        'category_id',
        'starting_bid',
        'bid_increment',
        'start_time',
        'end_time',
        'status',
        'winner_customer_id',
        'winning_bid_id',
        'winner_selected_at',
        'auto_select_at',
    ];

    protected $casts = [
        'images' => 'array',
        'starting_bid' => 'decimal:2',
        'bid_increment' => 'decimal:2',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'winner_selected_at' => 'datetime',
        'auto_select_at' => 'datetime',
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
        return (float) ($this->bids()->max('amount') ?: $this->starting_bid);
    }

    public function getMinimumNextBidAttribute(): float
    {
        $highestBid = $this->bids()->max('amount');

        return (float) ($highestBid ? $highestBid + $this->bid_increment : $this->starting_bid);
    }

    public function getPrimaryImageAttribute(): ?string
    {
        return $this->images[0] ?? null;
    }

    public function isLive(): bool
    {
        return $this->status === 'live'
            && $this->start_time
            && $this->end_time
            && Carbon::now()->between($this->start_time, $this->end_time);
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed' || ($this->end_time && Carbon::now()->greaterThan($this->end_time));
    }

    public function canBid(Customer $customer): bool
    {
        return $this->isLive() && (int) $this->vendor_id !== (int) $customer->getKey();
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
