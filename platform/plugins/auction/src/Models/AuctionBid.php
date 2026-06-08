<?php

namespace Botble\Auction\Models;

use Botble\Ecommerce\Models\Customer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuctionBid extends Model
{
    protected $table = 'auction_bids';

    protected $fillable = [
        'auction_id',
        'auction_item_id',
        'customer_id',
        'user_id',
        'amount',
        'bid_amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'bid_amount' => 'decimal:2',
    ];

    public function auction(): BelongsTo
    {
        return $this->belongsTo(Auction::class, 'auction_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id')->withDefault();
    }
}
