/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'matchMedia',
    'jquery/ui',
    'jquery/jquery.mobile.custom',
    'mage/translate'
], function ($, mediaCheck) {
    'use strict';

    /**
     * Menu Widget - this widget is a wrapper for the jQuery UI Menu
     */
    $.widget('mage.menu', $.ui.menu, {
        options: {
            responsive: false,
            expanded: false,
            showDelay: 42,
            hideDelay: 300,
            mediaBreakpoint: '(max-width: 991px)',
            breakPoint: 991
        },

        /**
         * @private
         */
        _create: function () {
            var self = this;

            this._super();
            $(window).on('resize', function () {
                self.element.find('.submenu-reverse').removeClass('submenu-reverse');
            });
        },

        /**
         * @private
         */
        _init: function () {
            this._super();

            if (this.options.expanded === true) {
                this.isExpanded();
            }

            if (this.options.responsive === true) {
                mediaCheck({
                    media: this.options.mediaBreakpoint,
                    entry: $.proxy(function () {
                        this._toggleMobileMode();
                    }, this),
                    exit: $.proxy(function () {
                        this._toggleDesktopMode();
                    }, this)
                });
            }

            this._assignControls()._listen();
            this._setActiveMenu();
        },

        focus: function( event, item ) {

            var nested, focused;
            this.blur( event, event && event.type === "focus" );

            this._scrollIntoView( item );

            this.active = item.first();
            focused = this.active.children( "a" ).addClass( "ui-state-focus" );

            //Nikal add class to body
            $('body').addClass('navigation-open');

            // Only update aria-activedescendant if there's a role
            // otherwise we assume focus is managed elsewhere
            if ( this.options.role ) {
                this.element.attr( "aria-activedescendant", focused.attr( "id" ) );
            }

            // Highlight active parent menu item, if any
            this.active
                .parent()
                .closest( ".ui-menu-item" )
                .children( "a:first" )
                .addClass( "ui-state-active" );

            if ( event && event.type === "keydown" ) {
                this._close();
            } else {
                this.timer = this._delay(function() {
                    this._close();
                }, this.delay );
            }

            nested = item.children( ".ui-menu" );
            if ( nested.length && event && ( /^mouse/.test( event.type ) ) ) {
                this._startOpening(nested);
            }
            this.activeMenu = item.parent();

            this._trigger( "focus", event, { item: item } );
        },

        collapseAll: function( event, all ) {
            clearTimeout( this.timer );
            this.timer = this._delay(function() {
                // If we were passed an event, look for the submenu that contains the event
                var currentMenu = all ? this.element :
                    $( event && event.target ).closest( this.element.find( ".ui-menu" ) );

                // If we found no valid submenu ancestor, use the main menu to close all sub menus anyway
                console.log(currentMenu);
                if ( !currentMenu.length ) {

                    currentMenu = this.element;
                }

                if(!currentMenu.hasClass('level0') && !currentMenu.hasClass('level1') && !currentMenu.hasClass('level2')){
                    //Nikal remove class to body
                    $('body').removeClass('navigation-open');
                }

                this._close( currentMenu );

                this.blur( event );
                this.activeMenu = currentMenu;
            }, this.delay );



        },

        /**
         * @return {Object}
         * @private
         */
        _assignControls: function () {
            this.controls = {
                toggleBtn: $('[data-action="toggle-nav"]'),
                swipeArea: $('.nav-sections')
            };

            return this;
        },

        /**
         * @private
         */
        _listen: function () {
            var controls = this.controls,
                toggle = this.toggle;

            this._on(controls.toggleBtn, {
                'click': toggle
            });
            this._on(controls.swipeArea, {
                'swipeleft': toggle
            });
        },

        /**
         * Toggle.
         */
        toggle: function () {
            var html = $('html');

            if (html.hasClass('nav-open')) {
                html.removeClass('nav-open');
                setTimeout(function () {
                    html.removeClass('nav-before-open');
                }, this.options.hideDelay);
            } else {
                html.addClass('nav-before-open');
                setTimeout(function () {
                    html.addClass('nav-open');
                }, this.options.showDelay);
            }
        },

        /**
         * Tries to figure out the active category for current page and add appropriate classes:
         *  - 'active' class for active category
         *  - 'has-active' class for all parents of active category
         *
         *  First, checks whether current URL is URL of category page,
         *  otherwise tries to retrieve category URL in case of current URL is product view page URL
         *  which has category tree path in it.
         *
         * @return void
         * @private
         */
        _setActiveMenu: function () {
            var currentUrl = window.location.href.split('?')[0];

            if (!this._setActiveMenuForCategory(currentUrl)) {
                this._setActiveMenuForProduct(currentUrl);
            }
        },

        /**
         * Looks for category with provided URL and adds 'active' CSS class to it if it was not set before.
         * If menu item has parent categories, sets 'has-active' class to all af them.
         *
         * @param {String} url - possible category URL
         * @returns {Boolean} - true if active category was founded by provided URL, otherwise return false
         * @private
         */
        _setActiveMenuForCategory: function (url) {

            var urlWithoutHttp = url.replace('http:',''),
                activeCategoryLink = this.element.find('a[href="' + urlWithoutHttp + '"]'),
                classes,
                classNav;

            if (!activeCategoryLink || !activeCategoryLink.hasClass('ui-corner-all')) {

                //category was not found by provided URL
                return false;
            } else if (!activeCategoryLink.parent().hasClass('active')) {

                activeCategoryLink.parent().addClass('active');
                classes = activeCategoryLink.parent().attr('class');
                classNav = classes.match(/(nav\-)[0-9]+(\-[0-9]+)+/gi);

                if (classNav) {
                    this._setActiveParent(classNav[0]);
                }
            }

            return true;
        },

        /**
         * Sets 'has-active' CSS class to all parent categories which have part of provided class in childClassName
         *
         * @example
         *  childClassName - 'nav-1-2-3'
         *  CSS class 'has-active' will be added to categories have 'nav-1-2' and 'nav-1' classes
         *
         * @param {String} childClassName - Class name of active category <li> element
         * @return void
         * @private
         */
        _setActiveParent: function (childClassName) {
            var parentElement,
                parentClass = childClassName.substr(0, childClassName.lastIndexOf('-'));

            if (parentClass.lastIndexOf('-') !== -1) {
                parentElement = this.element.find('.' + parentClass);

                if (parentElement) {
                    parentElement.addClass('has-active');
                }
                this._setActiveParent(parentClass);
            }
        },

        /**
         * Tries to retrieve category URL from current URL and mark this category as active
         * @see _setActiveMenuForCategory(url)
         *
         * @example
         *  currentUrl - http://magento.com/category1/category12/product.html,
         *  category URLs has extensions .phtml - http://magento.com/category1.phtml
         *  method sets active category which has URL http://magento.com/category1/category12.phtml
         *
         * @param {String} currentUrl - current page URL without parameters
         * @return void
         * @private
         */
        _setActiveMenuForProduct: function (currentUrl) {
            var categoryUrlExtension,
                lastUrlSection,
                possibleCategoryUrl,
                //retrieve first category URL to know what extension is used for category URLs
                firstCategoryUrl = this.element.find('> li a').attr('href');

            if (firstCategoryUrl) {
                lastUrlSection = firstCategoryUrl.substr(firstCategoryUrl.lastIndexOf('/'));
                categoryUrlExtension = lastUrlSection.lastIndexOf('.') !== -1 ?
                    lastUrlSection.substr(lastUrlSection.lastIndexOf('.')) : '';

                possibleCategoryUrl = currentUrl.substr(0, currentUrl.lastIndexOf('/')) + categoryUrlExtension;
                this._setActiveMenuForCategory(possibleCategoryUrl);
            }
        },

        /**
         * Add class for expanded option.
         */
        isExpanded: function () {
            var subMenus = this.element.find(this.options.menus),
                expandedMenus = subMenus.find(this.options.menus);

            expandedMenus.addClass('expanded');
        },

        /**
         * @param {jQuery.Event} event
         * @private
         */
        _activate: function (event) {
            window.location.href = this.active.find('> a').attr('href');
            this.collapseAll(event);
        },

        _open: function( submenu ) {
            var position = (this.options.position !== false) ? $.extend({
                of: this.active
            }, this.options.position ) : false;

            clearTimeout( this.timer );
            this.element.find( ".ui-menu" ).not( submenu.parents( ".ui-menu" ) )
                .hide()
                .attr( "aria-hidden", "true" );

            submenu
                .show()
                .removeAttr( "aria-hidden" )
                .attr( "aria-expanded", "true" );

            if(position !== false) {
                submenu.position(position);
            }
        },

        /**
         * @param {jQuery.Event} event
         * @private
         */
        _keydown: function (event) {
            var match, prev, character, skip, regex,
                preventDefault = true;

            /* eslint-disable max-depth */
            /**
             * @param {String} value
             */
            function escape(value) {
                return value.replace(/[\-\[\]{}()*+?.,\\\^$|#\s]/g, '\\$&');
            }

            if (this.active.closest(this.options.menus).attr('aria-expanded') != 'true') { //eslint-disable-line eqeqeq

                switch (event.keyCode) {
                    case $.ui.keyCode.PAGE_UP:
                        this.previousPage(event);
                        break;

                    case $.ui.keyCode.PAGE_DOWN:
                        this.nextPage(event);
                        break;

                    case $.ui.keyCode.HOME:
                        this._move('first', 'first', event);
                        break;

                    case $.ui.keyCode.END:
                        this._move('last', 'last', event);
                        break;

                    case $.ui.keyCode.UP:
                        this.previous(event);
                        break;

                    case $.ui.keyCode.DOWN:
                        if (this.active && !this.active.is('.ui-state-disabled')) {
                            this.expand(event);
                        }
                        break;

                    case $.ui.keyCode.LEFT:
                        this.previous(event);
                        break;

                    case $.ui.keyCode.RIGHT:
                        this.next(event);
                        break;

                    case $.ui.keyCode.ENTER:
                    case $.ui.keyCode.SPACE:
                        this._activate(event);
                        break;

                    case $.ui.keyCode.ESCAPE:
                        this.collapse(event);
                        break;
                    default:
                        preventDefault = false;
                        prev = this.previousFilter || '';
                        character = String.fromCharCode(event.keyCode);
                        skip = false;

                        clearTimeout(this.filterTimer);

                        if (character === prev) {
                            skip = true;
                        } else {
                            character = prev + character;
                        }

                        regex = new RegExp('^' + escape(character), 'i');
                        match = this.activeMenu.children('.ui-menu-item').filter(function () {
                            return regex.test($(this).children('a').text());
                        });
                        match = skip && match.index(this.active.next()) !== -1 ?
                            this.active.nextAll('.ui-menu-item') :
                            match;

                        // If no matches on the current filter, reset to the last character pressed
                        // to move down the menu to the first item that starts with that character
                        if (!match.length) {
                            character = String.fromCharCode(event.keyCode);
                            regex = new RegExp('^' + escape(character), 'i');
                            match = this.activeMenu.children('.ui-menu-item').filter(function () {
                                return regex.test($(this).children('a').text());
                            });
                        }

                        if (match.length) {
                            this.focus(event, match);

                            if (match.length > 1) {
                                this.previousFilter = character;
                                this.filterTimer = this._delay(function () {
                                    delete this.previousFilter;
                                }, 1000);
                            } else {
                                delete this.previousFilter;
                            }
                        } else {
                            delete this.previousFilter;
                        }
                }
            } else {
                switch (event.keyCode) {
                    case $.ui.keyCode.DOWN:
                        this.next(event);
                        break;

                    case $.ui.keyCode.UP:
                        this.previous(event);
                        break;

                    case $.ui.keyCode.RIGHT:
                        if (this.active && !this.active.is('.ui-state-disabled')) {
                            this.expand(event);
                        }
                        break;

                    case $.ui.keyCode.ENTER:
                    case $.ui.keyCode.SPACE:
                        this._activate(event);
                        break;

                    case $.ui.keyCode.LEFT:
                    case $.ui.keyCode.ESCAPE:
                        this.collapse(event);
                        break;
                    default:
                        preventDefault = false;
                        prev = this.previousFilter || '';
                        character = String.fromCharCode(event.keyCode);
                        skip = false;

                        clearTimeout(this.filterTimer);

                        if (character === prev) {
                            skip = true;
                        } else {
                            character = prev + character;
                        }

                        regex = new RegExp('^' + escape(character), 'i');
                        match = this.activeMenu.children('.ui-menu-item').filter(function () {
                            return regex.test($(this).children('a').text());
                        });
                        match = skip && match.index(this.active.next()) !== -1 ?
                            this.active.nextAll('.ui-menu-item') :
                            match;

                        // If no matches on the current filter, reset to the last character pressed
                        // to move down the menu to the first item that starts with that character
                        if (!match.length) {
                            character = String.fromCharCode(event.keyCode);
                            regex = new RegExp('^' + escape(character), 'i');
                            match = this.activeMenu.children('.ui-menu-item').filter(function () {
                                return regex.test($(this).children('a').text());
                            });
                        }

                        if (match.length) {
                            this.focus(event, match);

                            if (match.length > 1) {
                                this.previousFilter = character;
                                this.filterTimer = this._delay(function () {
                                    delete this.previousFilter;
                                }, 1000);
                            } else {
                                delete this.previousFilter;
                            }
                        } else {
                            delete this.previousFilter;
                        }
                }
            }

            /* eslint-enable max-depth */
            if (preventDefault) {
                event.preventDefault();
            }
        },

        /**
         * @private
         */
        _toggleMobileMode: function () {
            var subMenus;

            //Hack to group span's
            $('.ui-menu-item.group').find('> span').each(function() {
                $(this).wrap('<a href="#" class="no-redirect"></a>');
            });

            $(document).on('click', '.toggle-menu', function(e) {
                e.preventDefault();

                var navigation = $('.nav-sections');
                var html = $('html');
                var openedMenus = $('.ui-opened');

                if(navigation.hasClass('on')) {
                    navigation.removeClass('on').addClass('off');

                    setTimeout(function () {
                        navigation.removeClass('off');
                        html.removeClass('overflow-block');
                        openedMenus.removeClass('ui-opened is-locked');

                    }, 300);
                } else {
                    navigation.addClass('on');
                    html.addClass('overflow-block');
                }
            });

            $(this.element).off('mouseenter mouseleave');
            this._on({
                'click .ui-menu-item:has(a)': function (event) {

                    var target = $(event.target).closest('.ui-menu-item');

                    if(target.hasClass('item-type-cms-block')) {
                        return;
                    }

                    if(target.parent().hasClass('submenu') && !$(event.target).hasClass('back-to-parent-href') ) {
                        target.parent().addClass('is-locked');
                    }

                    event.preventDefault();

                    if($(window).width() > this.options.breakPoint) {
                        if (!target.hasClass('level-top') || !target.has('.ui-menu').length) {
                            window.location.href = target.find('> a').attr('href');
                        }
                    } else {
                        if(target.find('> .submenu').length === 0) {
                            window.location.href = target.find('> a').attr('href');
                        }
                    }
                },

                /**
                 * @param {jQuery.Event} event
                 */
                'click .ui-menu-item:has(.ui-state-active)': function (event) {
                    this.collapseAll(event, true);
                }
            });

            subMenus = this.element.find('.submenu').parent();

            subMenus.addClass('ui-menu-parent');

            $.each(subMenus, $.proxy(function (index, item) {

                var category = null,
                    categoryUrl = null,
                    menu = $(item).find('> .submenu'),
                    elm = null,
                    all = true,
                    noRedirect = false,
                    backCategory = null;

                if($(item).find('> a').length > 0) {
                    category = $(item).find('> a span').not('.ui-menu-icon').text();
                    categoryUrl = $(item).find('> a').attr('href');
                    elm = '<a class="back-to-parent-href" href="'+categoryUrl+'" >'+category+'</a>';

                    if($(item).find('> a').hasClass('no-redirect')) {
                        noRedirect = true;
                    }
                } else {
                    category = $(item).find('> span').text();
                    elm = '<span class="back-to-parent-href">'+category+'</span>';
                    all = false
                }

                backCategory = '<li class="back-to-parent">'+elm+'</li>';

                if(all){
                    var hrefClass = (noRedirect)? 'no-redirect-href' : '',
                        liClass = (noRedirect)? 'no-redirect-item' : '';

                    this.categoryLink = $('<a>')
                        .attr('href', categoryUrl)
                        .attr('class', hrefClass)
                        .text($.mage.__('All ') + category);

                    this.categoryParent = $('<li>')
                        .attr('class', liClass)
                        .addClass('ui-menu-item all-category')
                        .html(this.categoryLink);
                }

                if (menu.find('.all-category').length === 0) {
                    if(all) {
                        menu.prepend(this.categoryParent);
                    }

                    menu.prepend(backCategory);
                }

                $(item).find('.back-to-parent').on('click', function(e){
                    e.preventDefault();

                    $(this).parent().removeClass('ui-opened');
                    $(this).parent().parents('.is-locked').removeClass('is-locked');
                })

            }, this));
        },

        /**
         * @private
         */
        _toggleDesktopMode: function () {
            var categoryParent, html;

            //Unhack to group span's
            $('.ui-menu-item.group').find('> a.no-redirect > *').each(function() {
                $(this).unwrap();
            });

            //Submenu parent class
            var subMenus = this.element.find('.submenu').parent();

            subMenus.addClass('ui-menu-parent');

            this._on({
                /**
                 * Prevent focus from sticking to links inside menu after clicking
                 * them (focus should always stay on UL during navigation).
                 */
                'mousedown .ui-menu-item > a': function (event) {
                    event.preventDefault();
                },

                /**
                 * Prevent focus from sticking to links inside menu after clicking
                 * them (focus should always stay on UL during navigation).
                 */
                'click .ui-state-disabled > a': function (event) {
                    event.preventDefault();
                },

                /**
                 * @param {jQuer.Event} event
                 */
                'click .ui-menu-item:has(a)': function (event) {
                    var target = $(event.target).closest('.ui-menu-item');

                    if (!this.mouseHandled && target.not('.ui-state-disabled').length) {
                        this.select(event);

                        // Only set the mouseHandled flag if the event will bubble, see #9469.
                        if (!event.isPropagationStopped()) {
                            this.mouseHandled = true;
                        }

                        // Open submenu on click
                        if (target.has('.ui-menu').length) {
                            this.expand(event);
                        } else if (!this.element.is(':focus') &&
                            $(this.document[0].activeElement).closest('.ui-menu').length
                        ) {
                            // Redirect focus to the menu
                            this.element.trigger('focus', [true]);

                            // If the active item is on the top level, let it stay active.
                            // Otherwise, blur the active item since it is no longer visible.
                            if (this.active && this.active.parents('.ui-menu').length === 1) { //eslint-disable-line
                                clearTimeout(this.timer);
                            }
                        }
                    }
                },

                /**
                 * @param {jQuery.Event} event
                 */
                'mouseenter .ui-menu-item': function (event) {
                    var target = $(event.currentTarget),
                        submenu = this.options.menus,
                        ulElement,
                        ulElementWidth,
                        width,
                        targetPageX,
                        rightBound;

                    if (target.has(submenu)) {
                        ulElement = target.find(submenu);
                        ulElementWidth = ulElement.outerWidth(true);
                        width = target.outerWidth() * 2;
                        targetPageX = target.offset().left;
                        rightBound = $(window).width();

                        if (ulElementWidth + width + targetPageX > rightBound) {
                            ulElement.addClass('submenu-reverse');
                        }

                        if (targetPageX - ulElementWidth < 0) {
                            ulElement.removeClass('submenu-reverse');
                        }
                    }

                    // Remove ui-state-active class from siblings of the newly focused menu item
                    // to avoid a jump caused by adjacent elements both having a class with a border
                    target.siblings().children('.ui-state-active').removeClass('ui-state-active');
                    this.focus(event, target);
                },

                /**
                 * @param {jQuery.Event} event
                 */
                'mouseleave': function (event) {
                    this.collapseAll(event, true);
                },

                /**
                 * Mouse leave.
                 */
                'mouseleave .ui-menu': 'collapseAll'
            });

            categoryParent = this.element.find('.all-category');
            html = $('html');

            categoryParent.remove();

            if (html.hasClass('nav-open')) {
                html.removeClass('nav-open');
                setTimeout(function () {
                    html.removeClass('nav-before-open');
                }, 300);
            }
        },

        /**
         * @param {*} handler
         * @param {Number} delay
         * @return {Number}
         * @private
         */
        _delay: function (handler, delay) {
            var instance = this,

                /**
                 * @return {*}
                 */
                handlerProxy = function () {
                    return (typeof handler === 'string' ? instance[handler] : handler).apply(instance, arguments);
                };

            return setTimeout(handlerProxy, delay || 0);
        },

        /**
         * @param {jQuery.Event} event
         */
        expand: function (event) {
            var newItem = this.active &&
                this.active
                    .children('.ui-menu')
                    .children('.ui-menu-item')
                    .first();

            if (newItem && newItem.length) {

                if (newItem.closest('.ui-menu').is(':visible') &&
                    newItem.closest('.ui-menu').has('.all-categories') &&
                        $(window).width() > this.options.breakpoint
                ) {
                    return;
                }

                if($(event.target).is('.back-to-parent-href')) {
                    return;
                }

                newItem.parent().addClass('ui-opened');

                this._open(newItem.parent());

                // Delay so Firefox will not hide activedescendant change in expanding submenu from AT
                this._delay(function () {
                    this.focus(event, newItem);
                });
            }
        },

        /**
         * @param {jQuery.Event} event
         */
        select: function (event) {
            var ui;

            this.active = this.active || $(event.target).closest('.ui-menu-item');

            if (this.active.is('.all-category')) {
                this.active = $(event.target).closest('.ui-menu-item');
            }
            ui = {
                item: this.active
            };

            if (!this.active.has('.ui-menu').length) {
                this.collapseAll(event, true);
            }

            this._trigger('select', event, ui);
        }
    });

    $.widget('mage.navigation', $.mage.menu, {
        options: {
            responsiveAction: 'wrap', //option for responsive handling
            maxItems: null, //option to set max number of menu items
            container: '#menu', //container to check against navigation length
            moreText: $.mage.__('more'),
            breakpoint: 768
        },

        /**
         * @private
         */
        _init: function () {
            var that, responsive;

            this._super();

            that = this;
            responsive = this.options.responsiveAction;

            this.element
                .addClass('ui-menu-responsive')
                .attr('responsive', 'main');

            this.setupMoreMenu();
            this.setMaxItems();

            //check responsive option
            if (responsive == 'onResize') { //eslint-disable-line eqeqeq
                $(window).on('resize', function () {
                    if ($(window).width() > that.options.breakpoint) {
                        that._responsive();
                        $('[responsive=more]').show();
                    } else {
                        that.element.children().show();
                        $('[responsive=more]').hide();
                    }
                });
            } else if (responsive == 'onReload') { //eslint-disable-line eqeqeq
                this._responsive();
            }
        },

        /**
         * Setup more menu.
         */
        setupMoreMenu: function () {
            var moreListItems = this.element.children().clone(),
                moreLink = $('<a>' + this.options.moreText + '</a>');

            moreListItems.hide();

            moreLink.attr('href', '#');

            this.moreItemsList = $('<ul>')
                .append(moreListItems);

            this.moreListContainer = $('<li>')
                .append(moreLink)
                .append(this.moreItemsList);

            this.responsiveMenu = $('<ul>')
                .addClass('ui-menu-more')
                .attr('responsive', 'more')
                .append(this.moreListContainer)
                .menu({
                    position: {
                        my: 'right top',
                        at: 'right bottom'
                    }
                })
                .insertAfter(this.element);
        },

        /**
         * @private
         */
        _responsive: function () {
            var container = $(this.options.container),
                containerSize = container.width(),
                width = 0,
                items = this.element.children('li'),
                more = $('.ui-menu-more > li > ul > li a');

            items = items.map(function () {
                var item = {};

                item.item = $(this);
                item.itemSize = $(this).outerWidth();

                return item;
            });

            $.each(items, function (index) {
                var itemText = items[index].item
                    .find('a:first')
                    .text();

                width += parseInt(items[index].itemSize, null); //eslint-disable-line radix

                if (width < containerSize) {
                    items[index].item.show();

                    more.each(function () {
                        var text = $(this).text();

                        if (text === itemText) {
                            $(this).parent().hide();
                        }
                    });
                } else if (width > containerSize) {
                    items[index].item.hide();

                    more.each(function () {
                        var text = $(this).text();

                        if (text === itemText) {
                            $(this).parent().show();
                        }
                    });
                }
            });
        },

        /**
         * Set max items.
         */
        setMaxItems: function () {
            var items = this.element.children('li'),
                itemsCount = items.length,
                maxItems = this.options.maxItems,
                overflow = itemsCount - maxItems,
                overflowItems = items.slice(overflow);

            overflowItems.hide();

            overflowItems.each(function () {
                var itemText = $(this).find('a:first').text();

                $(this).hide();

                $('.ui-menu-more > li > ul > li a').each(function () {
                    var text = $(this).text();

                    if (text === itemText) {
                        $(this).parent().show();
                    }
                });
            });
        }
    });

    return {
        menu: $.mage.menu,
        navigation: $.mage.navigation
    };
});
