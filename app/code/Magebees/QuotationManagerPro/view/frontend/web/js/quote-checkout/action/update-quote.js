define(
    [
        'jquery',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/place-order',
        'Magebees_QuotationManagerPro/js/quote-checkout/action/redirect-on-success',
        'Magebees_QuotationManagerPro/js/quote-checkout/model/resource-url-manager',
        'mage/translate',
        'Magento_Ui/js/model/messageList',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magebees_QuotationManagerPro/js/quote-checkout/checkout-data-quotation'
    ],
    function (
        $,
        quote,
        placeOrderService,
        redirectOnSuccessAction,
        resourceUrlManagerModel,
        $t,
        globalMessageList,
        fullScreenLoader,
        checkoutDataQuotation
    ) {
        'use strict';

        /**
         * This action handles the update quotation action
         */
        return function () {
            var quoteRequestUrl, quoteData;

            /**
             * The quote data that is being send wit the request
             *
             * @type {{cartId: *, form_key: *, quotation_data}}
             */
            quoteData = {
                cartId: quote.getQuoteId(),
                form_key: window.checkoutConfig.formKey,
                quotation_guest_field_data: JSON.stringify(checkoutDataQuotation.getQuotationGuestFieldsFromData()),
                quotation_field_data: JSON.stringify(checkoutDataQuotation.getQuotationFieldsFromData()),
                quotation_product_data: JSON.stringify(checkoutDataQuotation.getQuotationProductsFromData()),
                quotation_store_config_data: JSON.stringify(checkoutDataQuotation.getQuotationConfigDataFromData())
            };

            /**
             * Get request URL
             *
             * @type {*|string}
             */
            quoteRequestUrl = resourceUrlManagerModel.getUrlForUpdateQuote(quote, quoteData);

            /**
             * Request quotation update
             * 
             * @see Quotation/Controller/Quote/Ajax/UpdateQuote.php
             */
            return placeOrderService(quoteRequestUrl, quoteData, globalMessageList);
        };
    }
);
