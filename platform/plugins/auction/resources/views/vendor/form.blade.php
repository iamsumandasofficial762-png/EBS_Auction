@php
    $disabled = $disabled ?? false;
    $images = implode(PHP_EOL, (array) old('images', $auction->images ?? []));
@endphp

@if ($errors->any())
    <div class="alert alert-danger">
        @foreach ($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif

<form method="POST" action="{{ $action }}">
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="card">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label">{{ __('Title') }}</label>
                    <input class="form-control" name="title" value="{{ old('title', $auction->title) }}" @disabled($disabled) required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">{{ __('Slug') }}</label>
                    <input class="form-control" name="slug" value="{{ old('slug', $auction->slug) }}" @disabled($disabled)>
                </div>
                <div class="col-12">
                    <label class="form-label">{{ __('Description') }}</label>
                    <textarea class="form-control" name="description" rows="5" @disabled($disabled)>{{ old('description', $auction->description) }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">{{ __('Images') }}</label>
                    <textarea class="form-control" name="images" rows="3" placeholder="{{ __('One image path or URL per line') }}" @disabled($disabled)>{{ $images }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('Category') }}</label>
                    <select class="form-control" name="category_id" @disabled($disabled)>
                        <option value="">{{ __('None') }}</option>
                        @foreach ($categories as $id => $name)
                            <option value="{{ $id }}" @selected((string) old('category_id', $auction->category_id) === (string) $id)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('Status') }}</label>
                    <select class="form-control" name="status" @disabled($disabled)>
                        @foreach (['draft' => __('Draft'), 'scheduled' => __('Scheduled'), 'live' => __('Live'), 'closed' => __('Closed'), 'cancelled' => __('Cancelled')] as $value => $label)
                            <option value="{{ $value }}" @selected(old('status', $auction->status) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('Starting bid') }}</label>
                    <input class="form-control" name="starting_bid" type="number" step="0.01" min="0" value="{{ old('starting_bid', $auction->starting_bid) }}" @disabled($disabled) required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('Bid increment') }}</label>
                    <input class="form-control" name="bid_increment" type="number" step="0.01" min="0.01" value="{{ old('bid_increment', $auction->bid_increment) }}" @disabled($disabled) required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('Start time') }}</label>
                    <input class="form-control" name="start_time" type="datetime-local" value="{{ old('start_time', optional($auction->start_time)->format('Y-m-d\\TH:i')) }}" @disabled($disabled) required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('End time') }}</label>
                    <input class="form-control" name="end_time" type="datetime-local" value="{{ old('end_time', optional($auction->end_time)->format('Y-m-d\\TH:i')) }}" @disabled($disabled) required>
                </div>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-between">
            <a href="{{ route('marketplace.vendor.auctions.index') }}" class="btn btn-outline-secondary">{{ __('Back') }}</a>
            <button class="btn btn-primary" type="submit" @disabled($disabled)>{{ __('Save auction') }}</button>
        </div>
    </div>
</form>
