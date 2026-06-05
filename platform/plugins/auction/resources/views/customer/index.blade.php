@extends(Theme::getThemeNamespace() . '::views.ecommerce.customers.master')

@section('title', __('Auction'))

@section('content')
    @php
        EcommerceHelper::registerThemeAssets();
        $tabKeys = array_keys($tabs);
        $activeTab = in_array($activeTab, $tabKeys) ? $activeTab : 'live';
    @endphp

    @include('plugins/auction::customer.partials.styles')

    @if (session('success_msg'))
        <div class="alert alert-success">{{ session('success_msg') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="auction-dashboard">
        <div class="auction-dashboard__intro">
            <div>
                <span class="auction-kicker">{{ __('Bidder Dashboard') }}</span>
                <h2>{{ __('Auction items') }}</h2>
                <p>{{ __('Track live lots, upcoming auctions, your bids, and results without exposing private bidder details.') }}</p>
            </div>
        </div>

        <div class="auction-tabs" role="tablist">
            @foreach ($tabs as $key => [$label, $items])
                <a
                    href="{{ route('auction.customer.index', ['tab' => $key]) }}"
                    @class(['auction-tab', 'is-active' => $activeTab === $key])
                >
                    <span>{{ $label }}</span>
                    <strong>{{ $items->count() }}</strong>
                </a>
            @endforeach
        </div>

        @foreach ($tabs as $key => [$label, $items])
            <div @class(['auction-tab-panel', 'd-none' => $activeTab !== $key])>
                @if ($key === 'notifications')
                    @include('plugins/auction::customer.partials.notification-list', ['notifications' => $items])
                @else
                    <div class="auction-grid">
                        @forelse ($items as $auction)
                            @include('plugins/auction::customer.partials.auction-card', ['auction' => $auction])
                        @empty
                            <div class="auction-empty">
                                <x-core::icon name="ti ti-gavel" />
                                <h3>{{ __('No items here yet') }}</h3>
                                <p>{{ __('When auctions match this tab, they will appear here.') }}</p>
                            </div>
                        @endforelse
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    @include('plugins/auction::customer.partials.scripts')
@endsection
