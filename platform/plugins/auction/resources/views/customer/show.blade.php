@extends(EcommerceHelper::viewPath('customers.master'))

@section('title', $auction->title)

@section('content')
    <div class="card border-0 mb-4">
        <div class="row g-0">
            <div class="col-md-5">
                <img class="w-100 h-100" style="object-fit: cover; min-height: 280px;" src="{{ $auction->primary_image ? RvMedia::getImageUrl($auction->primary_image, null, false, RvMedia::getDefaultImage()) : RvMedia::getDefaultImage() }}" alt="{{ $auction->title }}">
            </div>
            <div class="col-md-7">
                <div class="card-body">
                    <span class="badge bg-{{ $auction->status === 'live' ? 'success' : ($auction->status === 'scheduled' ? 'warning' : 'secondary') }}">{{ Str::headline($auction->status) }}</span>
                    <h3 class="mt-3">{{ $auction->title }}</h3>
                    <p class="text-muted">{{ $auction->description }}</p>
                    <div class="row g-3 mb-4">
                        <div class="col-6"><small class="text-muted d-block">{{ __('Current bid') }}</small><strong>{{ format_price($auction->current_bid_amount) }}</strong></div>
                        <div class="col-6"><small class="text-muted d-block">{{ __('Minimum next bid') }}</small><strong>{{ format_price($auction->minimum_next_bid) }}</strong></div>
                        <div class="col-6"><small class="text-muted d-block">{{ __('Starts') }}</small><strong>{{ $auction->start_time->translatedFormat('M d, Y H:i') }}</strong></div>
                        <div class="col-6"><small class="text-muted d-block">{{ __('Ends') }}</small><strong>{{ $auction->end_time->translatedFormat('M d, Y H:i') }}</strong></div>
                    </div>

                    <form id="place-bid" method="POST" action="{{ route('auction.customer.bid', $auction) }}">
                        @csrf
                        <label class="form-label">{{ __('Your bid') }}</label>
                        <div class="input-group">
                            <input class="form-control @error('amount') is-invalid @enderror" name="amount" type="number" step="0.01" min="{{ $auction->minimum_next_bid }}" value="{{ old('amount', $auction->minimum_next_bid) }}" @disabled(! $auction->canBid(auth('customer')->user()))>
                            <button class="btn btn-primary" type="submit" @disabled(! $auction->canBid(auth('customer')->user()))>{{ __('Place Bid') }}</button>
                        </div>
                        @error('amount')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if ($auction->winner_customer_id)
        <div class="alert alert-success">
            {{ __('Winner: :name with :amount', ['name' => $auction->winner->name, 'amount' => format_price($auction->winningBid->amount)]) }}
        </div>
    @endif

    <div class="row g-4">
        <div class="col-md-6">
            <h4 class="h5">{{ __('Your bid history') }}</h4>
            <table class="table table-striped">
                <tbody>
                    @forelse ($myBids as $bid)
                        <tr>
                            <td>{{ format_price($bid->amount) }}</td>
                            <td class="text-end">{{ $bid->created_at->translatedFormat('M d, Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td>{{ __('You have not bid on this auction yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="col-md-6">
            <h4 class="h5">{{ __('Top bidders') }}</h4>
            <table class="table table-striped">
                <tbody>
                    @forelse ($auction->bids->sortByDesc('amount')->take(10) as $bid)
                        <tr>
                            <td>{{ $bid->customer->name }}</td>
                            <td class="text-end">{{ format_price($bid->amount) }}</td>
                        </tr>
                    @empty
                        <tr><td>{{ __('No bids yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
