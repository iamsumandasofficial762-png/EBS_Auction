<div class="auction-notifications">
    @forelse ($notifications as $notification)
        <div @class(['auction-notification', 'is-unread' => ! $notification->is_read])>
            <div>
                <span>{{ __(Str::headline($notification->type)) }}</span>
                <h3>{{ $notification->title }}</h3>
                <p>{{ $notification->message }}</p>
                <small>{{ $notification->created_at->diffForHumans() }}</small>
            </div>
            <div class="auction-notification__actions">
                @if ($notification->auction_id)
                    <a class="auction-btn auction-btn--outline" href="{{ route('auction.customer.show', $notification->auction_id) }}">
                        {{ __('View') }}
                    </a>
                @endif
                @if (! $notification->is_read)
                    <form method="POST" action="{{ route('auction.customer.notifications.read', $notification) }}">
                        @csrf
                        <button class="auction-btn auction-btn--primary" type="submit">{{ __('Mark read') }}</button>
                    </form>
                @endif
            </div>
        </div>
    @empty
        <div class="auction-empty">
            <x-core::icon name="ti ti-bell" />
            <h3>{{ __('No notifications') }}</h3>
            <p>{{ __('Auction result updates will appear here.') }}</p>
        </div>
    @endforelse
</div>
