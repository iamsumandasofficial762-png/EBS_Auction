@extends(EcommerceHelper::viewPath('customers.master'))

@section('title', __('Auction'))

@section('content')
    <style>
        .auction-grid { display: grid; gap: 20px; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); }
        .auction-card { border: 1px solid #e5e7eb; border-radius: 8px; overflow: hidden; background: #fff; height: 100%; }
        .auction-card__image { aspect-ratio: 4 / 3; background: #f3f4f6; overflow: hidden; }
        .auction-card__image img { height: 100%; object-fit: cover; width: 100%; }
        .auction-badge { border-radius: 4px; color: #fff; display: inline-flex; font-size: 11px; font-weight: 700; padding: 4px 8px; text-transform: uppercase; }
        .auction-badge--live { background: #16875b; }
        .auction-badge--scheduled { background: #f5a400; color: #111; }
        .auction-badge--closed { background: #6b7280; }
        .auction-meta { display: grid; gap: 8px; grid-template-columns: 1fr 1fr; }
        .auction-meta span { color: #6b7280; display: block; font-size: 12px; }
        .auction-meta strong { color: #111827; font-size: 14px; }
    </style>

    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h3 class="mb-1">{{ __('Auction') }}</h3>
            <p class="text-muted mb-0">{{ __('Browse live, upcoming, and recently closed auction items.') }}</p>
        </div>
    </div>

    @if (session('success_msg'))
        <div class="alert alert-success">{{ session('success_msg') }}</div>
    @endif

    <div class="auction-grid">
        @forelse ($auctions as $auction)
            <div class="auction-card">
                <div class="auction-card__image">
                    @if ($auction->primary_image)
                        <img src="{{ RvMedia::getImageUrl($auction->primary_image, null, false, RvMedia::getDefaultImage()) }}" alt="{{ $auction->title }}">
                    @else
                        <img src="{{ RvMedia::getDefaultImage() }}" alt="{{ $auction->title }}">
                    @endif
                </div>
                <div class="p-3">
                    <span class="auction-badge auction-badge--{{ $auction->status }}">
                        {{ $auction->status === 'live' ? __('Live Auction') : __(Str::headline($auction->status)) }}
                    </span>
                    <h4 class="h6 mt-3 mb-2">{{ $auction->title }}</h4>
                    <p class="text-muted small mb-3">{{ Str::limit(strip_tags($auction->description), 90) }}</p>
                    <div class="auction-meta mb-3">
                        <div><span>{{ __('Current bid') }}</span><strong>{{ format_price($auction->current_bid_amount) }}</strong></div>
                        <div><span>{{ __('Time left') }}</span><strong data-auction-countdown="{{ optional($auction->end_time)->toIso8601String() }}">{{ $auction->isClosed() ? __('Closed') : __('Calculating') }}</strong></div>
                        <div><span>{{ __('Starting bid') }}</span><strong>{{ format_price($auction->starting_bid) }}</strong></div>
                        <div><span>{{ __('Bid increment') }}</span><strong>{{ format_price($auction->bid_increment) }}</strong></div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('auction.customer.show', $auction) }}" class="btn btn-outline-secondary btn-sm flex-fill">{{ __('View Details') }}</a>
                        <a href="{{ route('auction.customer.show', $auction) }}#place-bid" class="btn btn-primary btn-sm flex-fill @if (! $auction->canBid(auth('customer')->user())) disabled @endif">{{ __('Place Bid') }}</a>
                    </div>
                </div>
            </div>
        @empty
            <div class="alert alert-info mb-0">{{ __('No auction items are available yet.') }}</div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $auctions->links() }}
    </div>

    <div class="mt-5">
        <h4 class="h5 mb-3">{{ __('My recent bids') }}</h4>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>{{ __('Auction') }}</th>
                        <th>{{ __('Amount') }}</th>
                        <th>{{ __('Date') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($myBids as $bid)
                        <tr>
                            <td><a href="{{ route('auction.customer.show', $bid->auction) }}">{{ $bid->auction->title }}</a></td>
                            <td>{{ format_price($bid->amount) }}</td>
                            <td>{{ $bid->created_at->translatedFormat('M d, Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3">{{ __('You have not placed any bids yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script>
        document.querySelectorAll('[data-auction-countdown]').forEach(function (element) {
            var end = new Date(element.dataset.auctionCountdown).getTime();
            var render = function () {
                var distance = end - Date.now();
                if (distance <= 0) {
                    element.textContent = '{{ __('Closed') }}';
                    return;
                }
                var days = Math.floor(distance / 86400000);
                var hours = Math.floor((distance % 86400000) / 3600000);
                var minutes = Math.floor((distance % 3600000) / 60000);
                element.textContent = days ? days + 'd ' + hours + 'h' : hours + 'h ' + minutes + 'm';
            };
            render();
            setInterval(render, 60000);
        });
    </script>
@endsection
