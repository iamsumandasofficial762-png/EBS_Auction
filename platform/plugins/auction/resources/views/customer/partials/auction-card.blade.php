@php
    $customer = auth('customer')->user();
    $myBid = $auction->getBidFrom($customer?->getKey());
    $displayStatus = $auction->customerDisplayStatus($customer?->getKey());
    $canBid = $auction->canCustomerBid($customer);
    $imageUrl = $auction->primary_image ? RvMedia::getImageUrl($auction->primary_image, null, false, RvMedia::getDefaultImage()) : RvMedia::getDefaultImage();
    $conditionLabel = $auction->condition ? __(Str::headline($auction->condition)) : null;
    $sku = $auction->sku ?? null;
    $autoSelectAt = $auction->autoSelectAt();
    $tab = $tab ?? request('tab', 'live');
    $now = \Carbon\Carbon::now(config('app.timezone'));
    $liveStartedAt = $auction->start_time ?: $auction->created_at;
    $liveNowUntil = $liveStartedAt ? $liveStartedAt->copy()->addHour() : null;
    $showLiveNowBadge = $auction->status === 'published'
        && $auction->end_time
        && $auction->end_time->greaterThan($now)
        && $liveStartedAt
        && $now->between($liveStartedAt, $liveNowUntil);
    $statusLabel = [
        'live' => __('Live Auction'),
        'pending' => __('Live Auction'),
        'upcoming' => __('Upcoming'),
        'closed' => __('Closed'),
        'waiting' => __('Waiting Result'),
        'won' => __('Won'),
    ][$displayStatus] ?? $auction->status_label;
    $countdownLabel = __('Closes in');
    $countdownTarget = $auction->end_time;
    $countdownText = null;
    $expiredText = __('Closed');

    if ($tab === 'upcoming' || $auction->status === 'scheduled') {
        $countdownLabel = __('Live starts in');
        $countdownTarget = $auction->start_time;
        $expiredText = __('Starting...');
    } elseif ($tab === 'waiting' || $displayStatus === 'waiting') {
        $countdownLabel = __('Result declear in');
        $countdownTarget = $autoSelectAt;
        $expiredText = __('Selecting winner...');
    } elseif ($tab === 'closed' || $displayStatus === 'closed') {
        $countdownLabel = __('Status');
        $countdownTarget = null;
        $countdownText = __('Closed');
    } elseif ($tab === 'won' || $displayStatus === 'won') {
        $countdownLabel = __('Status');
        $countdownTarget = null;
        $countdownText = __('Won');
    }
@endphp

<article class="auction-card auction-card--{{ $displayStatus }}" data-auction-id="{{ $auction->getKey() }}">
    <div class="auction-card__media">
        <span class="auction-card__badge">
            <x-core::icon name="ti ti-gavel" />
            {{ $statusLabel }}
        </span>
        @if ($showLiveNowBadge)
            <span
                class="auction-live-now-badge"
                data-live-now-until="{{ optional($liveNowUntil)->toIso8601String() }}"
            >
                {{ __('Live Now') }}
            </span>
        @endif
        @include('plugins/auction::customer.partials.image-slider', ['auction' => $auction, 'variant' => 'card'])
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
                <span>{{ $countdownLabel }}</span>
                @if ($countdownTarget)
                    <strong
                        data-countdown-target="{{ optional($countdownTarget)->toIso8601String() }}"
                        data-expired-text="{{ $expiredText }}"
                    >
                        {{ $countdownTarget->isPast() ? $expiredText : __('Calculating') }}
                    </strong>
                @else
                    <strong>{{ $countdownText }}</strong>
                @endif
            </div>
            <div class="auction-card__closing-date">
                <span>{{ __('Closing Date') }}</span>
                <strong>
                    {{ optional($auction->end_time)->format('M d, Y') ?: __('N/A') }}
                    <small>{{ optional($auction->end_time)->format('h:i A') }}</small>
                </strong>
            </div>
        </div>

        <div @class(['auction-card__actions', 'auction-card__actions--single' => ! $canBid])>
            @if ($canBid)
                <button
                    class="auction-btn auction-btn--primary js-place-bid"
                    type="button"
                    data-auction-open-bid
                    data-auction-id="{{ $auction->getKey() }}"
                    data-title="{{ $auction->title }}"
                    data-current-bid="{{ $auction->current_bid_amount }}"
                    data-minimum-bid="{{ $auction->minimum_next_bid }}"
                    data-url="{{ route('auction.customer.bid', $auction) }}"
                    data-image="{{ $imageUrl }}"
                    data-set-amount-raw="{{ $auction->starting_bid }}"
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
