@extends(MarketplaceHelper::viewPath('vendor-dashboard.layouts.master'))

@section('content')
    <div class="card mb-3">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-1">{{ $auction->title }}</h4>
                <p class="text-muted mb-0">{{ __('Current highest bid: :amount', ['amount' => format_price($auction->current_bid_amount)]) }}</p>
            </div>
            <a href="{{ route('marketplace.vendor.auctions.index') }}" class="btn btn-outline-secondary">{{ __('Back') }}</a>
        </div>
    </div>

    @if ($auction->winner_customer_id)
        <div class="alert alert-success">
            {{ __('Winner: :name with :amount', ['name' => $auction->winner->name, 'amount' => format_price($auction->winningBid->amount)]) }}
        </div>
    @endif

    <div class="table-responsive">
        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>{{ __('Bidder') }}</th>
                    <th>{{ __('Amount') }}</th>
                    <th>{{ __('Placed at') }}</th>
                    <th class="text-end">{{ __('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($bids as $bid)
                    <tr>
                        <td>{{ $bid->customer->name }}<br><small class="text-muted">{{ $bid->customer->email }}</small></td>
                        <td>{{ format_price($bid->amount) }}</td>
                        <td>{{ $bid->created_at->translatedFormat('M d, Y H:i') }}</td>
                        <td class="text-end">
                            <form method="POST" action="{{ route('marketplace.vendor.auctions.choose-winner', [$auction, $bid]) }}">
                                @csrf
                                <button class="btn btn-sm btn-primary" type="submit" @disabled(! $auction->canChooseWinner())>{{ __('Choose winner') }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4">{{ __('No bids have been placed yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $bids->links() }}
@endsection
