define([
    'uiComponent',
    'Magento_Customer/js/customer-data',
    'jquery',
    'ko',
    'quoteSidebar'
], function (Component, customerData, $, ko) {
    'use strict';

    var sidebarInitializedQuote = false;
    var addToQuoteCalls = 0;

    var miniquote = $("[data-block='miniquote']");
	
	/**start for update minicart when data is modified [these may cause multiple time request for update minicart ]*/ 
	var sections = ['cart'];
	customerData.invalidate(sections);
	customerData.reload(sections, true);
	/**end for update minicart when data is modified*/
    miniquote.on('dropdowndialogopen', function () {
        initSidebarQuote();
    });

    function initSidebarQuote() {
        if (!$('[data-role=product-item]').length) {
            return false;
        }
        miniquote.trigger('contentUpdated');
        if (sidebarInitializedQuote) {
            return false;
        }
	/*	 var sections = ['magebees_quote'];
            customerData.invalidate(sections);
            customerData.reload(sections, true);*/
		
        sidebarInitializedQuote = true;
        miniquote.quoteSidebar({
            "targetElement": "div.block.block-miniquote",
            "url": {
                "checkout": window.quotation.checkoutUrl,
                "update": window.quotation.updateItemQtyUrl,
                "remove": window.quotation.removeItemUrl
            },
            "button": {
                "checkout": "#top-quote-btn-checkout",
                "remove": "#mini-quote a.action.delete",
                "close": "#btn-miniquote-close"
            },
            "showquote": {
                "parent": "span.counter",
                "qty": "span.counter-number",
                "label": "span.counter-label"
            },
            "miniquote": {
                "list": "#mini-quote",
                "content": "#miniquote-content-wrapper",
                "qty": "div.items-total",
                "subtotal": "div.subtotal span.price",
				'maxItemsVisible': 3
            },
            "item": {
                "qty": ":input.quote-item-qty",
                "button": ":button.update-quote-item"
            },
            "confirmMessage": $.mage.__(
                'Are you sure you would like to remove this item from the quote?'
            )
        });
    }

    return Component.extend({
        quoteCartUrl: window.quotation.quoteCartUrl,
        initialize: function () {
            var self = this;
            this._super();

            //fix for cached data
           // customerData.reload(['magebees_quote'], true);
            customerData.reload(['magebees_quote'], false);			

            this.quote = customerData.get('magebees_quote');
            this.quote.subscribe(function () {
                addToQuoteCalls--;
                this.isLoading(addToQuoteCalls > 0);
                sidebarInitializedQuote = false;
                initSidebarQuote();
            }, this);
            $('[data-block="miniquote"]').on('contentLoading', function (event) {
                addToQuoteCalls++;
                self.isLoading(true);
            });
        },
        isLoading: ko.observable(false),
        initSidebarQuote: initSidebarQuote,
        closeSidebar: function () {
            var miniquote = $('[data-block="miniquote"]');
            miniquote.on('click', '[data-action="close"]', function (event) {
                event.stopPropagation();
                miniquote.find('[data-role="dropdownDialog"]').dropdownDialog("close");
            });
            return true;
        },
        getItemRenderer: function (productType) {
            return this.itemRenderer[productType] || 'defaultRenderer';
        }
    });
});
