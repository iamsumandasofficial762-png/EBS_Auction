@php
    $variant = $variant ?? 'card';
    $images = $auction->images ?? [];

    if (is_string($images)) {
        $decodedImages = json_decode($images, true);
        $images = json_last_error() === JSON_ERROR_NONE ? $decodedImages : [$images];
    }

    if (! is_array($images)) {
        $images = $images ? [$images] : [];
    }

    $imageUrls = collect($images)
        ->filter()
        ->map(fn ($image) => RvMedia::getImageUrl($image, null, false, RvMedia::getDefaultImage()))
        ->filter()
        ->values();

    if ($imageUrls->isEmpty()) {
        $imageUrls = collect([RvMedia::getDefaultImage()]);
    }

    $hasMultipleImages = $imageUrls->count() > 1;
@endphp

<div class="auction-image-slider auction-image-slider--{{ $variant }}" data-auction-slider>
    <div class="auction-slider-track">
        @foreach ($imageUrls as $imageUrl)
            <div class="auction-slider-slide">
                <img src="{{ $imageUrl }}" alt="{{ $auction->title }}">
            </div>
        @endforeach
    </div>

    @if ($hasMultipleImages)
        <button type="button" class="auction-slider-btn auction-slider-btn--prev" data-slider-prev aria-label="{{ __('Previous image') }}">&lsaquo;</button>
        <button type="button" class="auction-slider-btn auction-slider-btn--next" data-slider-next aria-label="{{ __('Next image') }}">&rsaquo;</button>

        <div class="auction-slider-dots">
            @foreach ($imageUrls as $index => $imageUrl)
                <button type="button" data-slider-dot="{{ $index }}" aria-label="{{ __('Show image :number', ['number' => $index + 1]) }}"></button>
            @endforeach
        </div>
    @endif
</div>
