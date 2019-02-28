var mobile = 992;

/* ------------------------------
 // Mobile toggle
 ------------------------------- */
require(['domReady', 'jquery'], function (domReady, $) {
    $(window).on('resize', function() {
        if($(window).outerWidth() < mobile) {
            $('body').addClass('is-mobile');
        } else {
            $('body').removeClass('is-mobile');
        }
    }).resize();
});
/* ------------------------------
 // Mobile toggle -- END --
 ------------------------------- */

/* ------------------------------
 // Slick
 ------------------------------- */
require(['jquery', 'slick'], function ($) {
    $(document).ready(function() {
        var items = $('[data-slick]');

        $(window).resize(function() {

            var defaultOptions = {
                slidesToShow: 1,
                slidesToScroll: 1,
                arrows: false,
                dots: true
            };

            $('[data-slick]').each(function(i, item){
                var options = Object.assign({}, defaultOptions, $(item).data('slick'));

                if($(item).hasClass('slick-initialized')) {
                    $(item).slick('setPosition');

                    return;
                }

                $(item).slick(options);
            });
        }).resize();

        //Force same height for all slides
        $(items).on('setPosition', function (event, slick) {

            var slides = $(event.target).find('.slick-slide');

            slides.height('auto');

            var slickTrack = $(event.target).find('.slick-track');
            var slickTrackHeight = $(slickTrack).height();

            slides.css('height', slickTrackHeight + 'px');
        });
    });
});

/* ------------------------------
 // Slick -- END --
 ------------------------------- */

/* ------------------------------
 // Sticky header
 ------------------------------- */
require(['jquery', 'jquery/ui', 'mage/dropdown'], function ($) {
    $(function () {
        var header = $('.page-header'),
            body = $('body'),
            initialOffset = 0;

        body.addClass('down');

        $(window).on('scroll', function() {
            var pageTopOffset = $(window).scrollTop();

            if(pageTopOffset > initialOffset) {
                body.addClass('down').removeClass('up');
            } else {
                body.addClass('up').removeClass('down');
            }

            if(pageTopOffset > initialOffset && !body.hasClass('scroll-off') && pageTopOffset > header.height()) {
                body.addClass('scroll-on');

                //Find all dialogs and close them
                $(document).find('.ui-dialog-content').dropdownDialog('close');

            } else if(pageTopOffset < initialOffset && pageTopOffset < (header.height())) {
                body.removeClass('scroll-on').addClass('scroll-off');

                //Find all dialogs and close them
                $(document).find('.ui-dialog-content').dropdownDialog('close');

                setTimeout(function() {
                    body.removeClass('scroll-off');
                }, 300);

            }

            initialOffset = pageTopOffset;
        });
    });
});
/* ------------------------------
 // Sticky header -- END --
 ------------------------------- */

/* ------------------------------
 // Wrap footer columns
 ------------------------------- */
require(['jquery'], function ($) {
    $(document).ready(function () {
        $('.footer-columns > .cols > .col').not('.no-collapse').each(function(i, item){
            var content = $(item).find('> *').not('h4:first-child(), h3:first-child()');

            content.wrapAll('<div class="col-content"></div>');

            $(item).find('>h4, >h3').first().on('click', function() {
                var colContent = $(this).next();

                if(colContent.parent().hasClass('on')) {
                    colContent.parent().removeClass('on');
                    return;
                }

                $(this).parents('.footer-columns').find('.on').removeClass('on');

                colContent.parent().toggleClass('on');
            });
        });
    });
});
/* ------------------------------
 // Wrap footer columns -- END --
 ------------------------------- */

/* ------------------------------
 // Mobile summary block - checkout
 ------------------------------- */
require(['domReady', 'jquery'], function (domReady, $) {
    $(function () {
        $(document).on('click', '.is-mobile .opc-block-summary .title', function () {
            $(this).parent().toggleClass('active');
            $(this).parent().find('.summary-wrapper').slideToggle();
        });
    });
});
/* ------------------------------
 // Wrap footer columns -- END --
 ------------------------------- */

/* ------------------------------
 // Toggle filters sidebar
 ------------------------------- */
require(['jquery'], function ($) {
    $(function () {
        var body = $('body'),
            filters = $('.layered-filter-block-container'),
            dir = 'left',
            isMobile = false;

        $(document).on('click', '.filters-trigger', function () {

            $(this).parent().toggleClass('active');

            body.toggleClass('sidebar-hidden filters-hidden');

            if(isMobile) {
                filters.slideToggle(300);

                return;
            }

            filters.toggle("slide", { direction: dir }, 300);
        });

        $(window).on('resize', function() {
            filters.attr('style', '');

            isMobile = ($(window).width() < 768);
        });
    });
});
/* ------------------------------
 // Toggle filters sidebar -- END --
 ------------------------------- */

/* ------------------------------
 // Mobile discount block - cart
 ------------------------------- */
require(['domReady', 'jquery'], function (domReady, $) {
    $(function () {
        $(document).on('click', '.is-mobile .cart-main-wrapper .discount .block-label', function () {
            $(this).parents('.discount').toggleClass('active');
            $(this).parent().find('.fieldset.coupon').slideToggle();
        });
    });
});
/* ------------------------------
 // Wrap footer columns -- END --
 ------------------------------- */

/* ------------------------------
 // Checkout address button for mobile
 ------------------------------- */
require(['domReady', 'jquery'], function (domReady, $) {
    $(function () {
        $(document).on('click', '.checkout-adderess > button', function () {
            $(this).parent().find('address').slideToggle();
        });
    });
});
/* ------------------------------
 // Wrap footer columns -- END --
 ------------------------------- */

/* ------------------------------
 // Dropdowns slide style
 ------------------------------- */
require(['jquery'], function ($) {
    var dropdown = $('.dropdown-slider-wrapper');

    dropdown.on('click', '[data-action="close"]', function (event) {
        event.stopPropagation();
        dropdown.find('.ui-dialog-content').dropdownDialog('close');
    });
});
/* ------------------------------
 // Search modal -- END --
 ------------------------------- */

/* ------------------------------
 // QTY buttons
 ------------------------------- */
require(['domReady', 'jquery'], function (domReady, $) {
    $(function () {
        $(document).on('click', '.qty-sub', function(e) {
            e.preventDefault();

            var input = $(this).next();

            if(input.val() > 0) input.val( parseInt(input.val()) - 1).trigger('change');
        });

        $(document).on('click', '.qty-add', function(e) {
            e.preventDefault();

            var input = $(this).prev();

            input.val( parseInt(input.val()) + 1).trigger('change');
        });
    });
});
/* ------------------------------
 // QTY buttons -- END --
 ------------------------------- */

/* ------------------------------
 // QTY Cart show/hide actions
 ------------------------------- */
require(['domReady', 'jquery'], function (domReady, $) {
    $(function () {
        $(document).on('change', '.cart-item-qty', function (e) {
            $(this).parents('.product-actions-wrapper').find('button.update-cart-item').show();
        });
    });
});
/* ------------------------------
 // QTY Cart show/hide actions -- END --
 ------------------------------- */

/* ------------------------------
 // QTY Cart show/hide minicart update action
 ------------------------------- */
require(['jquery'], function ($) {
    $('.cart-main-wrapper .qty').each(function(i, item){
        $(item).on('change', function(e) {
            $('.cart-main-wrapper .actions-wrapper').addClass('on');
        });
    });
});
/* ------------------------------
 // QTY Cart show/hide actions -- END --
 ------------------------------- */

/* ------------------------------
 // Modal swatches helper sizes
 ------------------------------- */
require(['jquery'], function ($) {
    $('.product-spedition li').on('click', function() {
        $(this).toggleClass('active');
    });
});
/* ------------------------------
 // Modal swatches helper sizes  -- END --
 ------------------------------- */

/* ------------------------------
 // Catalog filter submenus
 ------------------------------- */
require(['jquery'], function ($) {
    $(function () {

        $('.filter-options').find('.item-parent-categories .item.on').each(function(){
            $(this).parents('.item-parent-categories > .ln-items-cat-sub').slideDown('fast');
            $(this).parents('.item-parent-categories').addClass('on');
        });

        $(document).on('click', '.item-parent-categories > .parent', function() {
            $(this).parent().toggleClass('on');
            $(this).next().slideToggle('fast');
        });

    });
});
/* ------------------------------
 // Catalog filter submenus  -- END --
 ------------------------------- */

/* ------------------------------
 // Pin sticky elements
 ------------------------------- */
require(['domReady', 'jquery', 'stickyPlugin'], function (domReady, $) {
    function categoryListPin() {
        var categoryTopToolbarPin = $('.layer-product-list > .toolbar-first > .toolbar-products'),
            categoryBottomToolbar = $('.layer-product-list > .toolbar-last > .toolbar-products'),
            bottomOffset = categoryBottomToolbar.find(' > .pages').length > 0 ? categoryBottomToolbar.height() : 0;

        categoryTopToolbarPin.hcSticky({
            stickTo: '.layer-product-list',
            top: 50,
            bottomEnd: bottomOffset,
            responsive: {
                992: {
                    disable: true
                }
            }
        });
    }

    function categoryLayerPin() {

        var categoryTopToolbarPin = $('.layer-product-list > .toolbar-first > .toolbar-products'),
            categoryBottomToolbar = $('.layer-product-list > .toolbar-last > .toolbar-products'),
            categoryFiltersBlockPin = $('.layered-filter-block-container > .filter > .filter-content'),
            categoryTopToolbarHeight = (categoryTopToolbarPin.height() > 0) ? categoryTopToolbarPin.height() : 43,
            bottomOffset = categoryBottomToolbar.find(' > .pages').length > 0 ? categoryBottomToolbar.height() : 0;

        categoryFiltersBlockPin.hcSticky({
            stickTo: '.layered-filter-block-container',
            top: 50,
            bottomEnd: bottomOffset,
            responsive: {
                992: {
                    disable: true
                }
            },
            onStart: function(sticky) {
                $(this).css('top', sticky.top + categoryTopToolbarHeight);
            }
        });

    }

    function cartSummaryPin() {
        var cartPagePin = $('.cart-summary-wrapper');

        cartPagePin.hcSticky({
            stickTo: '.cart-container',
            top: 50,
            responsive: {
                992: {
                    disable: true
                }
            }
        });
    }

    $(function() {
        var productPagePin = $('.product-info-main'),
            productContainer = $('#layer-product-list'),
            layerContainer  = $('.layered-filter-block-container');

        productPagePin.hcSticky({
            stickTo: '.column.main',
            top: 70,
            bottomEnd: 40,
            responsive: {
                992: {
                    disable: true
                }
            }
        });

        //categoryListPin();
        //categoryLayerPin();
        cartSummaryPin();

        //productContainer.on('contentUpdated', function() {
        //    categoryListPin();
        //});
        //
        //layerContainer.on('contentUpdated', function() {
        //    categoryLayerPin();
        //});

    });
});
/* ------------------------------
 // Pin sticky elements  -- END --
 ------------------------------- */
