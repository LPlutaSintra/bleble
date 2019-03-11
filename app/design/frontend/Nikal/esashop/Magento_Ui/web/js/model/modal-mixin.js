define([
    'jquery',
    'mage/utils/wrapper'
], function ($, wrapper) {
    'use strict';

    return function (modal) {

        modal.prototype.openModal = wrapper.wrap(modal.prototype.openModal, function(original) {

            var result = original();
            $('.' + this.options.overlayClass).appendTo('.modal-popup._show');
            //Setting z-index of inner-wrap to 900 so that it is actually clickable and not hiding behind the overlay
            $('.modal-custom').css('z-index', 1001);
            return result;
        });

        return modal;
    };
});