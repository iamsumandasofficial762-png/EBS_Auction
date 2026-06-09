@extends(MarketplaceHelper::viewPath('vendor-dashboard.layouts.master'))

@section('content')
    <style>
        .auction-bidders-table {
            border-collapse: separate;
            border-spacing: 0 10px;
        }

        .auction-bidders-table thead th {
            border: 0;
            color: #64748b;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: .04em;
            padding: 0 14px 6px;
            text-transform: uppercase;
        }

        .auction-bidders-table tbody tr {
            background: #fff;
            box-shadow: 0 8px 24px rgba(15, 23, 42, .06);
        }

        .auction-bidders-table tbody td {
            border-bottom: 1px solid #e6edf6;
            border-top: 1px solid #e6edf6;
            padding: 16px 14px;
            vertical-align: middle;
        }

        .auction-bidders-table tbody td:first-child {
            border-left: 1px solid #e6edf6;
            border-radius: 8px 0 0 8px;
        }

        .auction-bidders-table tbody td:last-child {
            border-radius: 0 8px 8px 0;
            border-right: 1px solid #e6edf6;
        }

        .auction-bidders-table .auction-bidder-row--winner {
            background: #effaf3;
            box-shadow: 0 10px 26px rgba(22, 163, 74, .14);
        }

        .auction-bidders-table .auction-bidder-row--winner td {
            border-color: #bbf7d0;
        }

        .auction-bidder-name {
            align-items: center;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .auction-winner-pill {
            background: #16a34a;
            border-radius: 999px;
            color: #fff;
            font-size: 11px;
            font-weight: 800;
            padding: 5px 10px;
            text-transform: uppercase;
        }
    </style>

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
        <table class="table align-middle auction-bidders-table">
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
                    @php
                        $isWinner = $auction->winner_customer_id
                            && (int) $auction->winner_customer_id === (int) $bid->customer_id;
                    @endphp
                    <tr @class(['auction-bidder-row--winner' => $isWinner])>
                        <td>
                            <div class="auction-bidder-name">
                                <strong>{{ $bid->customer->name }}</strong>
                                @if ($isWinner)
                                    <span class="auction-winner-pill">{{ __('Winner') }}</span>
                                @endif
                            </div>
                        </td>
                        <td>{{ format_price($bid->amount) }}</td>
                        <td>{{ $bid->created_at->translatedFormat('M d, Y H:i') }}</td>
                        <td class="text-end">
                            @if ($isWinner)
                                <button class="btn btn-sm btn-primary" type="button">{{ __('Contact') }}</button>
                            @else
                                <form method="POST" action="{{ route('marketplace.vendor.auctions.choose-winner', [$auction, $bid]) }}">
                                    @csrf
                                    <button class="btn btn-sm btn-primary" type="submit" @disabled(! $auction->canChooseWinner())>{{ __('Choose winner') }}</button>
                                </form>
                            @endif
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
