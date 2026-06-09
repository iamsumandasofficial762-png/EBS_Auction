<script>
    document.addEventListener('DOMContentLoaded', function () {
        var dashboard = document.querySelector('[data-auction-dashboard]');
        var countdownIntervals = [];
        var refreshTimer = null;
        var isRefreshing = false;
        var isBidModalOpen = false;

        var activeModal = function () {
            return isBidModalOpen || document.querySelector('.auction-bid-modal.show');
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

            isBidModalOpen = true;

            var form = modal.querySelector('.auction-bid-form');
            var amountInput = modal.querySelector('[name="amount"]');
            var image = modal.querySelector('[data-bid-auction-image]');
            var imageWrap = modal.querySelector('.auction-bid-product');
            var timeLeft = modal.querySelector('[data-bid-auction-time-left]');

            if (form && button.dataset.url) {
                form.action = button.dataset.url;
                form.dataset.auctionId = button.dataset.auctionId || '';
            }

            if (amountInput) {
                amountInput.min = button.dataset.setAmountRaw || '';
                amountInput.value = button.dataset.setAmountRaw || '';
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

        var formatCountdown = function (distance) {
            var days = Math.floor(distance / 86400000);
            var hours = Math.floor((distance % 86400000) / 3600000);
            var minutes = Math.floor((distance % 3600000) / 60000);

            return days
                ? days + 'd ' + String(hours).padStart(2, '0') + 'h ' + String(minutes).padStart(2, '0') + 'm'
                : String(hours).padStart(2, '0') + 'h ' + String(minutes).padStart(2, '0') + 'm';
        };

        var initAuctionImageSliders = function () {
            document.querySelectorAll('[data-auction-slider]').forEach(function (slider) {
                if (slider.dataset.initialized === '1') {
                    return;
                }

                slider.dataset.initialized = '1';

                var track = slider.querySelector('.auction-slider-track');
                var slides = slider.querySelectorAll('.auction-slider-slide');
                var prev = slider.querySelector('[data-slider-prev]');
                var next = slider.querySelector('[data-slider-next]');
                var dots = slider.querySelectorAll('[data-slider-dot]');
                var index = 0;

                if (!track || !slides.length) {
                    return;
                }

                var goTo = function (newIndex) {
                    index = (newIndex + slides.length) % slides.length;
                    track.style.transform = 'translateX(-' + (index * 100) + '%)';

                    dots.forEach(function (dot, dotIndex) {
                        dot.classList.toggle('is-active', dotIndex === index);
                    });
                };

                if (prev) {
                    prev.addEventListener('click', function (event) {
                        event.preventDefault();
                        event.stopPropagation();
                        goTo(index - 1);
                    });
                }

                if (next) {
                    next.addEventListener('click', function (event) {
                        event.preventDefault();
                        event.stopPropagation();
                        goTo(index + 1);
                    });
                }

                dots.forEach(function (dot) {
                    dot.addEventListener('click', function (event) {
                        event.preventDefault();
                        event.stopPropagation();
                        goTo(parseInt(dot.dataset.sliderDot, 10) || 0);
                    });
                });

                goTo(0);
            });
        };

        var initCountdowns = function () {
            clearCountdowns();

            document.querySelectorAll('[data-countdown-target]').forEach(function (element) {
                var raw = element.dataset.countdownTarget;

                if (!raw) {
                    return;
                }

                var target = new Date(raw).getTime();
                var expiredText = element.dataset.expiredText || '{{ __('Closed') }}';
                var refreshTriggered = false;

                var render = function () {
                    var distance = target - Date.now();

                    if (distance <= 0) {
                        element.textContent = expiredText;

                        if (!refreshTriggered && dashboard) {
                            refreshTriggered = true;
                            refreshAuctionStatus();
                        }

                        return;
                    }

                    element.textContent = formatCountdown(distance);
                };

                render();
                countdownIntervals.push(setInterval(render, 1000));
            });

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
                        element.textContent = '{{ __('Closed') }}';

                        if (!refreshTriggered && dashboard) {
                            refreshTriggered = true;
                            refreshAuctionStatus();
                        }

                        return;
                    }

                    element.textContent = formatCountdown(distance);
                };

                render();
                countdownIntervals.push(setInterval(render, 60000));
            });

            document.querySelectorAll('[data-auto-select-at]').forEach(function (element) {
                var raw = element.dataset.autoSelectAt;

                if (!raw) {
                    element.textContent = '{{ __('Selecting winner...') }}';

                    return;
                }

                var autoSelectAt = new Date(raw).getTime();
                var refreshTriggered = false;

                var render = function () {
                    var distance = autoSelectAt - Date.now();

                    if (distance <= 0) {
                        element.textContent = '{{ __('Selecting winner...') }}';

                        if (!refreshTriggered && dashboard) {
                            refreshTriggered = true;
                            refreshAuctionStatus();
                        }

                        return;
                    }

                    element.textContent = formatCountdown(distance);
                };

                render();
                countdownIntervals.push(setInterval(render, 60000));
            });

            document.querySelectorAll('[data-live-now-until]').forEach(function (badge) {
                var raw = badge.dataset.liveNowUntil;

                if (!raw) {
                    return;
                }

                var until = new Date(raw).getTime();
                var refreshTriggered = false;

                var render = function () {
                    var distance = until - Date.now();

                    if (distance <= 0) {
                        badge.remove();

                        if (!refreshTriggered && dashboard) {
                            refreshTriggered = true;
                            refreshAuctionStatus();
                        }

                        return;
                    }
                };

                render();
                countdownIntervals.push(setInterval(render, Math.min(Math.max(until - Date.now(), 1000), 60000)));
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

                    if (activeModal()) {
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
                    initAuctionImageSliders();
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

        document.addEventListener('show.bs.modal', function (event) {
            if (event.target && event.target.classList.contains('auction-bid-modal')) {
                isBidModalOpen = true;
            }
        });

        document.addEventListener('hidden.bs.modal', function (event) {
            if (!event.target || !event.target.classList.contains('auction-bid-modal')) {
                return;
            }

            isBidModalOpen = false;

            if (dashboard) {
                setTimeout(window.refreshAuctionStatus, 100);
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

        var updateNotificationCount = function (count) {
            var notificationCount = document.querySelector('[data-auction-tab-count="notifications"]');

            if (notificationCount && count !== undefined && count !== null) {
                notificationCount.textContent = count;
            }
        };

        var submitNotificationAction = function (form, onSuccess) {
            var button = form.querySelector('[type="submit"]');
            var originalText = button ? button.textContent : '';
            var methodField = form.querySelector('[name="_method"]');

            if (button) {
                button.disabled = true;
            }

            fetch(form.action, {
                method: methodField ? methodField.value : form.method,
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: new FormData(form)
            })
                .then(function (response) {
                    return response.json().then(function (json) {
                        if (!response.ok || !json.success) {
                            throw new Error(json.message || '{{ __('Unable to update notification.') }}');
                        }

                        return json;
                    });
                })
                .then(function (json) {
                    updateNotificationCount(json.unread_count);
                    onSuccess(json);
                })
                .catch(function (error) {
                    showMessage('error', error.message);

                    if (button) {
                        button.disabled = false;
                        button.textContent = originalText;
                    }
                });
        };

        document.addEventListener('submit', function (event) {
            var form = event.target.closest('[data-notification-read-form], [data-notification-delete-form]');

            if (!form) {
                return;
            }

            event.preventDefault();

            if (form.matches('[data-notification-read-form]')) {
                submitNotificationAction(form, function () {
                    var card = form.closest('[data-notification-card]');

                    if (card) {
                        card.classList.remove('is-unread');
                        card.classList.add('is-read');
                        card.dataset.isUnread = '0';
                    }

                    var button = form.querySelector('[type="submit"]');

                    if (button) {
                        button.textContent = '{{ __('Read') }}';
                        button.classList.remove('auction-btn--primary');
                        button.classList.add('auction-btn--muted', 'is-read-button');
                        button.disabled = true;
                    }
                });

                return;
            }

            submitNotificationAction(form, function () {
                var card = form.closest('[data-notification-card]');

                if (card) {
                    card.remove();
                }

                if (dashboard) {
                    setTimeout(window.refreshAuctionStatus, 100);
                }
            });
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
        initAuctionImageSliders();

        if (dashboard) {
            refreshTimer = setInterval(window.refreshAuctionStatus, 15000);
        }
    });
</script>
