define([
    "jquery",
    "Magebees_QuotationManagerPro/quote/view/scripts"
], function (jQuery) {
    'use strict';

    var $el = jQuery('#edit_form'),
        config,
        baseUrl,
        quote,
        payment;

	
    if (!$el.length || !$el.data('quote-config')) {
        return;
    }

    config = $el.data('quote-config');
    baseUrl = $el.data('load-base-url');

    quote = new AdminQuote(config);
    quote.setLoadBaseUrl(baseUrl);

    payment = {
        switchMethod: quote.switchPaymentMethod.bind(quote)
    };

    window.quote = quote;
    window.payment = payment;
});