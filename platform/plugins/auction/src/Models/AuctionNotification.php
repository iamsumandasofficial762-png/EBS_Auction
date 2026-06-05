<?php

namespace Botble\Auction\Models;

use Botble\Ecommerce\Models\Customer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuctionNotification extends Model
{
    protected $table = 'auction_notifications';

    protected $fillable = [
        'customer_id',
        'auction_id',
        'type',
        'title',
        'message',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function auction(): BelongsTo
    {
        return $this->belongsTo(Auction::class, 'auction_id')->withDefault();
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id')->withDefault();
    }
}
