@extends(MarketplaceHelper::viewPath('vendor-dashboard.layouts.master'))

@section('content')
    @include('plugins/auction::vendor.form', [
        'action' => route('marketplace.vendor.auctions.update', $auction),
        'method' => 'PUT',
        'auction' => $auction,
        'categories' => $categories,
    ])
@endsection
