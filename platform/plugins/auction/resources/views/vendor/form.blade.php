@php
    $canEditCriticalFields = $auction->exists ? $auction->canVendorEditCriticalFields() : true;
    $criticalDisabled = $disabled ?? ! $canEditCriticalFields;
    $images = old('images', $auction->images ?? []);
@endphp

@if ($errors->any())
    <div class="alert alert-danger">
        @foreach ($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif

<form
    method="POST"
    action="{{ $action }}"
    data-ai-suggest-price-url="{{ route('marketplace.vendor.auctions.ai.suggest-price') }}"
    data-ai-generate-description-url="{{ route('marketplace.vendor.auctions.ai.generate-description') }}"
>
    @csrf
    @if ($method !== 'POST')
        @method($method)
    @endif

    <div class="card">
        <div class="card-body">
            <div class="row g-3">
                @if (! $canEditCriticalFields && $auction->exists)
                    <div class="col-12">
                        <div class="alert alert-warning mb-0">
                            {{ __('This auction has bids or is closed. Only descriptions and images can be updated.') }}
                        </div>
                    </div>
                @endif

                <div class="col-md-8">
                    <label class="form-label">{{ __('Product Name') }}</label>
                    <input class="form-control" name="title" value="{{ old('title', $auction->title) }}" @disabled($criticalDisabled) required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">{{ __('Slug') }}</label>
                    <input class="form-control" name="slug" value="{{ old('slug', $auction->slug) }}" @disabled($criticalDisabled)>
                </div>
                <div class="col-12">
                    <label class="form-label">{{ __('Product Images') }}</label>
                    @include(MarketplaceHelper::viewPath('vendor-dashboard.forms.partials.custom-images'), [
                        'name' => 'images',
                        'values' => $images,
                    ])
                </div>
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                        <label class="form-label mb-0">{{ __('Short Description') }}</label>
                        <button class="btn btn-sm btn-outline-primary" type="button" data-auction-ai-generate-description>
                            {{ __('AI Generate Description') }}
                        </button>
                    </div>
                    <textarea class="form-control" name="short_description" rows="3" required>{{ old('short_description', $auction->short_description) }}</textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">{{ __('Full Description') }}</label>
                    {!! render_editor('description', old('description', $auction->description), true, ['required' => true]) !!}
                    <div class="alert alert-info mt-3 d-none" data-auction-ai-description-result>
                        <div class="fw-semibold mb-2">{{ __('AI generated description') }}</div>
                        <div class="mb-2">
                            <small class="text-muted d-block">{{ __('Short Description') }}</small>
                            <div data-auction-ai-short-description></div>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block">{{ __('Full Description') }}</small>
                            <div data-auction-ai-full-description></div>
                        </div>
                        <button class="btn btn-sm btn-primary" type="button" data-auction-ai-use-description>{{ __('Use Description') }}</button>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('Category') }}</label>
                    <select class="form-control" name="category_id" @disabled($criticalDisabled)>
                        <option value="">{{ __('None') }}</option>
                        @foreach ($categories as $id => $name)
                            <option value="{{ $id }}" @selected((string) old('category_id', $auction->category_id) === (string) $id)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">{{ __('Condition') }}</label>
                    <select class="form-control" name="condition" @disabled($criticalDisabled) required>
                        @foreach (['new' => __('New'), 'used' => __('Used'), 'refurbished' => __('Refurbished')] as $value => $label)
                            <option value="{{ $value }}" @selected(old('condition', $auction->condition) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">{{ __('Brand') }}</label>
                    <input class="form-control" name="brand" maxlength="150" value="{{ old('brand', $auction->brand) }}" @disabled($criticalDisabled)>
                </div>
                <div class="col-md-4">
                    <label class="form-label">{{ __('Model') }}</label>
                    <input class="form-control" name="model" maxlength="150" value="{{ old('model', $auction->model) }}" @disabled($criticalDisabled)>
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('Status') }}</label>
                    <select class="form-control" name="status" @disabled($criticalDisabled) required>
                        @foreach (['draft' => __('Draft'), 'published' => __('Published'), 'scheduled' => __('Scheduled'), 'closed' => __('Closed')] as $value => $label)
                            <option value="{{ $value }}" @selected(old('status', $auction->status) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <div class="d-flex justify-content-between align-items-center gap-2 mb-2">
                        <label class="form-label mb-0">{{ __('Starting bid') }}</label>
                        @if (! $criticalDisabled)
                            <button class="btn btn-sm btn-outline-primary" type="button" data-auction-ai-suggest-price>
                                {{ __('AI Suggest Price') }}
                            </button>
                        @endif
                    </div>
                    <input class="form-control" name="starting_bid" type="number" step="0.01" min="1" value="{{ old('starting_bid', $auction->starting_bid) }}" @disabled($criticalDisabled) required>
                    <div class="alert alert-info mt-3 d-none" data-auction-ai-price-result>
                        <div class="fw-semibold mb-1">{{ __('AI suggested starting price') }}</div>
                        <div class="mb-1">{{ __('Suggested starting bid') }}: <strong data-auction-ai-price></strong></div>
                        <div class="mb-1">{{ __('Market range') }}: <span data-auction-ai-market-range></span></div>
                        <div class="mb-1">{{ __('Confidence') }}: <span data-auction-ai-confidence></span></div>
                        <div class="small text-muted mb-3" data-auction-ai-reason></div>
                        <button class="btn btn-sm btn-primary" type="button" data-auction-ai-use-price>{{ __('Use This Price') }}</button>
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('Start time') }}</label>
                    <input class="form-control" name="start_time" type="datetime-local" value="{{ old('start_time', optional($auction->start_time)->format('Y-m-d\\TH:i')) }}" @disabled($criticalDisabled) required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('End time') }}</label>
                    <input class="form-control" name="end_time" type="datetime-local" value="{{ old('end_time', optional($auction->end_time)->format('Y-m-d\\TH:i')) }}" @disabled($criticalDisabled) required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">{{ __('Auto Winner Delay') }}</label>
                    <input class="form-control" name="auto_winner_delay_hours" type="number" min="1" max="168" value="{{ old('auto_winner_delay_hours', $auction->auto_winner_delay_hours ?: 8) }}" @disabled($criticalDisabled) required>
                </div>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-between">
            <a href="{{ route('marketplace.vendor.auctions.index') }}" class="btn btn-outline-secondary">{{ __('Back') }}</a>
            <button class="btn btn-primary" type="submit" @disabled($disabled ?? false)>{{ __('Save auction') }}</button>
        </div>
    </div>
</form>

@push('footer')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var form = document.querySelector('form[data-ai-suggest-price-url]');

            if (!form) {
                return;
            }

            var state = {
                suggestedPrice: null,
                shortDescription: null,
                fullDescription: null
            };

            var priceButton = form.querySelector('[data-auction-ai-suggest-price]');
            var descriptionButton = form.querySelector('[data-auction-ai-generate-description]');
            var priceResult = form.querySelector('[data-auction-ai-price-result]');
            var descriptionResult = form.querySelector('[data-auction-ai-description-result]');

            var getField = function (name) {
                return form.querySelector('[name="' + name + '"]');
            };

            var getValue = function (name) {
                var field = getField(name);

                return field ? field.value : '';
            };

            var getSelectedText = function (name) {
                var field = getField(name);

                if (!field || !field.options || field.selectedIndex < 0) {
                    return '';
                }

                return field.options[field.selectedIndex].text || '';
            };

            var getImage = function () {
                var image = form.querySelector('input[name="images[]"]');

                return image ? image.value : '';
            };

            var payload = function () {
                return {
                    title: getValue('title'),
                    image: getImage(),
                    brand: getValue('brand'),
                    model: getValue('model'),
                    condition: getValue('condition'),
                    category: getSelectedText('category_id')
                };
            };

            var setLoading = function (button, loading) {
                if (!button) {
                    return;
                }

                if (loading) {
                    button.dataset.originalText = button.textContent;
                    button.textContent = '{{ __('AI is thinking...') }}';
                    button.disabled = true;
                    return;
                }

                button.textContent = button.dataset.originalText || button.textContent;
                button.disabled = false;
            };

            var showError = function (message) {
                if (window.Botble && Botble.showError) {
                    Botble.showError(message);
                    return;
                }

                alert(message);
            };

            var postJson = function (url, data) {
                return fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(data)
                }).then(function (response) {
                    return response.json().then(function (json) {
                        if (!response.ok || !json.success) {
                            throw new Error(json.message || '{{ __('AI service is temporarily unavailable.') }}');
                        }

                        return json;
                    });
                });
            };

            var setDescription = function (value) {
                var textarea = getField('description');

                if (textarea) {
                    textarea.value = value;
                    textarea.dispatchEvent(new Event('change', { bubbles: true }));
                }

                if (window.CKEDITOR && CKEDITOR.instances && CKEDITOR.instances.description) {
                    CKEDITOR.instances.description.setData(value);
                    return;
                }

                if (window.tinymce && tinymce.get('description')) {
                    tinymce.get('description').setContent(value);
                    return;
                }

                var editable = form.querySelector('.ck-editor__editable');

                if (editable && editable.ckeditorInstance) {
                    editable.ckeditorInstance.setData(value);
                }
            };

            if (priceButton) {
                priceButton.addEventListener('click', function () {
                    setLoading(priceButton, true);
                    priceResult && priceResult.classList.add('d-none');

                    postJson(form.dataset.aiSuggestPriceUrl, payload())
                        .then(function (json) {
                            var data = json.data;
                            state.suggestedPrice = data.suggested_starting_bid;

                            form.querySelector('[data-auction-ai-price]').textContent = '₹' + data.suggested_starting_bid;
                            form.querySelector('[data-auction-ai-market-range]').textContent = '₹' + data.market_price_min + ' - ₹' + data.market_price_max;
                            form.querySelector('[data-auction-ai-confidence]').textContent = data.confidence;
                            form.querySelector('[data-auction-ai-reason]').textContent = data.reason;
                            priceResult.classList.remove('d-none');
                        })
                        .catch(function (error) {
                            showError(error.message);
                        })
                        .finally(function () {
                            setLoading(priceButton, false);
                        });
                });
            }

            var usePriceButton = form.querySelector('[data-auction-ai-use-price]');

            if (usePriceButton) {
                usePriceButton.addEventListener('click', function () {
                    var startingBid = getField('starting_bid');

                    if (startingBid && state.suggestedPrice !== null) {
                        startingBid.value = state.suggestedPrice;
                        startingBid.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                });
            }

            if (descriptionButton) {
                descriptionButton.addEventListener('click', function () {
                    setLoading(descriptionButton, true);
                    descriptionResult && descriptionResult.classList.add('d-none');

                    postJson(form.dataset.aiGenerateDescriptionUrl, payload())
                        .then(function (json) {
                            var data = json.data;
                            state.shortDescription = data.short_description;
                            state.fullDescription = data.full_description;

                            form.querySelector('[data-auction-ai-short-description]').textContent = data.short_description;
                            form.querySelector('[data-auction-ai-full-description]').textContent = data.full_description;
                            descriptionResult.classList.remove('d-none');
                        })
                        .catch(function (error) {
                            showError(error.message);
                        })
                        .finally(function () {
                            setLoading(descriptionButton, false);
                        });
                });
            }

            var useDescriptionButton = form.querySelector('[data-auction-ai-use-description]');

            if (useDescriptionButton) {
                useDescriptionButton.addEventListener('click', function () {
                    var shortDescription = getField('short_description');

                    if (shortDescription && state.shortDescription !== null) {
                        shortDescription.value = state.shortDescription;
                        shortDescription.dispatchEvent(new Event('change', { bubbles: true }));
                    }

                    if (state.fullDescription !== null) {
                        setDescription(state.fullDescription);
                    }
                });
            }
        });
    </script>
@endpush
