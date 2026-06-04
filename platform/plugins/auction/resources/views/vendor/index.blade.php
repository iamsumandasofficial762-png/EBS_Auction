@extends(MarketplaceHelper::viewPath('vendor-dashboard.layouts.master'))

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <p class="text-muted mb-0">{{ __('Manage your auction items, bidders, and winners.') }}</p>
        <a href="{{ route('marketplace.vendor.auctions.create') }}" class="btn btn-primary">
            <x-core::icon name="ti ti-plus" />
            {{ __('Create auction') }}
        </a>
    </div>

    @if (session('success_msg'))
        <div class="alert alert-success">{{ session('success_msg') }}</div>
    @endif
    @if (session('error_msg'))
        <div class="alert alert-danger">{{ session('error_msg') }}</div>
    @endif

    <div class="table-responsive">
        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>{{ __('Title') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Current bid') }}</th>
                    <th>{{ __('Bidders') }}</th>
                    <th>{{ __('Ends') }}</th>
                    <th class="text-end">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($auctions as $auction)
                    <tr>
                        <td>{{ $auction->title }}</td>
                        <td><span class="badge bg-secondary">{{ Str::headline($auction->status) }}</span></td>
                        <td>{{ format_price($auction->current_bid_amount) }}</td>
                        <td>{{ $auction->bids_count }}</td>
                        <td>{{ $auction->end_time->translatedFormat('M d, Y H:i') }}</td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('marketplace.vendor.auctions.bidders', $auction) }}">{{ __('Bidders') }}</a>
                            <a class="btn btn-sm btn-outline-primary @if ($auction->start_time->lessThanOrEqualTo(now())) disabled @endif" href="{{ route('marketplace.vendor.auctions.edit', $auction) }}">{{ __('Edit') }}</a>
                            <form class="d-inline" method="POST" action="{{ route('marketplace.vendor.auctions.destroy', $auction) }}" onsubmit="return confirm('{{ __('Cancel this auction?') }}')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" type="submit">{{ __('Cancel') }}</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6">{{ __('No auction items found.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $auctions->links() }}
@endsection
