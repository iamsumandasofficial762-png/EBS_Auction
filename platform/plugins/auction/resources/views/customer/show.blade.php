@extends(Theme::getThemeNamespace() . '::views.ecommerce.customers.master')

@section('title', $auction->title)

@section('content')
    @php
        EcommerceHelper::registerThemeAssets();
        $customer = auth('customer')->user();
        $displayStatus = $auction->customerDisplayStatus($customer?->getKey());
        $canBid = $auction->canCustomerBid($customer);
        $imageUrl = $auction->primary_image ? RvMedia::getImageUrl($auction->primary_image, null, false, RvMedia::getDefaultImage()) : RvMedia::getDefaultImage();
        $conditionLabel = $auction->condition ? __(Str::headline($auction->condition)) : null;
        $sku = $auction->sku ?? null;
        $vendorName = $auction->store_id && optional($auction->store)->name ? $auction->store->name : optional($auction->vendor)->name;
        $statusLabel = [
            'live' => __('Live Auction'),
            'pending' => __('Live Auction'),
            'upcoming' => __('Upcoming'),
            'closed' => __('Closed'),
            'waiting' => __('Waiting Result'),
            'won' => __('Won'),
        ][$displayStatus] ?? $auction->status_label;
        $resultLabel = match (true) {
            $auction->isWonBy($customer?->getKey()) => __('Won'),
            $auction->winner_customer_id && ! $auction->isWonBy($customer?->getKey()) => __('Lost'),
            $displayStatus === 'waiting' || $auction->isWaitingForResult() => __('Waiting Result'),
            $auction->isEnded() => __('Closed'),
            default => __('In progress'),
        };
    @endphp

    @include('plugins/auction::customer.partials.styles')

    <style>
        .auction-detail-card { background: #fff; border: 1px solid #dce8f8; border-radius: 8px; box-shadow: 0 18px 42px rgba(31, 91, 153, .08); overflow: hidden; }
        .auction-detail-card__grid { display: grid; grid-template-columns: minmax(280px, 42%) 1fr; }
        .auction-detail-gallery { align-items: center; background: linear-gradient(180deg, #fbfdff 0%, #fff 100%); border-right: 1px solid #e2edf9; display: flex; justify-content: center; min-height: 470px; padding: 36px; }
        .auction-detail-gallery img { display: block; max-height: 420px; object-fit: contain; width: 100%; }
        .auction-detail-summary { padding: 34px 36px; }
        .auction-detail-badge { align-items: center; background: #1769c2; border-radius: 999px; color: #fff; display: inline-flex; font-size: 11px; font-weight: 800; gap: 6px; padding: 9px 13px; text-transform: uppercase; }
        .auction-detail-card--upcoming .auction-detail-badge { background: #d99613; }
        .auction-detail-card--closed .auction-detail-badge, .auction-detail-card--waiting .auction-detail-badge { background: #5e6b61; }
        .auction-detail-card--won .auction-detail-badge { background: #0b6fcb; }
        .auction-detail-title { color: #081827; font-size: 32px; font-weight: 800; line-height: 1.18; margin: 16px 0 12px; }
        .auction-detail-short { color: #596474; font-size: 15px; line-height: 1.65; margin: 0 0 18px; }
        .auction-detail-tags { display: flex; flex-wrap: wrap; gap: 7px; margin-bottom: 22px; }
        .auction-detail-tag { background: #f6fbff; border: 1px solid #7db5f1; border-radius: 999px; color: #1769c2; font-size: 11px; font-weight: 800; padding: 6px 10px; text-transform: uppercase; }
        .auction-detail-info-grid { border-top: 1px solid #e4edf9; display: grid; gap: 12px; grid-template-columns: repeat(2, minmax(0, 1fr)); padding-top: 22px; }
        .auction-detail-info-item { background: #fbfdff; border: 1px solid #dce8f8; border-radius: 8px; padding: 14px; }
        .auction-detail-info-item span { color: #68706b; display: block; font-size: 10px; font-weight: 800; letter-spacing: .03em; margin-bottom: 5px; text-transform: uppercase; }
        .auction-detail-info-item strong { color: #1769c2; display: block; font-size: 17px; font-weight: 800; line-height: 1.3; }
        .auction-detail-actions { display: grid; gap: 10px; grid-template-columns: 1fr 1fr; margin-top: 24px; }
        .auction-description-card { background: #fff; border: 1px solid #dce8f8; border-radius: 8px; box-shadow: 0 12px 28px rgba(31, 91, 153, .06); margin-top: 22px; padding: 28px; }
        .auction-description-card h3 { color: #10233f; font-size: 24px; font-weight: 800; margin: 0 0 14px; }
        .auction-description-card__body { color: #465363; font-size: 15px; line-height: 1.75; }
        .auction-description-card__body p:last-child { margin-bottom: 0; }
        @media (max-width: 991px) {
            .auction-detail-card__grid { grid-template-columns: 1fr; }
            .auction-detail-gallery { border-bottom: 1px solid #e2edf9; border-right: 0; min-height: 320px; }
        }
        @media (max-width: 575px) {
            .auction-detail-gallery, .auction-detail-summary, .auction-description-card { padding: 20px; }
            .auction-detail-title { font-size: 25px; }
            .auction-detail-info-grid, .auction-detail-actions { grid-template-columns: 1fr; }
        }
    </style>

    @if (session('success_msg'))
        <div class="alert alert-success">{{ session('success_msg') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="auction-detail-card auction-detail-card--{{ $displayStatus }}">
        <div class="auction-detail-card__grid">
            <div class="auction-detail-gallery">
                <img src="{{ $imageUrl }}" alt="{{ $auction->title }}">
            </div>
            <div class="auction-detail-summary">
                <span class="auction-detail-badge">
                    <x-core::icon name="ti ti-gavel" />
                    {{ $statusLabel }}
                </span>

                <h2 class="auction-detail-title">{{ $auction->title }}</h2>
                <p class="auction-detail-short">{{ $auction->short_description }}</p>

                <div class="auction-detail-tags">
                    @if ($conditionLabel)
                        <span class="auction-detail-tag">{{ $conditionLabel }}</span>
                    @endif
                    @if ($auction->brand)
                        <span class="auction-detail-tag">{{ $auction->brand }}</span>
                    @endif
                    @if ($auction->model)
                        <span class="auction-detail-tag">{{ $auction->model }}</span>
                    @endif
                    @if ($sku)
                        <span class="auction-detail-tag">{{ $sku }}</span>
                    @endif
                    @if ($auction->category_id && optional($auction->category)->name)
                        <span class="auction-detail-tag">{{ $auction->category->name }}</span>
                    @endif
                </div>

                <div class="auction-detail-info-grid">
                    <div class="auction-detail-info-item">
                        <span>{{ __('Set Amount') }}</span>
                        <strong>{{ format_price($auction->starting_bid) }}</strong>
                    </div>
                    <div class="auction-detail-info-item">
                        <span>{{ __('Closing Date & Time') }}</span>
                        <strong>{{ optional($auction->end_time)->format('M d, Y h:i A') ?: __('N/A') }}</strong>
                    </div>
                    <div class="auction-detail-info-item">
                        <span>{{ __('Time Left') }}</span>
                        <strong data-auction-countdown="{{ optional($auction->end_time)->toIso8601String() }}">
                            {{ $auction->isEnded() ? __('Closed') : __('Calculating') }}
                        </strong>
                    </div>
                    <div class="auction-detail-info-item">
                        <span>{{ __('Status') }}</span>
                        <strong>{{ $statusLabel }}</strong>
                    </div>
                    <div class="auction-detail-info-item">
                        <span>{{ __('My Bid') }}</span>
                        <strong>{{ $myBid ? format_price($myBid->amount) : __('Not placed') }}</strong>
                    </div>
                    <div class="auction-detail-info-item">
                        <span>{{ __('Result') }}</span>
                        <strong>{{ $resultLabel }}</strong>
                    </div>
                    @if ($vendorName)
                        <div class="auction-detail-info-item">
                            <span>{{ __('Vendor') }}</span>
                            <strong>{{ $vendorName }}</strong>
                        </div>
                    @endif
                    @if ($auction->start_time && $auction->isUpcoming())
                        <div class="auction-detail-info-item">
                            <span>{{ __('Starts At') }}</span>
                            <strong>{{ optional($auction->start_time)->format('M d, Y h:i A') }}</strong>
                        </div>
                    @endif
                </div>

                <div class="auction-detail-actions">
                    @if ($canBid)
                        <button
                            class="auction-btn auction-btn--primary"
                            type="button"
                            data-auction-open-bid
                            data-auction-id="{{ $auction->getKey() }}"
                            data-title="{{ $auction->title }}"
                            data-url="{{ route('auction.customer.bid', $auction) }}"
                            data-minimum-bid="{{ $auction->minimum_next_bid }}"
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
                    <a class="auction-btn auction-btn--outline" href="{{ route('auction.customer.index') }}">
                        {{ __('Back to Auctions') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="auction-description-card">
        <h3>{{ __('Full description') }}</h3>
        <div class="auction-description-card__body">{!! BaseHelper::clean($auction->description) !!}</div>
    </div>

    @if ($canBid)
        @include('plugins/auction::customer.partials.bid-modal', ['auction' => $auction])
    @endif

    @include('plugins/auction::customer.partials.scripts')
@endsection
