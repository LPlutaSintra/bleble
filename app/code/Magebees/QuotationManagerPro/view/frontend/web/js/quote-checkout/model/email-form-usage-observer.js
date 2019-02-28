define(
    [
        'ko',
        'jquery',
        'uiComponent',
        'Magento_Checkout/js/model/shipping-service',
        'Magebees_QuotationManagerPro/js/quote-checkout/model/quote-checkout-model-selector',
        'Magebees_QuotationManagerPro/js/quote-checkout/checkout-data-quotation'
    ],
    function(ko, $, Component, shippingService, selector, checkoutQuotationData) {
        "use strict";

        /**
         * This is the model that handles the form behavior
         */
        return Component.extend({

            /**
             * Flag for the variables that are fired by the loaded shipping service.
             */
            loaded: false,

            /**
             * Flag for the guests fields need to be shown
             */
            showGuestField: ko.observable(false),

            /**
             * Flag for the non-guest fields need to be shown
             */
            showNonGuestField: ko.observable(false),

            /**
             * Flag for all the fields if they are allowed to be shown
             */
            showFields: ko.observable(false),

            /**
             * Init component
             */
            initialize: function () {
                var self = this;
                this._super();

                this.showGuestField.extend({ notify: 'always' });
                this.showNonGuestField.extend({ notify: 'always' });
                this.showFields.extend({ notify: 'always' });

                if (!this.allowToUseForm()) {
                    this.initAllowToUseForm();
                } else {
                    /** Check the remove fields value of the email */
                    shippingService.isLoading.subscribe(function(isLoading){
                        if (!isLoading && !this.loaded) {
                            this.initAllowToUseForm();
                            setTimeout(function(){
                                self.initCopyNameService();
                            }, 1500);
                            this.loaded = true;

                            if (selector.hasLoginModel()) {
                                selector.getLoginModel().emailHasChanged();
                            }

                            if (selector.hasGuestModel()) {
                                selector.getGuestCheckoutModel().requestAccount.subscribe(
                                    function(){self.updateFields()}
                                );
                            }
                        }
                    }, this);
                }
            },

            /**
             * Init the allow to use form.
             */
            initAllowToUseForm: function () {
                var self = this;

                /** Init fields */
                self.updateFields();

                if (selector.hasLoginModel()) {
                    /** Subscribe to email change */
                    selector.getLoginModel().email.subscribe(function () {
                        self.updateFields()
                    });

                    /** Subscribe to password field */
                    selector.getLoginModel().isPasswordVisible.subscribe(function () {
                        self.updateFields()
                    });
                }
            },

            /**
             * Update the showGuestField, showNonGuestField and showFields
             * variable by the validForShow and requestAccount
             */
            updateFields: function () {
                var validForShow = true, requestAccount = true, configAllowForm = this.allowToUseForm();

                if (selector.hasLoginModel()) {
                    validForShow = !selector.getLoginModel().isPasswordVisible() && this.isLoginValid();
                }

                if (this.allowToUseGuest()) {
                    if (selector.hasGuestModel()) {
                        requestAccount = selector.getGuestModel().requestAccount();
                    }
                }

                this.showGuestField(validForShow && !requestAccount && configAllowForm);
                this.showNonGuestField(validForShow && requestAccount && configAllowForm);
                this.showFields(validForShow && configAllowForm);
            },

            /**
             * Checks if the email is valid
             *
             * @returns {boolean}
             */
            isLoginValid: function () {
                if (selector.getLoginFormField().first().val()) {
                    selector.getLoginForm().validation();
                    return Boolean(selector.getLoginFormField().valid());
                }

                return false;
            },

            /**
             * Update the firstname and lastname in the quotation form each time requestAccount is changed.
             */
            initCopyNameService: function () {
                if (selector.hasGuestModel()) {
                    selector.getGuestModel().requestAccount.subscribe(function () {
                        this.copyNames();
                    }, this);
                }
            },

            /**
             * Copy the first name and last name to the quotation fields.
             */
            copyNames: function () {
                this.updateQuotationField('firstname');
                this.updateQuotationField('lastname');
            },

            /**
             * Update a quotation field
             * KO will be notified by the change function on the input.
             *
             * @param name
             */
            updateQuotationField: function (name) {
                var field = this.getField(name, 'quotationFieldData');
                field.val(this.getAddressAttribute(name));
                field.change();
            },

            /**
             * Get an address attribute.
             * Fallback:
             *      1)  field on billing
             *      2)  field on shipping
             *      3)  field on quotation
             *
             * @param name
             * @returns string
             */
            getAddressAttribute: function (name) {
                var value = '';

                if (this.getField(name, 'billingAddress').val()) {
                    value = this.getField(name, 'billingAddress').val();
                } else if (this.getField(name, 'shippingAddress').val()) {
                    value =  this.getField(name, 'shippingAddress').val();
                } else {
                    value =  this.getField(name, 'quotationFieldData').val()
                }

                return value;
            },

            /**
             * Get a field
             *
             * @param fieldName
             * @param type
             * @returns {*|jQuery|HTMLElement}
             */
            getField: function (fieldName, type) {
                return $('[name="'+type+'.'+fieldName+'"] input');
            },

            /**
             * Get allow to use guest mode
             * @returns boolean
             */
            allowToUseGuest: function () {
                return Boolean(checkoutQuotationData.getQuotationConfigDataFromData().allowGuest);
            },

            /**
             * Get allow to use form
             * @returns boolean
             */
            allowToUseForm: function () {
                return Boolean(checkoutQuotationData.getQuotationConfigDataFromData().allowForm);
            }

        })();
    }
);
