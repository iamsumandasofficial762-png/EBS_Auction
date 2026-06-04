@extends(MarketplaceHelper::viewPath('vendor-dashboard.layouts.master'))

@section('content')
    @include('plugins/auction::vendor.form', [
        'action' => route('marketplace.vendor.auctions.store'),
        'method' => 'POST',
        'auction' => $auction,
        'categories' => $categories,
    ])
@endsection
