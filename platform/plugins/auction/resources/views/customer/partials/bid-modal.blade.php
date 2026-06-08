@php
    $imageUrl = $auction->primary_image ? RvMedia::getImageUrl($auction->primary_image, null, false, RvMedia::getDefaultImage()) : RvMedia::getDefaultImage();
    $conditionLabel = $auction->condition ? __(Str::headline($auction->condition)) : null;
    $sku = $auction->sku ?? null;
@endphp

<div class="modal fade auction-bid-modal" id="auction-bid-modal-{{ $auction->getKey() }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content auction-bid-form" method="POST" action="{{ route('auction.customer.bid', $auction) }}">
            @csrf
            <div class="modal-header">
                <div>
                    <span class="auction-kicker">{{ __('Confirm bid') }}</span>
                    <h5 class="modal-title" data-bid-auction-title>{{ $auction->title }}</h5>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
            </div>
            <div class="modal-body">
                <div class="auction-bid-product">
                    <img data-bid-auction-image src="{{ $imageUrl }}" alt="{{ $auction->title }}">
                </div>

                <div class="auction-bid-info">
                    <div>
                        <small>{{ __('Set Amount') }}</small>
                        <strong data-bid-auction-set-amount>{{ format_price($auction->starting_bid) }}</strong>
                    </div>
                    <div>
                        <small>{{ __('Closing Date & Time') }}</small>
                        <strong data-bid-auction-end-time>{{ optional($auction->end_time)->format('M d, Y h:i A') ?: __('N/A') }}</strong>
                    </div>
                    <div>
                        <small>{{ __('Time Left') }}</small>
                        <strong data-bid-auction-time-left data-auction-countdown="{{ optional($auction->end_time)->toIso8601String() }}">
                            {{ $auction->isEnded() ? __('Closed') : __('Calculating') }}
                        </strong>
                    </div>
                </div>

                <div class="auction-bid-tags">
                    @if ($conditionLabel)
                        <span data-bid-auction-condition>{{ $conditionLabel }}</span>
                    @endif
                    @if ($auction->brand)
                        <span data-bid-auction-brand>{{ $auction->brand }}</span>
                    @endif
                    @if ($auction->model)
                        <span data-bid-auction-model>{{ $auction->model }}</span>
                    @endif
                    @if ($sku)
                        <span data-bid-auction-sku>{{ $sku }}</span>
                    @endif
                </div>

                <label class="form-label fw-semibold" for="auction-bid-amount-{{ $auction->getKey() }}">{{ __('Bid amount') }}</label>
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
                <small class="auction-bid-help">{{ __('Enter your bid amount.') }}</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="auction-btn auction-btn--outline" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="submit" class="auction-btn auction-btn--primary">{{ __('Confirm Bid') }}</button>
            </div>
        </form>
    </div>
</div>
