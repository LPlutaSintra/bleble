define([
    'jquery',
    'Magebees_QuotationManagerPro/js/quote-checkout/checkout-data-quotation',
    'Magebees_QuotationManagerPro/js/quote-checkout/action/update-quote',
    'Magento_Checkout/js/model/full-screen-loader'
], function ($, checkoutDataQuotation, updateQuote, fullScreenLoader) {
    "use strict";

    /**
     * Bidding widget for placing a bid on the product detail page.
     */
    $.widget('bidding.update', {

        /**
         * The element options:
         * - Item ID is set in the element
         * - sessionProductKey is the key used on the session
         */
        options: {
            itemId: 0,
            sessionProductKey: undefined
        },

        /**
         * Add all the events on create
         *
         * @private
         */
        _create: function () {
            this.bindInputCheck();
            this.initInputValue();
        },

        /**
         * Update the session JS data on keyup
         */
        bindInputCheck: function () {
            var self = this;
            $(this.element).on('keyup', function (e) {
                self.updateData();
            });

            $(this.element).on('change', function (e) {
                self.saveData();
            });
        },

        /**
         * Init the input value by loading the value from the checkoutData
         */
        initInputValue: function() {
            var data = checkoutDataQuotation.getQuotationProductsFromData(),
                itemId = this.getItemId();

            if (typeof data[this.options.sessionProductKey] !== 'undefined'
                && typeof data[this.options.sessionProductKey][itemId] !== 'undefined') {
                this.setValue(data[this.options.sessionProductKey][itemId]);
            }

            this.toggleDisabled();
        },

        /**
         * Update the QuotationProducts session data
         */
        updateData: function () {
            var data = checkoutDataQuotation.getQuotationProductsFromData(),
                itemId = this.getItemId(),
                value = this.getValue();

            if (data.length == 0) {
                data = {};
            }

            if (typeof data[this.options.sessionProductKey] === 'undefined') {
                data[this.options.sessionProductKey] = {};
            }

            data[this.options.sessionProductKey][itemId] = value;

            checkoutDataQuotation.setQuotationProductsFromData(data);
        },

        /**
         * Save the new price to the session
         */
        saveData: function () {
            updateQuote().done(function() {
                fullScreenLoader.stopLoader(true);
            });
        },

        /**
         * Get the quote item id
         * @returns {number}
         */
        getItemId: function () {
            return this.options.itemId;
        },

        /**
         * Get the element price
         * @returns {*|jQuery}
         */
        getValue: function () {
            return $(this.element).val();
        },

        /**
         * Set value to the input
         * @param value
         */
        setValue: function (value) {
            $(this.element).val(value);
        },

        /**
         * Toggle disabled
         */
        toggleDisabled: function () {
            $(this.element).prop('disabled', function(i, v) { return !v; });
        }
    });

    return $.bidding.update;
});
