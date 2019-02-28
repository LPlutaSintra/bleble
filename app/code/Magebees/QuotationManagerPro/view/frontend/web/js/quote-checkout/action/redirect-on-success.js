define(
    [
        'Magebees_QuotationManagerPro/js/quote-checkout/model/resource-url-manager'
    ],
    function (resourceUrlManager) {
        'use strict';

        return {
            /**
             * Provide redirect to page
             */
            execute: function (quoteId) {
                var url = resourceUrlManager.getUrlForRedirectOnSuccess(quoteId, {id: quoteId});
                window.location.replace(url);
            }
        };
    }
);
