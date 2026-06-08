@php
    $customer = auth('customer')->user();
    $myBid = $auction->getBidFrom($customer?->getKey());
    $displayStatus = $auction->customerDisplayStatus($customer?->getKey());
    $canBid = $auction->canCustomerBid($customer);
    $imageUrl = $auction->primary_image ? RvMedia::getImageUrl($auction->primary_image, null, false, RvMedia::getDefaultImage()) : RvMedia::getDefaultImage();
    $conditionLabel = $auction->condition ? __(Str::headline($auction->condition)) : null;
    $sku = $auction->sku ?? null;
    $statusLabel = [
        'live' => __('Live Auction'),
        'pending' => __('Live Auction'),
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
            src="{{ $imageUrl }}"
            alt="{{ $auction->title }}"
        >
    </div>

    <div class="auction-card__body">
        <h3>{{ $auction->title }}</h3>

        <div class="auction-card__tags">
            @if ($auction->category_id && optional($auction->category)->name)
                <span>{{ $auction->category->name }}</span>
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
                <span>{{ __('Set Amount') }}</span>
                <strong>{{ format_price($auction->starting_bid) }}</strong>
            </div>
            <div>
                <span>{{ __('Time left') }}</span>
                <strong data-auction-countdown="{{ optional($auction->end_time)->toIso8601String() }}">
                    {{ $auction->isEnded() ? __('Closed') : __('Calculating') }}
                </strong>
            </div>
            <div class="auction-card__closing-date">
                <span>{{ __('Closing Date') }}</span>
                <strong>
                    {{ optional($auction->end_time)->format('M d, Y') ?: __('N/A') }}
                    <small>{{ optional($auction->end_time)->format('h:i A') }}</small>
                </strong>
            </div>
        </div>

        <div class="auction-card__actions">
            @if ($canBid)
                <button
                    class="auction-btn auction-btn--primary"
                    type="button"
                    data-auction-open-bid
                    data-auction-id="{{ $auction->getKey() }}"
                    data-title="{{ $auction->title }}"
                    data-current-bid="{{ $auction->current_bid_amount }}"
                    data-minimum-bid="{{ $auction->minimum_next_bid }}"
                    data-url="{{ route('auction.customer.bid', $auction) }}"
                    data-image="{{ $imageUrl }}"
                    data-set-amount="{{ format_price($auction->starting_bid) }}"
                    data-end-time="{{ optional($auction->end_time)->format('M d, Y h:i A') }}"
                    data-countdown="{{ optional($auction->end_time)->toIso8601String() }}"
                    data-condition="{{ $conditionLabel }}"
                    data-brand="{{ $auction->brand }}"
                    data-model="{{ $auction->model }}"
                    data-sku="{{ $sku }}"
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

            <a
                class="auction-btn auction-btn--outline"
                href="{{ route('auction.customer.show', $auction) }}"
                data-auction-id="{{ $auction->getKey() }}"
                data-title="{{ $auction->title }}"
                data-current-bid="{{ $auction->current_bid_amount }}"
                data-minimum-bid="{{ $auction->minimum_next_bid }}"
                data-url="{{ route('auction.customer.show', $auction) }}"
            >
                <x-core::icon name="ti ti-info-circle" />
                {{ __('View Details') }}
            </a>
        </div>
    </div>
</article>

@if ($canBid)
    @include('plugins/auction::customer.partials.bid-modal', ['auction' => $auction])
@endif
