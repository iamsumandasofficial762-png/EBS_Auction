@php
    $customer = auth('customer')->user();
    $myBid = $auction->getMyBid($customer?->getKey());
    $displayStatus = $auction->customerDisplayStatus($customer?->getKey());
    $canBid = $auction->canCustomerBid($customer);
    $statusLabel = [
        'live' => __('Live Auction'),
        'pending' => __('Bid Placed'),
        'upcoming' => __('Upcoming'),
        'closed' => __('Closed'),
        'waiting' => __('Waiting Result'),
        'won' => __('Won'),
    ][$displayStatus] ?? $auction->status_label;
@endphp

<article class="auction-card auction-card--{{ $displayStatus }}">
    <div class="auction-card__media">
        <span class="auction-card__badge">
            <x-core::icon name="ti ti-gavel" />
            {{ $statusLabel }}
        </span>
        <img
            src="{{ $auction->primary_image ? RvMedia::getImageUrl($auction->primary_image, null, false, RvMedia::getDefaultImage()) : RvMedia::getDefaultImage() }}"
            alt="{{ $auction->title }}"
        >
    </div>

    <div class="auction-card__body">
        <h3>{{ $auction->title }}</h3>

        <div class="auction-card__tags">
            @if ($auction->condition)
                <span>{{ __(Str::headline($auction->condition)) }}</span>
            @endif
            @if ($auction->brand)
                <span>{{ $auction->brand }}</span>
            @endif
            @if ($auction->model)
                <span>{{ $auction->model }}</span>
            @endif
        </div>

        <p>{{ Str::limit(strip_tags($auction->short_description), 95) }}</p>

        <div class="auction-card__meta">
            <div>
                <span>{{ __('Current bid') }}</span>
                <strong>{{ format_price($auction->current_bid_amount) }}</strong>
            </div>
            <div>
                <span>{{ $auction->isEnded() ? __('Result') : __('Time left') }}</span>
                <strong data-auction-countdown="{{ optional($auction->end_time)->toIso8601String() }}">
                    {{ $auction->isEnded() ? ($auction->winner_customer_id ? $statusLabel : __('Not allocated yet')) : __('Calculating') }}
                </strong>
            </div>
            <div>
                <span>{{ __('Starting bid') }}</span>
                <strong>{{ format_price($auction->starting_bid) }}</strong>
            </div>
            <div>
                <span>{{ __('My bid') }}</span>
                <strong>{{ $myBid ? format_price($myBid->amount) : __('Not placed') }}</strong>
            </div>
        </div>

        <div class="auction-card__actions">
            @if ($canBid)
                <button
                    class="auction-btn auction-btn--primary"
                    type="button"
                    data-auction-open-bid
                    data-bs-toggle="modal"
                    data-bs-target="#auction-bid-modal-{{ $auction->getKey() }}"
                >
                    <x-core::icon name="ti ti-gavel" />
                    {{ __('Place Bid') }}
                </button>
            @else
                <button class="auction-btn auction-btn--muted" type="button" disabled>
                    {{ $myBid ? __('Bid Placed') : ($auction->isUpcoming() ? __('Upcoming') : __('Closed')) }}
                </button>
            @endif

            <a class="auction-btn auction-btn--outline" href="{{ route('auction.customer.show', $auction) }}">
                <x-core::icon name="ti ti-info-circle" />
                {{ __('View Details') }}
            </a>
        </div>
    </div>
</article>

@if ($canBid)
    @include('plugins/auction::customer.partials.bid-modal', ['auction' => $auction])
@endif
