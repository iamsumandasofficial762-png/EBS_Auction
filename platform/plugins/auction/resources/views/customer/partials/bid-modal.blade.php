<div class="modal fade auction-bid-modal" id="auction-bid-modal-{{ $auction->getKey() }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content" method="POST" action="{{ route('auction.customer.bid', $auction) }}">
            @csrf
            <div class="modal-header">
                <div>
                    <span class="auction-kicker">{{ __('Confirm bid') }}</span>
                    <h5 class="modal-title">{{ $auction->title }}</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
            </div>
            <div class="modal-body">
                <div class="auction-bid-summary">
                    <div>
                        <span>{{ __('Current bid') }}</span>
                        <strong>{{ format_price($auction->current_bid_amount) }}</strong>
                    </div>
                    <div>
                        <span>{{ __('Starting bid') }}</span>
                        <strong>{{ format_price($auction->starting_bid) }}</strong>
                    </div>
                    <div>
                        <span>{{ __('Minimum bid') }}</span>
                        <strong>{{ format_price($auction->minimum_next_bid) }}</strong>
                    </div>
                </div>

                <label class="form-label fw-semibold mt-4" for="auction-bid-amount-{{ $auction->getKey() }}">{{ __('Bid amount') }}</label>
                <input
                    class="form-control"
                    id="auction-bid-amount-{{ $auction->getKey() }}"
                    name="amount"
                    type="number"
                    step="0.01"
                    min="{{ $auction->minimum_next_bid }}"
                    value="{{ old('amount', $auction->minimum_next_bid) }}"
                    required
                >
            </div>
            <div class="modal-footer">
                <button type="button" class="auction-btn auction-btn--outline" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="submit" class="auction-btn auction-btn--primary">{{ __('Confirm Bid') }}</button>
            </div>
        </form>
    </div>
</div>
