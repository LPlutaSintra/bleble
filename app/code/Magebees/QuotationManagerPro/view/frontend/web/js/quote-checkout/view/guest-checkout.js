define(
    [
        'jquery',
        'Magento_Ui/js/form/form',
        'ko',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/model/shipping-service',
        'Magebees_QuotationManagerPro/js/quote-checkout/checkout-data-quotation',
        'Magebees_QuotationManagerPro/js/quote-checkout/model/quote-checkout-model-selector',
        'uiRegistry'
    ],
    function (
        $,
        Component,
        ko,
        customer,
        shippingService,
        checkoutQuotationData,
        selector,
        registry
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Magebees_QuotationManagerPro/quote-checkout/view/guest-checkout'
            },

            loaded: false,
            loginSelector: '#customer-email-fieldset',
            isCustomerLoggedIn: customer.isLoggedIn(),

            showRequestShippingQuote: undefined,

            /** The toggle variable */
            requestAccount: ko.observable(customer.isLoggedIn()),

            /** Toggle action used in the template */
            toggleRequestAccount: function () {
                this.requestAccount(!this.requestAccount());
            },

            /** Field configuration for hiding and showing based of the toggle button */
            fieldConfig: [
                {selector: '#guest-fields', guestField: true},
                {selector: '#billing', guestField: false},
                {selector: '#shipping', guestField: false},
                {selector: '#opc-shipping_method', guestField: false}
            ],

            /**
             * Init component
             */
            initialize: function () {
                this._super();

                registry.async('checkoutProvider')(function (checkoutProvider) {
                    var quotationGuestFieldsData = checkoutQuotationData.getQuotationGuestFieldsFromData();

                    if (quotationGuestFieldsData) {
                        checkoutProvider.set(
                            'quotationGuestFieldData',
                            $.extend({}, checkoutProvider.get('quotationGuestFieldData'), quotationGuestFieldsData)
                        );
                    }

                    checkoutProvider.on('quotationGuestFieldData', function (quotationGuestFieldData) {
                        checkoutQuotationData.setQuotationGuestFieldsFromData(quotationGuestFieldData);
                    });
                });
            },

            /**
             * @return {exports.initObservable}
             */
            initObservable: function () {
                this._super();
                var self = this;

                /** Subscribe only if the customer is logged in otherwise the checkbox is invisible */
                if (!this.isCustomerLoggedIn) {
                    /** Subscribe to the button switcher */
                    self.requestAccount.subscribe(function (requestAccount) {
                        if (requestAccount) {
                            self.hideGuestFields();
                        } else {
                            self.showGuestFields();
                        }
                    });
                }

                /** Hide or show the guest fields based on the customer login */
                shippingService.isLoading.subscribe(function (isLoading) {
                    if (!isLoading && !this.loaded) {
                        this.loaded = true;

                        if (this.isCustomerLoggedIn) {
                            this.hideGuestFields();
                        } else {
                            this.showGuestFields();
                        }
                    }
                }, this);

                this.initShowRequestShippingQuote();

                /**
                 * Request account needs to be true when not using the guest functionality.
                 * You can change the in the Magento store configuration.
                 */
                if (!this.requestAccount()) {
                    this.requestAccount(!this.allowToUseGuest());
                }

                return this;
            },

            /**
             * Shows the guests fields and hides the customer fields
             */
            showGuestFields: function () {
                var self = this;
                $.each(self.fieldConfig, function (id, fieldConfig) {
                    if (fieldConfig.guestField) {
                        $(fieldConfig.selector).show();
                    } else {
                        $(fieldConfig.selector).hide();
                    }
                });
            },

            /**
             * Hides the guests fields and shows the customer fields
             */
            hideGuestFields: function () {
                var self = this;
                $.each(self.fieldConfig, function (id, fieldConfig) {
                    if (fieldConfig.guestField) {
                        $(fieldConfig.selector).hide();
                    } else {
                        $(fieldConfig.selector).show();
                    }
                });
            },

            /**
             * Hides all the fields
             */
            hideAllFields: function () {
                var self = this;
                $.each(self.fieldConfig, function (id, fieldConfig) {
                    $(fieldConfig.selector).hide();
                });
            },

            /**
             * Get allow to use guest mode
             * @returns boolean
             */
            allowToUseGuest: function () {
                return Boolean(checkoutQuotationData.getQuotationConfigDataFromData().allowGuest);
            },

            /**
             * Init hide request shipping quote checkbox
             */
            initShowRequestShippingQuote: function () {
                var self = this;

                self.showRequestShippingQuote = ko.computed(function () {
                    var passwordVisible = false, email = false;

                    if (selector.hasLoginModel()) {
                        passwordVisible = selector.getLoginModel().isPasswordVisible();
                        email = selector.getLoginModel().validateEmail();
                    }

                    return !self.isCustomerLoggedIn && self.allowToUseGuest() && !passwordVisible && email;
                });

                self.showRequestShippingQuote.extend({ notify: 'always' });
            }
        });
    }
);
