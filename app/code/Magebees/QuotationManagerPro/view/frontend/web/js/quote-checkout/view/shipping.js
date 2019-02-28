define(
    [
        'jquery',
        'Magento_Checkout/js/view/shipping',
        'ko',
        'Magebees_QuotationManagerPro/js/quote-checkout/action/place-quote',
        'Magebees_QuotationManagerPro/js/quote-checkout/action/update-quote',
        'Magento_Checkout/js/action/set-shipping-information',
        'Magento_Checkout/js/model/shipping-service',
        'Magento_Checkout/js/model/quote',
        'Magebees_QuotationManagerPro/js/quote-checkout/model/email-form-usage-observer'
    ],
    function (
        $,
        Component,
        ko,
        placeQuoteAction,
        updateQuoteAction,
        setShippingInformationAction,
        shippingService,
        quote,
        emailFormUsageObserver
    ) {
        'use strict';

        return Component.extend({
            allowToUseForm: emailFormUsageObserver.showNonGuestField
        });
    }
);
