@if ($key === 'notifications')
    @include('plugins/auction::customer.partials.notification-list', ['notifications' => $items])
@else
    <div class="auction-grid">
        @forelse ($items as $auction)
            @include('plugins/auction::customer.partials.auction-card', ['auction' => $auction, 'tab' => $key])
        @empty
            <div class="auction-empty">
                <x-core::icon name="ti ti-gavel" />
                <h3>{{ __('No items here yet') }}</h3>
                <p>{{ __('When auctions match this tab, they will appear here.') }}</p>
            </div>
        @endforelse
    </div>
@endif
