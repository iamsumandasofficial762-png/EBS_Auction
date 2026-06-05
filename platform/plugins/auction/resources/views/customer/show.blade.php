@extends(Theme::getThemeNamespace() . '::views.ecommerce.customers.master')

@section('title', $auction->title)

@section('content')
    @php
        EcommerceHelper::registerThemeAssets();
        $customer = auth('customer')->user();
        $displayStatus = $auction->customerDisplayStatus($customer?->getKey());
        $canBid = $auction->canCustomerBid($customer);
        $statusLabel = [
            'live' => __('Live Auction'),
            'pending' => __('Bid Placed'),
            'upcoming' => __('Upcoming'),
            'closed' => __('Closed'),
            'waiting' => __('Waiting For Result'),
            'won' => __('Won'),
        ][$displayStatus] ?? $auction->status_label;
    @endphp

    @include('plugins/auction::customer.partials.styles')

    @if (session('success_msg'))
        <div class="alert alert-success">{{ session('success_msg') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="auction-detail">
        <div class="row g-0">
            <div class="col-lg-5">
                <div class="auction-detail__gallery">
                    <img
                        src="{{ $auction->primary_image ? RvMedia::getImageUrl($auction->primary_image, null, false, RvMedia::getDefaultImage()) : RvMedia::getDefaultImage() }}"
                        alt="{{ $auction->title }}"
                    >
                </div>
            </div>
            <div class="col-lg-7">
                <div class="auction-detail__content">
                    <span class="auction-card__badge position-static d-inline-flex">
                        <x-core::icon name="ti ti-gavel" />
                        {{ $statusLabel }}
                    </span>

                    <h2>{{ $auction->title }}</h2>
                    <p class="text-muted">{{ $auction->short_description }}</p>

                    <div class="auction-card__tags mb-4">
                        @if ($auction->condition)
                            <span>{{ __(Str::headline($auction->condition)) }}</span>
                        @endif
                        @if ($auction->brand)
                            <span>{{ $auction->brand }}</span>
                        @endif
                        @if ($auction->model)
                            <span>{{ $auction->model }}</span>
                        @endif
                        @if ($auction->category_id && optional($auction->category)->name)
                            <span>{{ $auction->category->name }}</span>
                        @endif
                    </div>

                    <div class="auction-card__meta mb-4">
                        <div>
                            <span>{{ __('Current bid') }}</span>
                            <strong>{{ format_price($auction->current_bid_amount) }}</strong>
                        </div>
                        <div>
                            <span>{{ __('Minimum bid') }}</span>
                            <strong>{{ format_price($auction->minimum_next_bid) }}</strong>
                        </div>
                        <div>
                            <span>{{ __('Bid increment') }}</span>
                            <strong>{{ (float) $auction->bid_increment > 0 ? format_price($auction->bid_increment) : __('None') }}</strong>
                        </div>
                        <div>
                            <span>{{ __('Starting bid') }}</span>
                            <strong>{{ format_price($auction->starting_bid) }}</strong>
                        </div>
                        <div>
                            <span>{{ __('My bid') }}</span>
                            <strong>{{ $myBid ? format_price($myBid->amount) : __('Not placed') }}</strong>
                        </div>
                        <div>
                            <span>{{ __('Starts') }}</span>
                            <strong>{{ optional($auction->start_time)->translatedFormat('M d, Y H:i') }}</strong>
                        </div>
                        <div>
                            <span>{{ __('Ends') }}</span>
                            <strong>{{ optional($auction->end_time)->translatedFormat('M d, Y H:i') }}</strong>
                        </div>
                        <div>
                            <span>{{ __('Time left') }}</span>
                            <strong data-auction-countdown="{{ optional($auction->end_time)->toIso8601String() }}">
                                {{ $auction->isEnded() ? ($auction->winner_customer_id ? $statusLabel : __('Not allocated yet')) : __('Calculating') }}
                            </strong>
                        </div>
                        <div>
                            <span>{{ __('Result') }}</span>
                            <strong>{{ $auction->winner_customer_id ? ($auction->isWonBy($customer?->getKey()) ? __('Won') : __('Result declared')) : ($auction->isEnded() ? __('Waiting for result') : __('In progress')) }}</strong>
                        </div>
                        @if ($auction->store_id && optional($auction->store)->name)
                            <div>
                                <span>{{ __('Vendor') }}</span>
                                <strong>{{ $auction->store->name }}</strong>
                            </div>
                        @endif
                    </div>

                    <div class="auction-card__actions">
                        @if ($canBid)
                            <button class="auction-btn auction-btn--primary" type="button" data-bs-toggle="modal" data-bs-target="#auction-bid-modal-{{ $auction->getKey() }}">
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
    </div>

    <div class="auction-detail__description">
        <h3 class="h4 mb-3">{{ __('Full description') }}</h3>
        <div>{!! BaseHelper::clean($auction->description) !!}</div>
    </div>

    @if ($canBid)
        @include('plugins/auction::customer.partials.bid-modal', ['auction' => $auction])
    @endif

    @include('plugins/auction::customer.partials.scripts')
@endsection
