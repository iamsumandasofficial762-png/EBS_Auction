<div class="auction-notifications">
    @forelse ($notifications as $notification)
        @php
            $bidderName = auth('customer')->user()?->name;
            $typeLabel = [
                'new_auction' => __('INFO'),
                'auction_live' => __('SUCCESS'),
                'auction_closed' => __('WARNING'),
                'auction_result' => __('INFO'),
                'auction_won' => __('SUCCESS'),
                'auction_lost' => __('INFO'),
            ][$notification->type] ?? __(Str::headline($notification->type));
        @endphp

        <div
            @class(['auction-notification', 'is-unread' => ! $notification->is_read, 'is-read' => $notification->is_read])
            data-notification-card="{{ $notification->getKey() }}"
            data-is-unread="{{ ! $notification->is_read ? '1' : '0' }}"
        >
            <div>
                <span>{{ $typeLabel }}</span>
                <h3>{{ $notification->title }}</h3>
                <p>{{ trim(($bidderName ? $bidderName . ' ' : '') . $notification->message) }}</p>
                @if ($notification->auction_id && optional($notification->auction)->title)
                    <p><strong>{{ $notification->auction->title }}</strong></p>
                @endif
                <small>{{ $notification->created_at->diffForHumans() }}</small>
            </div>
            <div class="auction-notification__actions">
                @if ($notification->auction_id)
                    <a class="auction-btn auction-btn--outline" href="{{ route('auction.customer.show', $notification->auction_id) }}">
                        {{ __('View') }}
                    </a>
                @else
                    <button class="auction-btn auction-btn--muted" type="button" disabled>{{ __('View') }}</button>
                @endif
                <form method="POST" action="{{ route('auction.customer.notifications.read', $notification) }}" data-notification-read-form>
                    @csrf
                    <button
                        @class(['auction-btn', 'auction-btn--primary' => ! $notification->is_read, 'auction-btn--muted is-read-button' => $notification->is_read])
                        type="submit"
                        @disabled($notification->is_read)
                    >
                        {{ $notification->is_read ? __('Read') : __('Mark as Read') }}
                    </button>
                </form>
                <form method="POST" action="{{ route('auction.customer.notifications.delete', $notification) }}" data-notification-delete-form>
                    @csrf
                    @method('DELETE')
                    <button class="auction-btn auction-btn--outline auction-btn--danger" type="submit">{{ __('Delete') }}</button>
                </form>
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
