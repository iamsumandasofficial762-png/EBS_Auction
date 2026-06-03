(function ($) {
    'use strict';

    // Create global MartApp object for plugin detection
    let MartApp = window.MartApp || {};
    window.MartApp = MartApp;

    // Define show message functions using Theme's toast notifications
    MartApp.showSuccess = function(message) {
        if (typeof Theme !== 'undefined' && Theme.showSuccess) {
            Theme.showSuccess(message);
        }
    };

    MartApp.showError = function(message) {
        if (typeof Theme !== 'undefined' && Theme.showError) {
            Theme.showError(message);
        }
    };

    // Make show functions available globally for plugin compatibility
    window.showSuccess = MartApp.showSuccess;
    window.showError = MartApp.showError;

    let isRTL = $('body').prop('dir') === 'rtl'

    function backgroundImage() {
        let dataBackground = $('[data-background]');
        dataBackground.each(function () {
            if ($(this).attr('data-background')) {
                let imagePath = $(this).attr('data-background');
                $(this).css({
                    'background-image': 'url(' + imagePath + ')',
                    'background-color': '#fff'
                });
            }
        });
    }

    function siteToggleAction() {
        let navSidebar = $('.navigation--sidebar'),
            filterSidebar = $('.ps-filter--sidebar');
        $('.menu-toggle-open').on('click', function (e) {
            e.preventDefault();
            $(this).toggleClass('active');
            navSidebar.toggleClass('active');
            $('.ps-site-overlay').toggleClass('active');
        });

        $('.ps-toggle--sidebar').on('click', function (e) {
            e.preventDefault();
            let url = $(this).attr('href');
            $(this).toggleClass('active');
            $(this)
                .siblings('a')
                .removeClass('active');
            $(url).toggleClass('active');
            $(url)
                .siblings('.ps-panel--sidebar')
                .removeClass('active');
            if ($(this).hasClass('active')) {
                $('.ps-site-overlay').addClass('active');
            } else {
                $('.ps-site-overlay').removeClass('active');
            }
        });

        $('#filter-sidebar').on('click', function (e) {
            e.preventDefault();
            filterSidebar.addClass('active');
            $('.ps-site-overlay').addClass('active');
        });

        $('.ps-filter--sidebar .ps-filter__header .ps-btn--close').on(
            'click',
            function (e) {
                e.preventDefault();
                filterSidebar.removeClass('active');
                $('.ps-site-overlay').removeClass('active');
            }
        );

        $('body').on('click', function (e) {
            if (
                $(e.target)
                    .siblings('.ps-panel--sidebar')
                    .hasClass('active')
            ) {
                $('.ps-panel--sidebar').removeClass('active');
                $('.ps-site-overlay').removeClass('active');
            }
        });
    }

    function subMenuToggle() {
        $('.menu--mobile .menu-item-has-children > .sub-toggle').on(
            'click',
            function (e) {
                e.preventDefault();
                let current = $(this).parent('.menu-item-has-children');
                $(this).toggleClass('active');
                current
                    .siblings()
                    .find('.sub-toggle')
                    .removeClass('active');
                current.children('.sub-menu').slideToggle(350);
                current
                    .siblings()
                    .find('.sub-menu')
                    .slideUp(350);
                if (current.hasClass('has-mega-menu')) {
                    current.children('.mega-menu').slideToggle(350);
                    current
                        .siblings('.has-mega-menu')
                        .find('.mega-menu')
                        .slideUp(350);
                }
            }
        );
        $('.menu--mobile .has-mega-menu .mega-menu__column .sub-toggle').on(
            'click',
            function (e) {
                e.preventDefault();
                let current = $(this).closest('.mega-menu__column');
                $(this).toggleClass('active');
                current
                    .siblings()
                    .find('.sub-toggle')
                    .removeClass('active');
                current.children('.mega-menu__list').slideToggle(350);
                current
                    .siblings()
                    .find('.mega-menu__list')
                    .slideUp(350);
            }
        );

        let $listCategories = $(document).find('.widget-product-categories');
        if ($listCategories.length > 0) {
            $(document).on(
                'click',
                '.widget-product-categories .menu-item-has-children > .sub-toggle',
                function (e) {
                    e.preventDefault();
                    let current = $(this).parent('.menu-item-has-children');
                    $(this).toggleClass('active');
                    current
                        .siblings()
                        .find('.sub-toggle')
                        .removeClass('active');
                    current.children('.sub-menu').slideToggle(350);
                    current
                        .siblings()
                        .find('.sub-menu')
                        .slideUp(350);
                    if (current.hasClass('has-mega-menu')) {
                        current.children('.mega-menu').slideToggle(350);
                        current
                            .siblings('.has-mega-menu')
                            .find('.mega-menu')
                            .slideUp(350);
                    }
                }
            );
        }
    }

    let initMegaMenu = function () {
        setTimeout(function () {
            const $megaMenu = $(document).find('.mega-menu-wrapper')

            if (! $megaMenu.length) {
                return
            }

            if ($(window).width() > 1200 && typeof $.fn.masonry !== 'undefined') {
                $megaMenu.masonry({
                    itemSelector: '.mega-menu__column',
                    columnWidth: 200,
                    originLeft: !isRTL
                })
            }
        }, 500)
    }

    function stickyHeader() {
        let header = $('.header'),
            checkpoint = 50;
        header.each(function () {
            if ($(this).data('sticky') === true) {
                let el = $(this);
                $(window).scroll(function () {
                    let currentPosition = $(this).scrollTop();
                    if (currentPosition > checkpoint) {
                        el.addClass('header--sticky');

                        initMegaMenu()
                    } else {
                        el.removeClass('header--sticky');
                    }
                });
            }
        });

        let stickyCart = $('#cart-sticky');
        if (stickyCart.length > 0) {
            $(window).scroll(function () {
                let currentPosition = $(this).scrollTop();
                if (currentPosition > checkpoint) {
                    stickyCart.addClass('active');
                } else {
                    stickyCart.removeClass('active');
                }
            });
        }
    }

    function initOwlCarousel(container) {
        let selector = container ? $(container).find('.owl-slider, .owl-carousel') : $('.owl-slider, .owl-carousel');
        
        selector.not('.slick-slider').each(function () {
            let el = $(this);
            
            // Skip if already initialized
            if (el.hasClass('owl-loaded') || el.data('owl.carousel')) {
                return;
            }
            
            // Check if owlCarousel is available
            if (typeof $.fn.owlCarousel === 'undefined') {
                console.error('Owl Carousel plugin is not loaded');
                return;
            }
            
            let dataAuto = el.data('owl-auto'),
                dataLoop = el.data('owl-loop'),
                dataSpeed = el.data('owl-speed'),
                dataGap = el.data('owl-gap'),
                dataNav = el.data('owl-nav'),
                dataDots = el.data('owl-dots'),
                dataAnimateIn = el.data('owl-animate-in')
                    ? el.data('owl-animate-in')
                    : '',
                dataAnimateOut = el.data('owl-animate-out')
                    ? el.data('owl-animate-out')
                    : '',
                dataDefaultItem = el.data('owl-item'),
                dataItemXS = el.data('owl-item-xs'),
                dataItemSM = el.data('owl-item-sm'),
                dataItemMD = el.data('owl-item-md'),
                dataItemLG = el.data('owl-item-lg'),
                dataItemXL = el.data('owl-item-xl'),
                dataNavLeft = el.data('owl-nav-left')
                    ? el.data('owl-nav-left')
                    : "<i class='icon-chevron-left'></i>",
                dataNavRight = el.data('owl-nav-right')
                    ? el.data('owl-nav-right')
                    : "<i class='icon-chevron-right'></i>",
                duration = el.data('owl-duration'),
                datamouseDrag =
                    el.data('owl-mousedrag') == 'on' ? true : false;
            
            // Additional data attributes that might be used
            let dataMargin = el.data('owl-margin'),
                dataAutoHeight = el.data('owl-autoheight'),
                dataCenter = el.data('owl-center'),
                dataMouseDrag = el.data('owl-mouse-drag'),
                dataTouchDrag = el.data('owl-touch-drag'),
                dataSmartSpeed = el.data('owl-smart-speed'),
                dataItems = el.data('owl-items'),
                dataAutoplayHoverPause = el.data('owl-autoplay-hover-pause'),
                dataAutoplayTimeout = el.data('owl-autoplay-timeout'),
                dataResponsive = el.data('owl-responsive');
            
            // Check how many items we have - look for direct children that are actual items
            let itemCount = el.children().length;
            
            // If no direct children, check for common item patterns
            if (itemCount === 0) {
                itemCount = el.find('> .ps-product, > article, > .item, > div').length;
            }

            if (itemCount >= 2) {
                    // Normal carousel initialization for multiple items
                    el.addClass('owl-carousel').owlCarousel({
                        rtl: isRTL,
                        animateIn: dataAnimateIn,
                        animateOut: dataAnimateOut,
                        margin: dataMargin || dataGap || 0,
                        autoplay: dataAuto,
                        autoplayTimeout: dataAutoplayTimeout || dataSpeed || 5000,
                        autoplayHoverPause: dataAutoplayHoverPause !== undefined ? dataAutoplayHoverPause : true,
                        loop: dataLoop,
                        nav: dataNav,
                        mouseDrag: dataMouseDrag !== undefined ? dataMouseDrag : datamouseDrag,
                        touchDrag: dataTouchDrag !== undefined ? dataTouchDrag : true,
                        autoplaySpeed: duration,
                        navSpeed: duration,
                        dotsSpeed: duration,
                        dragEndSpeed: duration,
                        navText: [dataNavLeft, dataNavRight],
                        dots: dataDots,
                        items: dataItems || dataDefaultItem || 4,
                        center: dataCenter || false,
                        autoHeight: dataAutoHeight || false,
                        smartSpeed: dataSmartSpeed || 450,
                        responsive: dataResponsive || {
                            0: {
                                items: dataItemXS || 1,
                            },
                            480: {
                                items: dataItemSM || 2,
                            },
                            768: {
                                items: dataItemMD || 3,
                            },
                            992: {
                                items: dataItemLG || 4,
                            },
                            1200: {
                                items: dataItemXL || 5,
                            },
                            1680: {
                                items: dataDefaultItem || 6,
                            },
                        },
                    });
                } else if (itemCount === 1) {
                    // Special handling for single item
                    el.addClass('owl-carousel single-item-carousel').owlCarousel({
                        rtl: isRTL,
                        items: 1,
                        dots: false,
                        nav: false,
                        loop: false,
                        mouseDrag: false,
                        touchDrag: false,
                        responsive: {
                            0: {
                                items: 1,
                            },
                            480: {
                                items: 1,
                            },
                            768: {
                                items: 1,
                            },
                            992: {
                                items: 1,
                            },
                            1200: {
                                items: 1,
                            },
                            1680: {
                                items: 1,
                            },
                        }
                    });
                } else {
                    // If no items found, try initializing anyway in case structure is different
                    el.addClass('owl-carousel').owlCarousel({
                        rtl: isRTL,
                        items: dataItems || dataDefaultItem || 4,
                        nav: dataNav,
                        dots: dataDots,
                        loop: dataLoop,
                        margin: dataMargin || dataGap || 0,
                        autoplay: dataAuto,
                        autoplayTimeout: dataAutoplayTimeout || dataSpeed || 5000,
                        smartSpeed: dataSmartSpeed || 450,
                        responsive: dataResponsive || {
                            0: {
                                items: dataItemXS || 1,
                            },
                            480: {
                                items: dataItemSM || 2,
                            },
                            768: {
                                items: dataItemMD || 3,
                            },
                            992: {
                                items: dataItemLG || 4,
                            },
                            1200: {
                                items: dataItemXL || 5,
                            },
                            1680: {
                                items: dataDefaultItem || 6,
                            },
                        }
                    });
                }
        });
    }
    
    function owlCarouselConfig() {
        initOwlCarousel();
    }

    function mapConfig() {
        let map = $('#contact-map');
        if (map.length > 0) {
            map.gmap3({
                address: map.data('address'),
                zoom: map.data('zoom'),
                mapTypeId: google.maps.MapTypeId.ROADMAP,
                scrollwheel: false,
            })
                .marker(function (map) {
                    return {
                        position: map.getCenter(),
                        icon: 'img/marker.png',
                    };
                })
                .infowindow({
                    content: map.data('address'),
                })
                .then(function (infowindow) {
                    let map = this.get(0);
                    let marker = this.get(1);
                    marker.addListener('click', function () {
                        infowindow.open(map, marker);
                    });
                });
        } else {
            return false;
        }
    }

    function slickConfig() {
        let product = $('.ps-product--detail');
        if (product.length > 0) {
            let primary = product.find('.ps-product__gallery'),
                second = product.find('.ps-product__variants'),
                vertical = product
                    .find('.ps-product__thumbnail')
                    .data('vertical');
            primary.slick({
                slidesToShow: 1,
                slidesToScroll: 1,
                rtl: isRTL,
                asNavFor: '.ps-product__variants',
                fade: true,
                dots: false,
                infinite: false,
                arrows: primary.data('arrow'),
                prevArrow: "<button class='slick-prev slick-arrow'><i class='fa fa-angle-left'></i></button>",
                nextArrow: "<button class='slick-next slick-arrow'><i class='fa fa-angle-right'></i></button>",
            });
            second.slick({
                slidesToShow: second.data('item'),
                slidesToScroll: 1,
                rtl: isRTL,
                infinite: false,
                arrows: second.data('arrow'),
                focusOnSelect: true,
                prevArrow: "<button class='slick-prev slick-arrow'><i class='fa fa-angle-up'></i></button>",
                nextArrow: "<button class='slick-next slick-arrow'><i class='fa fa-angle-down'></i></button>",
                asNavFor: '.ps-product__gallery',
                vertical: vertical,
                responsive: [
                    {
                        breakpoint: 1200,
                        settings: {
                            arrows: second.data('arrow'),
                            slidesToShow: 4,
                            vertical: false,
                            prevArrow:
                                "<button class='slick-prev slick-arrow'><i class='fa fa-angle-left'></i></button>",
                            nextArrow:
                                "<button class='slick-next slick-arrow'><i class='fa fa-angle-right'></i></button>",
                        },
                    },
                    {
                        breakpoint: 992,
                        settings: {
                            arrows: second.data('arrow'),
                            slidesToShow: 4,
                            vertical: false,
                            prevArrow:
                                "<button class='slick-prev slick-arrow'><i class='fa fa-angle-left'></i></button>",
                            nextArrow:
                                "<button class='slick-next slick-arrow'><i class='fa fa-angle-right'></i></button>",
                        },
                    },
                    {
                        breakpoint: 480,
                        settings: {
                            slidesToShow: 3,
                            vertical: false,
                            prevArrow:
                                "<button class='slick-prev slick-arrow'><i class='fa fa-angle-left'></i></button>",
                            nextArrow:
                                "<button class='slick-next slick-arrow'><i class='fa fa-angle-right'></i></button>",
                        },
                    },
                ],
            });
        }
    }

    function tabs() {
        // Function to activate a tab
        function activateTab(target, shouldScroll = true) {
            if (!$(target).length) {
                return;
            }

            // Remove active class from all tabs and list items
            $('.ps-tab').removeClass('active');
            $('.ps-tab-list li').removeClass('active');

            // Add active class to target tab and corresponding list item
            $(target).addClass('active');
            $('.ps-tab-list li a[href="' + target + '"]').closest('li').addClass('active');

            // Initialize owl carousel in the activated tab
            setTimeout(function() {
                initOwlCarousel(target);
            }, 100);

            // Scroll to tab if needed
            if (shouldScroll && $('.header--product .navigation').length) {
                $('html, body').animate(
                    {
                        scrollTop: ($(target).offset().top - $('.header--product .navigation').height() - 165) + 'px',
                    },
                    800
                );
            }
        }

        // Check for hash in URL on page load and activate corresponding tab
        function checkHashOnLoad() {
            let hash = window.location.hash;
            if (hash && $(hash).length && $(hash).hasClass('ps-tab')) {
                // Small delay to ensure page is fully loaded
                setTimeout(function() {
                    activateTab(hash, false);
                }, 100);
            }
        }

        // Use event delegation for dynamically loaded content
        $(document).on('click', '.ps-tab-list  li > a ', function (e) {
            e.preventDefault();
            let target = $(this).attr('href');

            // Update URL hash without triggering scroll
            if (history.pushState) {
                history.pushState(null, null, target);
            } else {
                window.location.hash = target;
            }

            activateTab(target, true);
        });

        $(document).on('click', '.ps-tab-list.owl-slider .owl-item a', function (e) {
            e.preventDefault();
            let target = $(this).attr('href');

            // Update URL hash without triggering scroll
            if (history.pushState) {
                history.pushState(null, null, target);
            } else {
                window.location.hash = target;
            }

            // Remove active class from owl items
            $(this)
                .closest('.owl-item')
                .siblings('.owl-item')
                .removeClass('active');
            $(this)
                .closest('.owl-item')
                .addClass('active');

            activateTab(target, false);
        });

        // Handle browser back/forward buttons
        $(window).on('hashchange', function() {
            let hash = window.location.hash;
            if (hash && $(hash).length && $(hash).hasClass('ps-tab')) {
                activateTab(hash, false);
            }
        });

        // Initialize - check hash on page load
        checkHashOnLoad();
    }

    function rating() {
        $('select.ps-rating').each(function () {
            let readOnly;
            if ($(this).attr('data-read-only') == 'true') {
                readOnly = true;
            } else {
                readOnly = false;
            }
            $(this).barrating({
                theme: 'fontawesome-stars',
                readonly: readOnly,
                emptyValue: '0',
            });
        });
    }

    function productLightbox() {
        if (! $.fn.lightGallery) {
            return;
        }

        let product = $('.ps-product--detail');
        if (product.length > 0) {
            $('.ps-product__gallery').lightGallery({
                selector: '.item a',
                thumbnail: true,
                share: false,
                fullScreen: false,
                autoplay: false,
                autoplayControls: false,
                actualSize: false,
                closable: true,
                hideControlOnEnd: false,
            });
            if (product.hasClass('ps-product--sticky')) {
                $('.ps-product__thumbnail').lightGallery({
                    selector: '.item a',
                    thumbnail: true,
                    share: false,
                    fullScreen: false,
                    autoplay: false,
                    autoplayControls: false,
                    actualSize: false,
                    closable: true,
                    hideControlOnEnd: false,
                });
            }
        }
        if ($('.ps-gallery--image').length) {
            $('.ps-gallery--image').lightGallery({
                selector: '.ps-gallery__item',
                thumbnail: true,
                share: false,
                fullScreen: false,
                autoplay: false,
                autoplayControls: false,
                actualSize: false,
                closable: true,
                hideControlOnEnd: false,
            });
        }

        if ($('.ps-video').length) {
            $('.ps-video').lightGallery({
                thumbnail: false,
                share: false,
                fullScreen: false,
                autoplay: false,
                autoplayControls: false,
                actualSize: false,
                closable: true,
            });
        }
    }

    function backToTop() {
        let scrollPos = 0;
        let element = $('#back2top');
        $(window).scroll(function () {
            let scrollCur = $(window).scrollTop();
            if (scrollCur > scrollPos) {
                // scroll down
                if (scrollCur > 500) {
                    element.addClass('active');
                } else {
                    element.removeClass('active');
                }
            } else {
                // scroll up
                element.removeClass('active');
            }

            scrollPos = scrollCur;
        });

        element.on('click', function () {
            $('html, body').animate(
                {
                    scrollTop: '0px',
                },
                800
            );
        });
    }

    function modalInit() {
        let modal = $('.ps-modal');
        if (modal.length) {
            if (modal.hasClass('active')) {
                $('body').css('overflow-y', 'hidden');
            }
        }
        modal.find('.ps-modal__close, .ps-btn--close').on('click', function (e) {
            e.preventDefault();
            $(this)
                .closest('.ps-modal')
                .removeClass('active');
        });
        $('.ps-modal-link').on('click', function (e) {
            e.preventDefault();
            let target = $(this).attr('href');
            $(target).addClass('active');
            $('body').css('overflow-y', 'hidden');
        });
        $('.ps-modal').on('click', function (event) {
            if (!$(event.target).closest('.ps-modal__container').length) {
                modal.removeClass('active');
                $('body').css('overflow-y', 'auto');
            }
        });
    }

    function searchInit() {
        let searchbox = $('.ps-search');
        $('.ps-search-btn').on('click', function (e) {
            e.preventDefault();
            searchbox.addClass('active');
        });
        searchbox.find('.ps-btn--close').on('click', function (e) {
            e.preventDefault();
            searchbox.removeClass('active');
        });
    }

    function countDown() {
        let time = $('.ps-countdown');
        time.each(function () {
            let el = $(this),
                value = $(this).data('time');
            let countDownDate = new Date(value).getTime();
            let timeout = setInterval(function () {
                let now = new Date().getTime(),
                    distance = countDownDate - now;
                let days = Math.floor(distance / (1000 * 60 * 60 * 24)),
                    hours = Math.floor(
                        (distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)
                    ),
                    minutes = Math.floor(
                        (distance % (1000 * 60 * 60)) / (1000 * 60)
                    ),
                    seconds = Math.floor((distance % (1000 * 60)) / 1000);
                el.find('.days').html(days < 10 ? '0' + days : days);
                el.find('.hours').html(hours < 10 ? '0' + hours : hours);
                el.find('.minutes').html(minutes < 10 ? '0' + minutes : minutes);
                el.find('.seconds').html(seconds < 10 ? '0' + seconds : seconds);
                if (distance < 0) {
                    clearInterval(timeout);
                    el.closest('.ps-section').hide();
                }
            }, 1000);
        });
    }

    function productFilterToggle() {
        $('.ps-filter__trigger').on('click', function (e) {
            e.preventDefault();
            let el = $(this);
            el.find('.ps-filter__icon').toggleClass('active');
            el.closest('.ps-filter')
                .find('.ps-filter__content')
                .slideToggle();
        });
        if ($('.ps-sidebar--home').length > 0) {
            $('.ps-sidebar--home > .ps-sidebar__header > a').on(
                'click',
                function (e) {
                    e.preventDefault();
                    $(this)
                        .closest('.ps-sidebar--home')
                        .children('.ps-sidebar__content')
                        .slideToggle();
                }
            );
        }
    }

    function mainSlider() {
        let homeBanner = $('.ps-carousel--animate');
        homeBanner.slick({
            autoplay: true,
            rtl: isRTL,
            speed: 1000,
            lazyLoad: 'progressive',
            arrows: false,
            fade: true,
            dots: true,
            prevArrow: "<i class='slider-prev ba-back'></i>",
            nextArrow: "<i class='slider-next ba-next'></i>",
        });
    }

    function subscribePopup() {
        let subscribe = $('#subscribe'),
            time = subscribe.data('time');
        setTimeout(function () {
            if (subscribe.length > 0) {
                subscribe.addClass('active');
                $('body').css('overflow', 'hidden');
            }
        }, time);
        $('.ps-popup__close').on('click', function (e) {
            e.preventDefault();
            $(this)
                .closest('.ps-popup')
                .removeClass('active');
            $('body').css('overflow', 'auto');
        });
        $('#subscribe').on('click', function (event) {
            if (!$(event.target).closest('.ps-popup__content').length) {
                subscribe.removeClass('active');
                $('body').css('overflow-y', 'auto');
            }
        });
    }

    function stickySidebar() {
        let sticky = $('.ps-product--sticky'),
            stickySidebar,
            checkPoint = 992,
            windowWidth = $(window).innerWidth();
        if (sticky.length > 0) {
            stickySidebar = new StickySidebar(
                '.ps-product__sticky .ps-product__info',
                {
                    topSpacing: 20,
                    bottomSpacing: 20,
                    containerSelector: '.ps-product__sticky',
                }
            );
            if ($('.sticky-2').length > 0) {
                let stickySidebar2 = new StickySidebar(
                    '.ps-product__sticky .sticky-2',
                    {
                        topSpacing: 20,
                        bottomSpacing: 20,
                        containerSelector: '.ps-product__sticky',
                    }
                );
            }
            if (checkPoint > windowWidth) {
                stickySidebar.destroy();
                stickySidebar2.destroy();
            }
        } else {
            return false;
        }
    }

    function accordion() {
        let accordion = $('.ps-accordion');
        accordion.find('.ps-accordion__content').hide();
        $('.ps-accordion.active')
            .find('.ps-accordion__content')
            .show();
        accordion.find('.ps-accordion__header').on('click', function (e) {
            e.preventDefault();
            if (
                $(this)
                    .closest('.ps-accordion')
                    .hasClass('active')
            ) {
                $(this)
                    .closest('.ps-accordion')
                    .removeClass('active');
                $(this)
                    .closest('.ps-accordion')
                    .find('.ps-accordion__content')
                    .slideUp(350);
            } else {
                $(this)
                    .closest('.ps-accordion')
                    .addClass('active');
                $(this)
                    .closest('.ps-accordion')
                    .find('.ps-accordion__content')
                    .slideDown(350);
                $(this)
                    .closest('.ps-accordion')
                    .siblings('.ps-accordion')
                    .find('.ps-accordion__content')
                    .slideUp();
            }
            $(this)
                .closest('.ps-accordion')
                .siblings('.ps-accordion')
                .removeClass('active');
            $(this)
                .closest('.ps-accordion')
                .siblings('.ps-accordion')
                .find('.ps-accordion__content')
                .slideUp();
        });
    }

    function progressBar() {
        let progress = $('.ps-progress');
        progress.each(function () {
            let value = $(this).data('value');
            $(this)
                .find('span')
                .css({
                    width: value + '%',
                });
        });
    }

    function select2Config() {
        $('select.ps-select').select2({
            placeholder: $(this).data('placeholder'),
            minimumResultsForSearch: -1,
            templateSelection: function (state) {
                return jQuery.trim(state.text);
            }
        });
    }

    function carouselNavigation() {
        let prevBtn = $('.ps-carousel__prev'),
            nextBtn = $('.ps-carousel__next');
        prevBtn.on('click', function (e) {
            e.preventDefault();
            let target = $(this).attr('href');
            $(target).trigger('prev.owl.carousel', [1000]);
        });
        nextBtn.on('click', function (e) {
            e.preventDefault();
            let target = $(this).attr('href');
            $(target).trigger('next.owl.carousel', [1000]);
        });
    }

    function filterSlider() {
        let nonLinearSlider = document.getElementById('nonlinear');
        if (typeof nonLinearSlider != 'undefined' && nonLinearSlider != null) {
            noUiSlider.create(nonLinearSlider, {
                connect: true,
                behaviour: 'tap',
                start: [0, 1000],
                range: {
                    min: 0,
                    '10%': 100,
                    '20%': 200,
                    '30%': 300,
                    '40%': 400,
                    '50%': 500,
                    '60%': 600,
                    '70%': 700,
                    '80%': 800,
                    '90%': 900,
                    max: 1000,
                },
            });
            let nodes = [
                document.querySelector('.ps-slider__min'),
                document.querySelector('.ps-slider__max'),
            ];
            nonLinearSlider.noUiSlider.on('update', function (values, handle) {
                nodes[handle].innerHTML = Math.round(values[handle]);
            });
        }
    }

    function handleLiveSearch() {
        $('body').on('click', function (e) {
            if (
                $(e.target).closest('.ps-form--search-header') ||
                e.target.className === '.ps-form--search-header'
            ) {
                $('.ps-panel--search-result').removeClass('active');
            }
        });
    }

    const reviewList = function () {
        let $reviewListWrapper = $('body').find('.comment-list');
        const $loadingSpinner = $('body').find('.loading-spinner');

        $loadingSpinner.addClass('d-none');

        const fetchData = (url, hasAnimation = false) => {
            $.ajax({
                url: url,
                type: 'GET',
                beforeSend: function () {
                    $loadingSpinner.removeClass('d-none');

                    if (hasAnimation) {
                        $('html, body').animate({
                            scrollTop: `${$('.product-reviews-container').offset().top}px`,
                        }, 1500);
                    }
                },
                success: function (res) {
                    $reviewListWrapper.html(res.data);
                    $('.product-reviews-container .product-reviews-header').html(res.message);

                    let $galleries = $('.product-reviews-container .block__images');
                    if ($galleries.length) {
                        $galleries.map((index, value) => {
                            if (!$(value).data('lightGallery')) {
                                $(value).lightGallery({
                                    selector: 'a',
                                    thumbnail: true,
                                    share: false,
                                    fullScreen: false,
                                    autoplay: false,
                                    autoplayControls: false,
                                    actualSize: false,
                                });
                            }
                        });
                    }
                }, complete: function () {
                    $loadingSpinner.addClass('d-none');
                }
            })
        }

        if ($reviewListWrapper.length < 1) {
            return;
        }

        fetchData($reviewListWrapper.data('url'));

        $reviewListWrapper.on('click', '.ps-pagination ul li.page-item a', function (e) {
            e.preventDefault()

            const href = $(this).attr('href')

            if (href === '#') {
                return
            }

            fetchData(href, true)
        })
    }

    function sliderMainConfig() {
        let target = $('.owl-main-slider');
        if (target.length > 0) {
            target.each(function () {
                let el = $(this),
                    dataAuto = el.data('owl-auto'),
                    dataLoop = el.data('owl-loop'),
                    dataSpeed = el.data('owl-speed'),
                    dataGap = el.data('owl-gap'),
                    dataNav = el.data('owl-nav'),
                    dataDots = el.data('owl-dots'),
                    dataAnimateIn = el.data('owl-animate-in')
                        ? el.data('owl-animate-in')
                        : '',
                    dataAnimateOut = el.data('owl-animate-out')
                        ? el.data('owl-animate-out')
                        : '',
                    dataDefaultItem = el.data('owl-item'),
                    dataItemXS = el.data('owl-item-xs'),
                    dataItemSM = el.data('owl-item-sm'),
                    dataItemMD = el.data('owl-item-md'),
                    dataItemLG = el.data('owl-item-lg'),
                    dataItemXL = el.data('owl-item-xl'),
                    dataNavLeft = el.data('owl-nav-left')
                        ? el.data('owl-nav-left')
                        : "<i class='icon-chevron-left'></i>",
                    dataNavRight = el.data('owl-nav-right')
                        ? el.data('owl-nav-right')
                        : "<i class='icon-chevron-right'></i>",
                    duration = el.data('owl-duration'),
                    datamouseDrag =
                        el.data('owl-mousedrag') == 'on' ? true : false;
                // Check how many items we have
                let itemCount = target.children('div, span, a, img, h1, h2, h3, h4, h5, h5').length;

                if (itemCount >= 2) {
                    // Normal carousel initialization for multiple items
                    el.addClass('owl-carousel').owlCarousel({
                        rtl: isRTL,
                        animateIn: dataAnimateIn,
                        animateOut: dataAnimateOut,
                        margin: dataGap,
                        autoplay: dataAuto,
                        autoplayTimeout: dataSpeed,
                        autoplayHoverPause: true,
                        loop: dataLoop,
                        nav: dataNav,
                        mouseDrag: datamouseDrag,
                        touchDrag: true,
                        autoplaySpeed: duration,
                        navSpeed: duration,
                        dotsSpeed: duration,
                        dragEndSpeed: duration,
                        navText: [dataNavLeft, dataNavRight],
                        dots: dataDots,
                        items: dataDefaultItem,
                        responsive: {
                            0: {
                                items: dataItemXS,
                            },
                            480: {
                                items: dataItemSM,
                            },
                            768: {
                                items: dataItemMD,
                            },
                            992: {
                                items: dataItemLG,
                            },
                            1200: {
                                items: dataItemXL,
                            },
                            1680: {
                                items: dataDefaultItem,
                            },
                        },
                    });
                } else if (itemCount === 1) {
                    // Special handling for single item
                    el.addClass('owl-carousel single-item-carousel').owlCarousel({
                        rtl: isRTL,
                        items: 1,
                        dots: false,
                        nav: false,
                        loop: false,
                        mouseDrag: false,
                        touchDrag: false,
                        responsive: {
                            0: {
                                items: 1,
                            },
                            480: {
                                items: 1,
                            },
                            768: {
                                items: 1,
                            },
                            992: {
                                items: 1,
                            },
                            1200: {
                                items: 1,
                            },
                            1680: {
                                items: 1,
                            },
                        }
                    });
                }
            });
        }
    }

    $(function () {
        backgroundImage();
        owlCarouselConfig();
        sliderMainConfig();
        siteToggleAction();
        subMenuToggle();
        productFilterToggle();
        tabs();
        slickConfig();
        productLightbox();
        rating();
        backToTop();
        stickyHeader();
        mapConfig();
        modalInit();
        searchInit();
        countDown();
        mainSlider();
        stickySidebar();
        accordion();
        progressBar();
        select2Config();
        carouselNavigation();
        filterSlider();
        handleLiveSearch();
        reviewList()
    });

    // Bootstrap 5 tooltip initialization
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))

    $('#product-quickview').on('shown.bs.modal', function () {
        $('.ps-product--quickview .ps-product__images').slick('setPosition');
    });

    // Quick Shop event handlers
    document.addEventListener('ecommerce.quick-shop.before-send', function (e) {
        const { element, modal } = e.detail;
        element.addClass('loading');
        modal.find('.modal-body').html('<div class="ps-loading"><div class="ps-loading__spinner"></div></div>');
        
        // Mark modal to prevent URL updates
        modal.addClass('quick-shop-no-url-update');
    });

    document.addEventListener('ecommerce.quick-shop.completed', function (e) {
        const { element, modal } = e.detail;
        element.removeClass('loading');
        
        // Initialize quantity buttons - using the same logic as product detail page
        $(modal).find('.product__qty .up').on('click', function(event) {
            event.preventDefault();
            event.stopPropagation();
            let currentVal = parseInt($(this).closest('.product__qty').find('.qty-input').val(), 10) || 0;
            $(this).closest('.product__qty').find('.qty-input').val(currentVal + 1).prop('placeholder', currentVal + 1).trigger('change');
        });
        
        $(modal).find('.product__qty .down').on('click', function(event) {
            event.preventDefault();
            event.stopPropagation();
            let currentVal = parseInt($(this).closest('.product__qty').find('.qty-input').val(), 10) || 1;
            if (currentVal > 1) {
                $(this).closest('.product__qty').find('.qty-input').val(currentVal - 1).prop('placeholder', currentVal - 1).trigger('change');
            }
        });
        
        // Handle modal close button (Bootstrap 5)
        $(modal).find('.modal-close').on('click', function(e) {
            e.preventDefault();
            const bsModal = bootstrap.Modal.getInstance(modal[0]);
            if (bsModal) {
                bsModal.hide();
            }
        });
        
        // Prevent URL updates when selecting attributes in quick shop modal
        $(modal).find('[data-bb-toggle="product-form"]').attr('data-update-url', 'false');
        
        // Override window.history methods temporarily for quick shop modal
        const originalPushState = window.history.pushState;
        const originalReplaceState = window.history.replaceState;
        
        // Create a flag to track if we're in quick shop context
        $(modal).find('.product-attributes').attr('data-no-url-update', 'true');
        
        // Override the methods
        window.history.pushState = function() {
            // Check if the call is coming from within quick shop modal
            const isQuickShop = $('.product-attributes[data-no-url-update="true"]').length > 0;
            if (!isQuickShop) {
                return originalPushState.apply(window.history, arguments);
            }
            // Do nothing if in quick shop modal
        };
        
        window.history.replaceState = function() {
            // Check if the call is coming from within quick shop modal
            const isQuickShop = $('.product-attributes[data-no-url-update="true"]').length > 0;
            if (!isQuickShop) {
                return originalReplaceState.apply(window.history, arguments);
            }
            // Do nothing if in quick shop modal
        };
        
        // Restore original methods when modal is hidden
        $(modal).on('hidden.bs.modal', function() {
            window.history.pushState = originalPushState;
            window.history.replaceState = originalReplaceState;
            $(modal).find('.product-attributes').removeAttr('data-no-url-update');
        });
    });

    $(window).on('load', function () {
        $('body').addClass('loaded');
    });

    let collapseBreadcrumb = function () {
        $('ul.breadcrumb li').each(function () {
            let $this = $(this);
            if (!$this.is(':first-child') && !$this.is(':nth-child(2)') && !$this.is(':last-child')) {
                if (!$this.is(':nth-child(3)')) {
                    $this.find('a').closest('li').hide();
                } else {
                    $this.find('a').hide();
                    $this.find('.extra-breadcrumb-name').text('...').show();
                }
            }
        });
    }

    if ($(window).width() < 768) {
        collapseBreadcrumb();
    }

    $(window).on('resize', function () {
        collapseBreadcrumb();
    });

    $(document).ready(function() {
        initMegaMenu()
    })

    document.addEventListener('ecommerce.categories-dropdown.success', () => {
        initMegaMenu()
    })
    
    // Destroy existing carousel instances in a container
    function destroyOwlCarousel(container) {
        let target = container ? $(container).find('.owl-carousel') : $('.owl-carousel');
        target.each(function() {
            let el = $(this);
            if (el.data('owl.carousel')) {
                el.trigger('destroy.owl.carousel');
                el.removeClass('owl-carousel owl-loaded');
                el.find('.owl-stage-outer').children().unwrap();
            }
        });
    }
    
    // Reinitialize sliders for shortcodes
    function reinitializeShortcodeSliders(container) {
        // Use setTimeout to ensure DOM is fully ready
        setTimeout(function() {
            // First destroy any existing carousels to avoid conflicts
            destroyOwlCarousel(container);
            
            // Then initialize Owl Carousel sliders
            setTimeout(function() {
                initOwlCarousel(container);
            }, 50);
            
            // Initialize tab functionality for product collections
            if ($(container).find('.ps-tab-list').length > 0) {
                // Initialize the tab list as owl carousel if it has the class
                if ($(container).find('.ps-tab-list.owl-slider').length > 0) {
                    setTimeout(function() {
                        initOwlCarousel($(container).find('.ps-tab-list').parent());
                    }, 100);
                }
                
                // Re-attach tab click handlers
                $(container).find('.ps-tab-list li a').off('click').on('click', function (e) {
                    e.preventDefault();
                    let target = $(this).attr('href');
                    $(this).closest('li').addClass('active');
                    $(this).closest('li').siblings('li').removeClass('active');
                    $(target).addClass('active');
                    $(target).siblings('.ps-tab').removeClass('active');
                    
                    // Destroy and reinitialize carousel in the activated tab
                    setTimeout(function() {
                        destroyOwlCarousel(target);
                        setTimeout(function() {
                            initOwlCarousel(target);
                        }, 50);
                    }, 100);
                });
                
                // Initialize carousel in the first active tab
                let activeTab = $(container).find('.ps-tab.active');
                if (activeTab.length > 0) {
                    setTimeout(function() {
                        initOwlCarousel(activeTab);
                    }, 150);
                }
            }
            
            // Reinitialize carousel navigation buttons
            if ($(container).find('.ps-carousel__prev, .ps-carousel__next').length > 0) {
                $(container).find('.ps-carousel__prev').off('click').on('click', function (e) {
                    e.preventDefault();
                    let target = $(this).attr('href');
                    $(target).trigger('prev.owl.carousel', [1000]);
                });
                
                $(container).find('.ps-carousel__next').off('click').on('click', function (e) {
                    e.preventDefault();
                    let target = $(this).attr('href');
                    $(target).trigger('next.owl.carousel', [1000]);
                });
            }
        }, 200);
    }
    
    // Use MutationObserver to watch for dynamically added content
    var observeDOM = (function(){
        var MutationObserver = window.MutationObserver || window.WebKitMutationObserver;
        
        return function(obj, callback){
            if (!obj || obj.nodeType !== 1) return;
            
            if (MutationObserver){
                var mutationObserver = new MutationObserver(callback);
                mutationObserver.observe(obj, { childList: true, subtree: true });
                return mutationObserver;
            }
            else if (window.addEventListener){
                obj.addEventListener('DOMNodeInserted', callback, false);
                obj.addEventListener('DOMNodeRemoved', callback, false);
            }
        }
    })();
    
    // Watch for DOM changes and initialize sliders
    observeDOM(document.body, function(mutations) {
        var hasOwlSlider = false;
        
        mutations.forEach(function(mutation) {
            if (mutation.addedNodes && mutation.addedNodes.length > 0) {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1) { // Element node
                        if ($(node).hasClass('owl-slider') || $(node).find('.owl-slider').length > 0) {
                            hasOwlSlider = true;
                        }
                    }
                });
            }
        });
        
        if (hasOwlSlider) {
            setTimeout(function() {
                initOwlCarousel();
            }, 100);
        }
    });
    
    // Listen for shortcode loaded event
    document.addEventListener('shortcode.loaded', function (event) {
        setTimeout(function() {
            initOwlCarousel();
            backgroundImage();
            
            // Re-initialize other components that might be needed
            tabs();
            rating();
            select2Config();
            countDown();
            productFilterToggle();
            
            // Initialize tooltips for new content
            // Bootstrap 5 tooltip initialization
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
        }, 100);
    });
    
    // Listen for AJAX content loaded
    $(document).on('ajaxContentLoaded', function(event, container) {
        if (container) {
            initOwlCarousel(container);
        }
    });
    
    // Listen for tab shown event to reinitialize carousels in tabs
    $(document).on('shown.bs.tab', 'a[data-bs-toggle="tab"]', function() {
        const tabPane = $($(this).attr('href'));
        if (tabPane.length) {
            setTimeout(function() {
                initOwlCarousel(tabPane);
            }, 100);
        }
    });
    
    // Handle product collection tab clicks
    $(document).on('click', '.product-collections-tab .nav-tabs .nav-link:not([data-loaded])', function (e) {
        e.preventDefault();
        const $this = $(e.currentTarget);
        const tabPanel = $this.closest('.product-collections-tab').find('#' + $this.data('ref'));
        const $template = $this.closest('.product-collections-tab').find('.product-collection-items').html();

        $.ajax({
            url: $this.data('url'),
            dataType: 'json',
            success: (res) => {
                if (res.error == false) {
                    tabPanel.html($template.replace('__data__', res.data?.reduce((html, item) => html + '<div class="item">' + item + '</div>', '')));
                    setTimeout(function() {
                        initOwlCarousel(tabPanel);
                    }, 100);
                    $this.attr('data-loaded', 1);
                }
            }
        });
    });

    // ========================================
    // Cart Functions
    // ========================================

    // Load/refresh mini cart via AJAX
    MartApp.loadCart = function() {
        const $cartContainers = $('.ps-cart--mobile');
        if ($cartContainers.length === 0) return;

        const cartUrl = window.siteConfig && window.siteConfig.ajaxCartUrl ? window.siteConfig.ajaxCartUrl : '/ajax/cart';

        $.ajax({
            url: cartUrl,
            type: 'GET',
            success: function(response) {
                // Response format: { data: { count: X, html: '...' } }
                const data = response.data || response;
                const html = data.html || data;
                const count = data.count;

                // Update cart HTML
                $cartContainers.html(html);

                // Update cart count from response
                if (count !== undefined) {
                    $('.header__extra.btn-shopping-cart span i').text(count);
                    $('[data-bb-value="cart-count"]').text(count);
                }
            },
            error: function(error) {
                console.error('Error loading cart:', error);
            }
        });
    };

    // Make loadCart available globally
    window.loadCart = MartApp.loadCart;

    // Also define loadAjaxCart for plugin compatibility
    window.loadAjaxCart = function(cartData) {
        // Update cart count from cart data
        if (cartData && cartData.count !== undefined) {
            $('.header__extra.btn-shopping-cart span i').text(cartData.count);
            $('[data-bb-value="cart-count"]').text(cartData.count);
        }
        // Reload cart HTML
        MartApp.loadCart();
    };

    // ========================================
    // Add to Cart Form Handler
    // ========================================

    // Handle add-to-cart form submission via AJAX
    MartApp.addProductToCart = function() {
        $(document).on('click', 'form.add-to-cart-form button[type=submit]', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const $form = $(this).closest('form.add-to-cart-form');
            const $btn = $(this);

            if (!$('.hidden-product-id').val()) {
                $btn.prop('disabled', true).addClass('btn-disabled');
                return;
            }

            $btn.addClass('loading btn-disabled button-loading').prop('disabled', true);

            $form.find('.error-message').hide();
            $form.find('.success-message').hide();

            let data = $form.serializeArray();
            data.push({ name: 'checkout', value: $btn.prop('name') === 'checkout' ? 1 : 0 });

            $.ajax({
                type: 'POST',
                url: $form.prop('action'),
                data: $.param(data),
                success: function(res) {
                    if (res.error) {
                        if (window.showError) {
                            window.showError(res.message);
                        }
                        if (res.data && res.data.next_url !== undefined) {
                            setTimeout(function() {
                                window.location.href = res.data.next_url;
                            }, 500);
                        }
                        return false;
                    }

                    if (res.data && res.data.next_url !== undefined) {
                        window.location.href = res.data.next_url;
                        return false;
                    }

                    if (window.showSuccess) {
                        window.showSuccess(res.message);
                    }

                    // Update cart count
                    if (res.data && res.data.count !== undefined) {
                        $('[data-bb-value="cart-count"]').text(res.data.count);
                        $('.header__extra.btn-shopping-cart span i').text(res.data.count);
                    }

                    // Load mini cart
                    MartApp.loadCart();

                    // Dispatch cart added event for up-sale refresh
                    document.dispatchEvent(
                        new CustomEvent('ecommerce.cart.added', {
                            detail: {
                                data: res.data,
                                element: $btn[0],
                                message: res.message
                            },
                        })
                    );
                },
                error: function(error) {
                    console.error('Error adding to cart:', error);
                },
                complete: function() {
                    $btn.removeClass('loading btn-disabled button-loading').prop('disabled', false);
                },
            });
        });
    };

    // Initialize add to cart handler
    MartApp.addProductToCart();

    // ========================================
    // Up-Sale and Cross-Sale Products Support
    // ========================================

    // Store up-sale section refresh URL globally
    MartApp.upsellRefreshUrl = null;

    // Initialize block lazy loading for up-sale and cross-sale sections
    MartApp.initBlockLazyLoading = function() {
        const $lazyElements = $(document).find('[data-bb-toggle="block-lazy-loading"]');

        $lazyElements.each(function() {
            const $element = $(this);
            const url = $element.data('url');

            if (!url) return;

            // Store up-sale URL for refresh
            if (url.includes('up-sale-products')) {
                MartApp.upsellRefreshUrl = url;
            }

            $.ajax({
                url: url,
                type: 'GET',
                success: function(response) {
                    const data = response.data || response;
                    $element.replaceWith(data);

                    // Update lazy load images
                    if (typeof window.LazyLoad !== 'undefined' && window.lazyLoadInstance) {
                        window.lazyLoadInstance.update();
                    }

                    // Initialize slick carousel for cross-sale section
                    MartApp.initSlickCarousel();

                    // Initialize up-sale bundle
                    MartApp.initUpSaleBundle();
                },
                error: function(error) {
                    console.error('Error loading lazy content:', error);
                }
            });
        });

        // Refresh up-sale section when cart is updated
        document.addEventListener('ecommerce.cart.added', MartApp.refreshUpSaleSection);
        document.addEventListener('ecommerce.cart.removed', MartApp.refreshUpSaleSection);

        // Handle cross-sale add-to-cart buttons
        $(document).on('click', '.ec-cross-sale-add-btn[data-bb-toggle="add-to-cart"]', function(e) {
            e.preventDefault();
            e.stopImmediatePropagation();

            const $btn = $(this);
            const url = $btn.data('url');
            const productId = $btn.data('id');

            if ($btn.hasClass('loading')) return;

            $btn.addClass('loading').prop('disabled', true);

            $.ajax({
                url: url,
                type: 'POST',
                data: { id: productId },
                success: function(response) {
                    if (response.error) {
                        if (window.showError) {
                            window.showError(response.message || 'Failed to add product to cart');
                        }
                    } else {
                        // Show success message
                        if (response.message && window.showSuccess) {
                            window.showSuccess(response.message);
                        }

                        // Update cart count
                        if (response.data && response.data.count !== undefined) {
                            $('.header__extra.btn-shopping-cart span i').text(response.data.count);
                            $('[data-bb-value="cart-count"]').text(response.data.count);
                        }

                        // Refresh cart
                        MartApp.loadCart();

                        // Dispatch event
                        document.dispatchEvent(new CustomEvent('ecommerce.cart.added', {
                            detail: { data: response.data, element: $btn[0] }
                        }));
                    }
                },
                error: function(error) {
                    console.error('Error adding to cart:', error);
                },
                complete: function() {
                    $btn.removeClass('loading').prop('disabled', false);
                }
            });
        });
    };

    // Initialize slick carousel for cross-sale section
    MartApp.initSlickCarousel = function() {
        const $carousel = $('.ec-cross-sale-carousel:not(.slick-initialized)');

        if ($carousel.length && typeof $.fn.slick !== 'undefined') {
            $carousel.slick({
                rtl: isRTL,
                slidesToShow: 5,
                slidesToScroll: 1,
                arrows: true,
                dots: false,
                infinite: false,
                appendArrows: $carousel.closest('.ec-cross-sale-slider').find('.ec-cross-sale-arrows'),
                prevArrow: '<button class="slick-prev" type="button"><i class="icon-chevron-left"></i></button>',
                nextArrow: '<button class="slick-next" type="button"><i class="icon-chevron-right"></i></button>',
                responsive: [
                    {
                        breakpoint: 1200,
                        settings: {
                            slidesToShow: 4
                        }
                    },
                    {
                        breakpoint: 992,
                        settings: {
                            slidesToShow: 3
                        }
                    },
                    {
                        breakpoint: 768,
                        settings: {
                            slidesToShow: 2
                        }
                    },
                    {
                        breakpoint: 480,
                        settings: {
                            slidesToShow: 1
                        }
                    }
                ]
            });
        }
    };

    // Refresh up-sale section when cart changes
    MartApp.refreshUpSaleSection = function() {
        if (!MartApp.upsellRefreshUrl) {
            return;
        }

        const $section = $('[data-upsale-bundle]');
        if ($section.length === 0) {
            return;
        }

        // Add loading state
        $section.css('opacity', '0.5');

        $.ajax({
            url: MartApp.upsellRefreshUrl,
            type: 'GET',
            success: function(response) {
                const data = response.data || response;
                $section.replaceWith(data);

                // Update lazy load images
                if (typeof window.LazyLoad !== 'undefined' && window.lazyLoadInstance) {
                    window.lazyLoadInstance.update();
                }

                // Re-initialize bundle
                MartApp.initUpSaleBundle();
            },
            error: function() {
                $section.css('opacity', '1');
            }
        });
    };

    // Initialize up-sale bundle functionality
    MartApp.initUpSaleBundle = function() {
        const $section = $('[data-upsale-bundle]');
        if ($section.length === 0) return;

        const $checkboxes = $section.find('[data-upsale-checkbox]');
        const $totalPrice = $section.find('[data-upsale-total-price]');
        const $addAllBtn = $section.find('[data-upsale-add-all]');

        // Currency formatting helper
        const formatPrice = function(price) {
            const dataConfig = $section.data('currency-config');
            const currencies = dataConfig || window.currencies || {};

            const decimals = currencies.decimals !== undefined ? currencies.decimals : 0;
            const thousandsSep = currencies.thousands_separator || ',';
            const decimalSep = currencies.decimal_separator || '.';
            const symbol = currencies.symbol || '$';
            const isPrefix = currencies.is_prefix !== undefined ? currencies.is_prefix : true;

            const regex = '\\d(?=(\\d{3})+$)';
            let priceArr = price.toFixed(Math.max(0, ~~decimals)).toString().split('.');
            let formattedPrice = priceArr[0].replace(new RegExp(regex, 'g'), '$&' + thousandsSep) +
                (priceArr[1] ? decimalSep + priceArr[1] : '');

            return isPrefix ? symbol + formattedPrice : formattedPrice + symbol;
        };

        // Update total price based on checked items
        const updateTotal = function() {
            let total = 0;
            let selectedCount = 0;

            $checkboxes.filter(':checked').each(function() {
                const price = parseFloat($(this).attr('data-price')) || 0;
                total += price;
                selectedCount++;
            });

            $totalPrice.text(formatPrice(total));
            $addAllBtn.prop('disabled', selectedCount === 0);
        };

        // Checkbox change handler
        $checkboxes.off('change.upsale').on('change.upsale', updateTotal);

        // Initialize total
        updateTotal();

        // Add all selected items to cart
        $addAllBtn.off('click.upsale').on('click.upsale', function(e) {
            e.preventDefault();

            const $btn = $(this);
            const selectedProducts = [];
            const parentProduct = $btn.data('parent-product');

            $checkboxes.filter(':checked').each(function() {
                const productId = $(this).attr('data-id');
                if (productId) {
                    selectedProducts.push(productId);
                }
            });

            if (selectedProducts.length === 0) return;

            // Disable button while processing
            $btn.addClass('loading').prop('disabled', true);

            let index = 0;
            let successCount = 0;

            const addNextProduct = function() {
                if (index >= selectedProducts.length) {
                    // All done - update UI
                    $btn.removeClass('loading');

                    // Uncheck all checkboxes
                    $checkboxes.prop('checked', false);
                    updateTotal();

                    // Refresh the section
                    MartApp.refreshUpSaleSection();

                    // Refresh cart
                    MartApp.loadCart();

                    // Show success message
                    if (successCount > 0 && window.showSuccess) {
                        window.showSuccess('Added ' + successCount + ' item(s) to cart');
                    }
                    return;
                }

                const productId = selectedProducts[index];
                $.ajax({
                    url: $btn.data('url'),
                    type: 'POST',
                    data: {
                        id: productId,
                        reference_product_for_upsale: parentProduct
                    },
                    success: function() {
                        successCount++;
                        index++;
                        addNextProduct();
                    },
                    error: function() {
                        index++;
                        addNextProduct();
                    }
                });
            };

            addNextProduct();
        });

        // Individual add buttons
        $section.find('[data-upsale-add-btn]').off('click.upsale').on('click.upsale', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const $btn = $(this);
            const $item = $btn.closest('[data-upsale-bundle-item]');
            const $checkbox = $item.find('[data-upsale-checkbox]');
            const parentProduct = $addAllBtn.data('parent-product') || $btn.data('parent-product');
            // Use .attr() to get the updated DOM attribute value
            const productId = $btn.attr('data-id');
            const addUrl = $btn.data('url');

            // Add loading state
            $btn.addClass('loading').prop('disabled', true);

            $.ajax({
                url: addUrl,
                type: 'POST',
                data: {
                    id: productId,
                    reference_product_for_upsale: parentProduct
                },
                success: function(response) {
                    // Check for error response
                    if (response.error) {
                        if (window.showError) {
                            window.showError(response.message || 'Failed to add product to cart');
                        }
                        $btn.removeClass('loading').prop('disabled', false);
                        return;
                    }

                    // Show success message
                    if (response.message && window.showSuccess) {
                        window.showSuccess(response.message);
                    }

                    // Check the checkbox
                    $checkbox.prop('checked', true);
                    updateTotal();

                    // Update cart count
                    if (response.data && response.data.count !== undefined) {
                        $('.header__extra.btn-shopping-cart span i').text(response.data.count);
                        $('[data-bb-value="cart-count"]').text(response.data.count);
                    }

                    // Refresh cart
                    MartApp.loadCart();

                    // Dispatch event for other handlers
                    document.dispatchEvent(
                        new CustomEvent('ecommerce.cart.added', {
                            detail: { data: response.data, element: $btn[0] }
                        })
                    );

                    // Refresh section
                    MartApp.refreshUpSaleSection();
                },
                error: function(error) {
                    console.error('Error adding to cart:', error);
                },
                complete: function() {
                    $btn.removeClass('loading').prop('disabled', false);
                }
            });
        });

        // Variation attribute change handler
        $section.find('.ec-upsell-attributes .product-filter-item').off('change.upsale').on('change.upsale', function() {
            // Skip if disabled
            if ($(this).prop('disabled')) return;

            const $attrs = $(this).closest('.ec-upsell-attributes');
            const $item = $attrs.closest('[data-upsale-bundle-item]');
            const url = $attrs.data('target');

            if (!url) return;

            // Collect attributes in the format: attributes[slug]=id
            const data = { attributes: {} };
            $attrs.find('.product-filter-item:checked').each(function() {
                const slug = $(this).closest('.attribute-swatches-wrapper, .ec-upsell-attribute-group').data('slug');
                if (slug) {
                    data.attributes[slug] = $(this).val();
                }
            });

            $.ajax({
                url: url,
                type: 'GET',
                data: data,
                success: function(res) {
                    if (res.data) {
                        const variationId = res.data.id;
                        let price = res.data.sale_price || res.data.price;
                        const errorMessage = res.data.error_message;
                        const unavailableAttrIds = res.data.unavailable_attribute_ids || [];

                        // Update attribute availability
                        $attrs.find('.ec-upsell-attribute-option').each(function() {
                            const $option = $(this);
                            const attrId = parseInt($option.data('id'));
                            const $input = $option.find('input[type="radio"]');

                            if (unavailableAttrIds.includes(attrId)) {
                                $option.addClass('disabled').attr('title', 'Not available');
                                $input.prop('disabled', true);
                            } else {
                                $option.removeClass('disabled').removeAttr('title');
                                $input.prop('disabled', false);
                            }
                        });

                        // Only update IDs if valid variation found
                        if (variationId && !errorMessage) {
                            // Update hidden variation ID
                            $item.find('.ec-upsell-variation-id').val(variationId);
                            // Use .attr() to update DOM attributes directly
                            $item.find('[data-upsale-checkbox]').attr('data-id', variationId);
                            $item.find('[data-upsale-add-btn]').attr('data-id', variationId).prop('disabled', false);

                            // Apply bundle discount to variation price
                            if (price) {
                                const $checkbox = $item.find('[data-upsale-checkbox]');
                                const bundleDiscount = parseFloat($checkbox.attr('data-bundle-discount')) || 0;
                                const bundleDiscountType = $checkbox.attr('data-bundle-discount-type');

                                if (bundleDiscount > 0) {
                                    if (bundleDiscountType === 'percent') {
                                        price = price - (price * bundleDiscount / 100);
                                    } else {
                                        price = Math.max(0, price - bundleDiscount);
                                    }
                                }

                                $checkbox.attr('data-price', price);
                                updateTotal();
                            }
                        } else if (errorMessage) {
                            $item.find('[data-upsale-add-btn]').prop('disabled', true);
                        }
                    }
                }
            });
        });
    };

    // Initialize on document ready
    $(document).ready(function() {
        // Initialize lazy loading for up-sale and cross-sale sections
        MartApp.initBlockLazyLoading();
    });
})(jQuery);
