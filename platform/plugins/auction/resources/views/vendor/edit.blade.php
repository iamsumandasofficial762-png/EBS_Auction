@extends(MarketplaceHelper::viewPath('vendor-dashboard.layouts.master'))

@section('content')
    @if ($auction->start_time->lessThanOrEqualTo(now()))
        <div class="alert alert-warning">{{ __('This auction has started, so its item details can no longer be edited.') }}</div>
    @endif

    @include('plugins/auction::vendor.form', [
        'action' => route('marketplace.vendor.auctions.update', $auction),
        'method' => 'PUT',
        'auction' => $auction,
        'categories' => $categories,
        'disabled' => $auction->start_time->lessThanOrEqualTo(now()),
    ])
@endsection
