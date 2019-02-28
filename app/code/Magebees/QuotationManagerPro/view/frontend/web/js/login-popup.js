define([
        'jquery',
        'Magento_Customer/js/model/authentication-popup'
    ],
    function ($, authenticationPopup) {
        'use strict';

        return function (config, element) {
            $(element).click(function (event) {
                event.preventDefault();
                authenticationPopup.showModal();
                return false;
            });
        };
    }
);
