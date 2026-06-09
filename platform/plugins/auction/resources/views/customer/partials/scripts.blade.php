<script>
    document.addEventListener('DOMContentLoaded', function () {
        var dashboard = document.querySelector('[data-auction-dashboard]');
        var countdownIntervals = [];
        var refreshTimer = null;
        var isRefreshing = false;

        var activeModal = function () {
            return document.querySelector('.auction-bid-modal.show');
        };

        var showMessage = function (type, message) {
            if (!message) {
                return;
            }

            if (window.Botble && Botble.showNotice) {
                Botble.showNotice(type, message);
                return;
            }

            if (type === 'error') {
                alert(message);
                return;
            }

            var notice = document.createElement('div');
            notice.className = 'alert alert-success';
            notice.style.position = 'fixed';
            notice.style.right = '20px';
            notice.style.top = '20px';
            notice.style.zIndex = '1060';
            notice.textContent = message;
            document.body.appendChild(notice);

            setTimeout(function () {
                notice.remove();
            }, 3500);
        };

        var setText = function (container, selector, value) {
            var element = container.querySelector(selector);

            if (element && value) {
                element.textContent = value;
            }
        };

        var prepareBidModal = function (button) {
            var target = button.dataset.bsTarget;

            if (!target) {
                return;
            }

            var modal = document.querySelector(target);

            if (!modal) {
                return;
            }

            var form = modal.querySelector('.auction-bid-form');
            var amountInput = modal.querySelector('[name="amount"]');
            var image = modal.querySelector('[data-bid-auction-image]');
            var imageWrap = modal.querySelector('.auction-bid-product');
            var timeLeft = modal.querySelector('[data-bid-auction-time-left]');

            if (form && button.dataset.url) {
                form.action = button.dataset.url;
                form.dataset.auctionId = button.dataset.auctionId || '';
            }

            if (amountInput && button.dataset.minimumBid) {
                amountInput.min = button.dataset.minimumBid;
                amountInput.value = button.dataset.minimumBid;
            }

            if (image && imageWrap) {
                if (button.dataset.image) {
                    image.src = button.dataset.image;
                    image.alt = button.dataset.title || '';
                    imageWrap.classList.remove('is-empty');
                } else {
                    imageWrap.classList.add('is-empty');
                }
            }

            if (timeLeft && button.dataset.countdown) {
                timeLeft.dataset.auctionCountdown = button.dataset.countdown;
                timeLeft.textContent = '{{ __('Calculating') }}';
            }

            setText(modal, '[data-bid-auction-title]', button.dataset.title);
            setText(modal, '[data-bid-auction-set-amount]', button.dataset.setAmount);
            setText(modal, '[data-bid-auction-end-time]', button.dataset.endTime);
            setText(modal, '[data-bid-auction-condition]', button.dataset.condition);
            setText(modal, '[data-bid-auction-brand]', button.dataset.brand);
            setText(modal, '[data-bid-auction-model]', button.dataset.model);
            setText(modal, '[data-bid-auction-sku]', button.dataset.sku);

            initCountdowns();
        };

        var updateAuctionBidUi = function (data) {
            if (!data || !data.auction_id) {
                return;
            }

            var wrappers = document.querySelectorAll(
                '.auction-card[data-auction-id="' + data.auction_id + '"], .auction-detail-card[data-auction-id="' + data.auction_id + '"]'
            );

            wrappers.forEach(function (wrapper) {
                var bidButton = wrapper.querySelector('[data-auction-open-bid]');
                var myBid = wrapper.querySelector('[data-my-bid]');
                var bidStatus = wrapper.querySelector('[data-bid-status]');

                if (bidButton) {
                    bidButton.disabled = true;
                    bidButton.classList.remove('auction-btn--primary');
                    bidButton.classList.add('auction-btn--muted', 'is-disabled');
                    bidButton.removeAttribute('data-auction-open-bid');
                    bidButton.removeAttribute('data-bs-toggle');
                    bidButton.removeAttribute('data-bs-target');
                    bidButton.removeAttribute('data-url');
                    bidButton.textContent = data.button_text || '{{ __('Bid Placed') }}';
                }

                if (myBid && data.my_bid) {
                    myBid.textContent = data.my_bid;
                }

                if (bidStatus && data.status_label) {
                    bidStatus.textContent = data.status_label;
                }
            });
        };

        var clearCountdowns = function () {
            countdownIntervals.forEach(function (interval) {
                clearInterval(interval);
            });

            countdownIntervals = [];
        };

        var initCountdowns = function () {
            clearCountdowns();

            document.querySelectorAll('[data-auction-countdown]').forEach(function (element) {
                var raw = element.dataset.auctionCountdown;

                if (!raw) {
                    return;
                }

                var end = new Date(raw).getTime();
                var refreshTriggered = false;

                var render = function () {
                    var distance = end - Date.now();

                    if (distance <= 0) {
                        if (element.textContent === '{{ __('Calculating') }}') {
                            element.textContent = '{{ __('Not allocated yet') }}';
                        }

                        if (!refreshTriggered && dashboard) {
                            refreshTriggered = true;
                            refreshAuctionStatus();
                        }

                        return;
                    }

                    var days = Math.floor(distance / 86400000);
                    var hours = Math.floor((distance % 86400000) / 3600000);
                    var minutes = Math.floor((distance % 3600000) / 60000);

                    element.textContent = days
                        ? days + 'd ' + String(hours).padStart(2, '0') + 'h'
                        : String(hours).padStart(2, '0') + 'h ' + String(minutes).padStart(2, '0') + 'm';
                };

                render();
                countdownIntervals.push(setInterval(render, 60000));
            });
        };

        var setActiveTab = function (key, pushState) {
            if (!dashboard) {
                return;
            }

            dashboard.dataset.activeTab = key;

            document.querySelectorAll('[data-auction-tab]').forEach(function (tab) {
                tab.classList.toggle('is-active', tab.dataset.auctionTab === key);
            });

            document.querySelectorAll('[data-auction-tab-panel]').forEach(function (panel) {
                panel.classList.toggle('d-none', panel.dataset.auctionTabPanel !== key);
            });

            if (pushState) {
                var url = new URL(window.location.href);
                url.searchParams.set('tab', key);
                window.history.pushState({}, '', url.toString());
            }
        };

        window.refreshAuctionStatus = function () {
            if (!dashboard || isRefreshing || activeModal()) {
                return;
            }

            isRefreshing = true;

            var url = new URL(dashboard.dataset.statusFeedUrl, window.location.origin);
            url.searchParams.set('tab', dashboard.dataset.activeTab || 'live');

            fetch(url.toString(), {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(function (response) {
                    return response.json();
                })
                .then(function (data) {
                    if (!data.success) {
                        return;
                    }

                    Object.keys(data.counts || {}).forEach(function (key) {
                        var count = document.querySelector('[data-auction-tab-count="' + key + '"]');

                        if (count) {
                            count.textContent = data.counts[key];
                        }
                    });

                    Object.keys(data.html || {}).forEach(function (key) {
                        var panel = document.querySelector('[data-auction-tab-panel="' + key + '"]');

                        if (panel) {
                            panel.innerHTML = data.html[key];
                        }
                    });

                    initCountdowns();
                })
                .catch(function () {
                })
                .finally(function () {
                    isRefreshing = false;
                });
        };

        document.addEventListener('click', function (event) {
            var bidButton = event.target.closest('[data-auction-open-bid]');

            if (bidButton) {
                prepareBidModal(bidButton);
            }
        });

        document.addEventListener('click', function (event) {
            var tab = event.target.closest('[data-auction-tab]');

            if (!tab) {
                return;
            }

            event.preventDefault();
            setActiveTab(tab.dataset.auctionTab, true);
        });

        document.addEventListener('submit', function (event) {
            var form = event.target.closest('.auction-bid-form');

            if (!form) {
                return;
            }

            event.preventDefault();

            var button = form.querySelector('[type="submit"]');
            var originalText = button ? button.textContent : '';

            if (button) {
                button.disabled = true;
                button.textContent = '{{ __('Placing bid...') }}';
            }

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new FormData(form)
            })
                .then(function (response) {
                    return response.json().then(function (json) {
                        if (!response.ok || !json.success) {
                            var message = json.message || '{{ __('Unable to place bid.') }}';

                            if (json.errors) {
                                var firstKey = Object.keys(json.errors)[0];
                                message = json.errors[firstKey][0] || message;
                            }

                            throw new Error(message);
                        }

                        return json;
                    });
                })
                .then(function (json) {
                    var modal = form.closest('.modal');

                    if (modal && window.bootstrap) {
                        var instance = bootstrap.Modal.getInstance(modal) || new bootstrap.Modal(modal);
                        instance.hide();
                    }

                    showMessage('success', json.message);
                    updateAuctionBidUi(json);

                    if (dashboard) {
                        setTimeout(window.refreshAuctionStatus, 350);
                    }
                })
                .catch(function (error) {
                    showMessage('error', error.message);
                })
                .finally(function () {
                    if (button) {
                        button.disabled = false;
                        button.textContent = originalText;
                    }
                });
        });

        initCountdowns();

        if (dashboard) {
            refreshTimer = setInterval(window.refreshAuctionStatus, 15000);
        }
    });
</script>
