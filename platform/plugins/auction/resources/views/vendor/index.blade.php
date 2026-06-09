@extends(MarketplaceHelper::viewPath('vendor-dashboard.layouts.master'))

@push('header')
    <style>
        .auction-status-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 86px;
            padding: 0.35rem 0.65rem;
            border-radius: 999px;
            font-size: 0.75rem;
            font-weight: 600;
            line-height: 1;
            white-space: nowrap;
        }

        .auction-status-badge.live {
            color: #0f5132;
            background: #d1e7dd;
        }

        .auction-status-badge.scheduled {
            color: #664d03;
            background: #fff3cd;
        }

        .auction-status-badge.closed {
            color: #842029;
            background: #f8d7da;
        }

        .auction-status-badge.draft {
            color: #344054;
            background: #e9ecef;
        }

        .auction-delete-modal {
            position: fixed;
            inset: 0;
            z-index: 99999;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 16px;
        }

        .auction-delete-modal.is-active {
            display: flex;
        }

        .auction-delete-backdrop {
            position: absolute;
            inset: 0;
            background: rgba(7, 17, 28, 0.62);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
        }

        .auction-delete-card {
            position: relative;
            z-index: 1;
            width: min(420px, 100%);
            background: #fff;
            border-radius: 8px;
            padding: 28px;
            box-shadow: 0 24px 80px rgba(0, 0, 0, 0.28);
            animation: auctionDeletePop 0.18s ease-out;
        }

        @keyframes auctionDeletePop {
            from {
                opacity: 0;
                transform: translateY(12px) scale(0.96);
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .auction-delete-close {
            position: absolute;
            top: 14px;
            right: 16px;
            border: 0;
            background: transparent;
            color: var(--secondary-color, #6c7a91);
            font-size: 26px;
            line-height: 1;
            cursor: pointer;
        }

        .auction-delete-close:hover {
            color: var(--primary-color, #206bc4);
        }

        .auction-delete-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 52px;
            height: 52px;
            margin-bottom: 16px;
            border-radius: 50%;
            background: #fee2e2;
            color: #dc2626;
            font-size: 28px;
            font-weight: 800;
        }

        .auction-delete-card h3 {
            margin: 0 0 10px;
            color: var(--text-color, #182433);
            font-size: 22px;
            font-weight: 700;
        }

        .auction-delete-card p {
            margin-bottom: 16px;
            color: var(--secondary-color, #6c7a91);
        }

        .auction-delete-title {
            margin-bottom: 22px;
            padding: 12px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            background: #f8fafc;
            color: var(--text-color, #182433);
            font-weight: 600;
        }

        .auction-delete-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .auction-delete-button,
        .auction-delete-confirm {
            border-color: var(--primary-color, #206bc4);
            background: var(--primary-color, #206bc4);
            color: #fff;
        }

        .auction-delete-button:hover,
        .auction-delete-button:focus,
        .auction-delete-confirm:hover,
        .auction-delete-confirm:focus {
            border-color: var(--primary-color, #206bc4);
            background: var(--primary-color, #206bc4);
            color: #fff;
            filter: brightness(0.95);
        }

        .auction-delete-button:disabled,
        .auction-delete-confirm:disabled {
            border-color: var(--primary-color, #206bc4);
            background: var(--primary-color, #206bc4);
            color: #fff;
            opacity: 0.65;
        }

        body.auction-modal-open {
            overflow: hidden;
        }

        .auction-flash-message {
            transition: opacity 0.24s ease, transform 0.24s ease, margin 0.24s ease, padding 0.24s ease;
        }

        .auction-flash-message.is-hiding {
            opacity: 0;
            transform: translateY(-8px);
        }
    </style>
@endpush

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <p class="text-muted mb-0">{{ __('Manage your auction items, bidders, and winners.') }}</p>
        <a href="{{ route('marketplace.vendor.auctions.create') }}" class="btn btn-primary">
            <x-core::icon name="ti ti-plus" />
            {{ __('Create auction') }}
        </a>
    </div>

    @if (session('success_msg'))
        <div class="alert alert-success auction-flash-message" data-auction-flash>{{ session('success_msg') }}</div>
    @endif
    @if (session('error_msg'))
        <div class="alert alert-danger auction-flash-message" data-auction-flash>{{ session('error_msg') }}</div>
    @endif

    <div
        class="table-responsive"
        data-auction-list
        data-auction-statuses-url="{{ route('marketplace.vendor.auctions.statuses') }}"
    >
        <table class="table table-striped align-middle">
            <thead>
                <tr>
                    <th>{{ __('Title') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Current bid') }}</th>
                    <th>{{ __('Bidders') }}</th>
                    <th>{{ __('Ends') }}</th>
                    <th class="text-end">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($auctions as $auction)
                    <tr
                        data-auction-row="{{ $auction->getKey() }}"
                        data-auction-start-at="{{ optional($auction->start_time)->toIso8601String() }}"
                    >
                        <td>{{ $auction->title }}</td>
                        <td>
                            <span class="auction-status-badge {{ $auction->status_badge_class }}" data-auction-status-badge>
                                {{ $auction->status_label }}
                            </span>
                        </td>
                        <td data-auction-current-bid>{{ format_price($auction->current_bid_amount) }}</td>
                        <td data-auction-bids-count>{{ $auction->bids_count }}</td>
                        <td data-auction-end-time>{{ $auction->end_time->translatedFormat('M d, Y H:i') }}</td>
                        <td class="text-end">
                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('marketplace.vendor.auctions.bidders', $auction) }}">{{ __('Bidders') }}</a>
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('marketplace.vendor.auctions.edit', $auction) }}">{{ __('Edit') }}</a>
                            <form class="d-inline auction-delete-form" method="POST" action="{{ route('marketplace.vendor.auctions.destroy', $auction) }}">
                                @csrf
                                @method('DELETE')
                                <button
                                    class="btn btn-sm auction-delete-button js-auction-delete"
                                    type="button"
                                    data-auction-title="{{ $auction->title }}"
                                    @disabled(! $auction->canVendorDelete())
                                >
                                    {{ __('Delete') }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6">{{ __('No auction items found.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $auctions->links() }}

    <div class="auction-delete-modal" id="auctionDeleteModal" aria-hidden="true">
        <div class="auction-delete-backdrop" data-auction-delete-dismiss></div>

        <div class="auction-delete-card" role="dialog" aria-modal="true" aria-labelledby="auctionDeleteHeading">
            <button type="button" class="auction-delete-close" aria-label="{{ __('Close') }}" data-auction-delete-dismiss>&times;</button>

            <div class="auction-delete-icon">!</div>

            <h3 id="auctionDeleteHeading">{{ __('Delete Auction?') }}</h3>

            <p>
                {{ __('Are you sure you want to delete this auction? This action cannot be undone.') }}
            </p>

            <div class="auction-delete-title" id="auctionDeleteTitle"></div>

            <div class="auction-delete-actions">
                <button type="button" class="btn btn-light js-auction-delete-cancel" data-auction-delete-dismiss>
                    {{ __('Cancel') }}
                </button>

                <button type="button" class="btn auction-delete-confirm js-auction-delete-confirm">
                    {{ __('Delete Auction') }}
                </button>
            </div>
        </div>
    </div>
@endsection

@push('footer')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var auctionList = document.querySelector('[data-auction-list]');
            var auctionListRefreshTimer = null;
            var auctionListStartTimer = null;

            document.querySelectorAll('[data-auction-flash]').forEach(function (alert) {
                setTimeout(function () {
                    alert.classList.add('is-hiding');

                    setTimeout(function () {
                        alert.remove();
                    }, 260);
                }, 3500);
            });

            var getAuctionRows = function () {
                return auctionList ? Array.prototype.slice.call(auctionList.querySelectorAll('[data-auction-row]')) : [];
            };

            var scheduleNextAuctionStatusRefresh = function () {
                if (!auctionList) {
                    return;
                }

                if (auctionListStartTimer) {
                    clearTimeout(auctionListStartTimer);
                    auctionListStartTimer = null;
                }

                var nearestDelay = null;

                getAuctionRows().forEach(function (row) {
                    var badge = row.querySelector('[data-auction-status-badge]');

                    if (!badge || !badge.classList.contains('scheduled') || !row.dataset.auctionStartAt) {
                        return;
                    }

                    var delay = new Date(row.dataset.auctionStartAt).getTime() - Date.now();

                    if (delay > 0 && delay < 2147483647 && (nearestDelay === null || delay < nearestDelay)) {
                        nearestDelay = delay;
                    }
                });

                if (nearestDelay !== null) {
                    auctionListStartTimer = setTimeout(refreshVendorAuctionListStatuses, nearestDelay + 1000);
                }
            };

            function refreshVendorAuctionListStatuses() {
                if (!auctionList || !auctionList.dataset.auctionStatusesUrl) {
                    return;
                }

                var rows = getAuctionRows();
                var ids = rows.map(function (row) {
                    return row.dataset.auctionRow;
                }).filter(Boolean);

                if (!ids.length) {
                    return;
                }

                var url = new URL(auctionList.dataset.auctionStatusesUrl, window.location.origin);
                url.searchParams.set('ids', ids.join(','));

                fetch(url.toString(), {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(function (response) {
                        return response.json();
                    })
                    .then(function (json) {
                        if (!json.success || !json.auctions) {
                            return;
                        }

                        Object.keys(json.auctions).forEach(function (id) {
                            var data = json.auctions[id];
                            var row = auctionList.querySelector('[data-auction-row="' + id + '"]');

                            if (!row) {
                                return;
                            }

                            row.dataset.auctionStartAt = data.start_time || '';

                            var badge = row.querySelector('[data-auction-status-badge]');

                            if (badge) {
                                badge.className = 'auction-status-badge ' + data.status_badge_class;
                                badge.textContent = data.status_label;
                            }

                            var currentBid = row.querySelector('[data-auction-current-bid]');
                            var bidsCount = row.querySelector('[data-auction-bids-count]');
                            var endTime = row.querySelector('[data-auction-end-time]');
                            var deleteButton = row.querySelector('.js-auction-delete');

                            if (currentBid) {
                                currentBid.textContent = data.current_bid;
                            }

                            if (bidsCount) {
                                bidsCount.textContent = data.bids_count;
                            }

                            if (endTime) {
                                endTime.textContent = data.end_time_label || '';
                            }

                            if (deleteButton) {
                                deleteButton.disabled = !data.can_delete;
                            }
                        });

                        scheduleNextAuctionStatusRefresh();
                    })
                    .catch(function () {
                    });
            }

            if (auctionList) {
                refreshVendorAuctionListStatuses();
                scheduleNextAuctionStatusRefresh();
                auctionListRefreshTimer = setInterval(refreshVendorAuctionListStatuses, 10000);
            }

            var modal = document.getElementById('auctionDeleteModal');

            if (!modal) {
                return;
            }

            var titleEl = document.getElementById('auctionDeleteTitle');
            var confirmBtn = modal.querySelector('.js-auction-delete-confirm');
            var activeForm = null;
            var defaultConfirmText = confirmBtn ? confirmBtn.textContent : '';

            var openDeleteModal = function (form, title) {
                activeForm = form;

                if (titleEl) {
                    titleEl.textContent = title || '{{ __('This auction') }}';
                }

                if (confirmBtn) {
                    confirmBtn.disabled = false;
                    confirmBtn.textContent = defaultConfirmText;
                }

                modal.classList.add('is-active');
                modal.setAttribute('aria-hidden', 'false');
                document.body.classList.add('auction-modal-open');
            };

            var closeDeleteModal = function () {
                activeForm = null;
                modal.classList.remove('is-active');
                modal.setAttribute('aria-hidden', 'true');
                document.body.classList.remove('auction-modal-open');
            };

            document.addEventListener('click', function (event) {
                var deleteButton = event.target.closest('.js-auction-delete');

                if (deleteButton) {
                    event.preventDefault();

                    if (deleteButton.disabled) {
                        return;
                    }

                    openDeleteModal(deleteButton.closest('form'), deleteButton.dataset.auctionTitle);

                    return;
                }

                if (event.target.closest('[data-auction-delete-dismiss]')) {
                    closeDeleteModal();
                }
            });

            if (confirmBtn) {
                confirmBtn.addEventListener('click', function () {
                    if (!activeForm) {
                        return;
                    }

                    confirmBtn.disabled = true;
                    confirmBtn.textContent = '{{ __('Deleting...') }}';
                    activeForm.submit();
                });
            }

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && modal.classList.contains('is-active')) {
                    closeDeleteModal();
                }
            });
        });
    </script>
@endpush
