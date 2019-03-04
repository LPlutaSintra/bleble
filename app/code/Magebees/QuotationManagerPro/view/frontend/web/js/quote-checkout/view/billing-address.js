define(
    [
        'jquery',
        'ko',
        'Magento_Ui/js/form/form',
        'Magento_Customer/js/model/customer',
        'Magento_Customer/js/model/address-list',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/action/create-billing-address',
        'Magento_Checkout/js/action/select-billing-address',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/model/checkout-data-resolver',
        'Magento_Customer/js/customer-data',
        'Magento_Checkout/js/action/set-billing-address',
        'Magento_Ui/js/model/messageList',
        'mage/translate',
        'uiRegistry',
        'Magento_Checkout/js/model/address-converter',
        'Magento_Checkout/js/action/select-shipping-address',
        'Magebees_QuotationManagerPro/js/quote-checkout/model/email-form-usage-observer'
    ],
    function (
        $,
        ko,
        Component,
        customer,
        addressList,
        quote,
        createBillingAddress,
        selectBillingAddress,
        checkoutData,
        checkoutDataResolver,
        customerData,
        setBillingAddressAction,
        globalMessageList,
        $t,
        registry,
        addressConverter,
        selectShippingAddress,
        emailFormUsageObserver
    ) {
        'use strict';

        var lastSelectedBillingAddress = null,
            newAddressOption = {
                getAddressInline: function () {
                return $t('New Address');
            },
                customerAddressId: null
            },
            countryData = customerData.get('directory-data'),
            addressOptions = addressList().filter(function (address) {
                return address.getType() == 'customer-address';
            });

        addressOptions.push(newAddressOption);

        return Component.extend({
            defaults: {
                template: 'Magento_Checkout/billing-address'
            },
            currentBillingAddress: quote.billingAddress,
            addressOptions: addressOptions,
            customerHasAddresses: addressOptions.length > 1,
            isVirtual: ko.observable(quote.isVirtual()),
            allowToUseForm: emailFormUsageObserver.showNonGuestField,

            /**
             * Init component
             */
            initialize: function () {
                this._super();
                checkoutDataResolver.resolveBillingAddress();

                registry.async('checkoutProvider')(function (checkoutProvider) {
                    var billingAddressData = checkoutData.getBillingAddressFromData();

                    if (billingAddressData) {
                        checkoutProvider.set(
                            'billingAddress',
                            $.extend({}, checkoutProvider.get('billingAddress'), billingAddressData)
                        );
                    }
                    checkoutProvider.on('billingAddress', function (billingAddressData) {
                        checkoutData.setBillingAddressFromData(billingAddressData);
                    });
                });
            },

            /**
             * @return {exports.initObservable}
             */
            initObservable: function () {
                this._super()
                    .observe({
                        selectedAddress: null,
                        isAddressDetailsVisible: true,
                        isAddressFormVisible: !customer.isLoggedIn() || addressOptions.length == 1,
                        isAddressSameAsShipping: true,
                        saveInAddressBook: 1
                    });

                quote.billingAddress.subscribe(function (newAddress) {
                    if (quote.isVirtual()) {
                        this.isAddressSameAsShipping(false);
                    }

                    if (newAddress != null && newAddress.saveInAddressBook !== undefined) {
                        this.saveInAddressBook(newAddress.saveInAddressBook);
                    } else {
                        this.saveInAddressBook(1);
                    }

                }, this);

                return this;
            },

            canUseShippingAddress: ko.computed(function () {
                return !quote.isVirtual() && quote.shippingAddress() && quote.shippingAddress().canUseForBilling();
            }),

            /**
             * Get address options as text
             * @param {Object} address
             * @return {*}
             */
            addressOptionsText: function (address) {
                return address.getAddressInline();
            },

            /**
             * Select the shipping address
             * @return {Boolean}
             */
            useShippingAddress: function () {
                if (this.isAddressSameAsShipping()) {
                    selectBillingAddress(quote.shippingAddress());
                    if (window.checkoutConfig.reloadOnBillingAddress) {
                        setBillingAddressAction(globalMessageList);
                    }
                    this.isAddressDetailsVisible(true);
                } else {
                    lastSelectedBillingAddress = quote.billingAddress();
                    quote.billingAddress(null);
                    this.isAddressDetailsVisible(false);
                }
                checkoutData.setSelectedBillingAddress(null);

                return true;
            },

            /**
             * Update address action
             */
            updateAddress: function () {
                if (this.selectedAddress() && this.selectedAddress() != newAddressOption) {
                    selectBillingAddress(this.selectedAddress());
                    checkoutData.setSelectedBillingAddress(this.selectedAddress().getKey());
                    if (window.checkoutConfig.reloadOnBillingAddress) {
                        setBillingAddressAction(globalMessageList);
                    }
                } else {
                    this.source.set('params.invalid', false);
                    this.source.trigger(this.dataScopePrefix + '.data.validate');
                    if (this.source.get(this.dataScopePrefix + '.custom_attributes')) {
                        this.source.trigger(this.dataScopePrefix + '.custom_attributes.data.validate');
                    }

                    if (!this.source.get('params.invalid')) {
                        var addressData = this.source.get(this.dataScopePrefix),
                            newBillingAddress;

                        if (customer.isLoggedIn() && !this.customerHasAddresses) {
                            this.saveInAddressBook(1);
                        }
                        addressData.save_in_address_book = this.saveInAddressBook() ? 1 : 0;
                        newBillingAddress = createBillingAddress(addressData);

                        // New address must be selected as a billing address
                        selectBillingAddress(newBillingAddress);
                        checkoutData.setSelectedBillingAddress(newBillingAddress.getKey());
                        checkoutData.setNewCustomerBillingAddress(addressData);
                        setBillingAddressAction(globalMessageList);
                    }
                }

                if (this.isVirtual()) {
                    this.updateBillingAddress(newBillingAddress);
                }
            },

            /**
             * Update the billing address by the shipping address
             */
            updateByShippingAddress: function() {
                selectBillingAddress(quote.shippingAddress());
                return setBillingAddressAction(globalMessageList);
            },

            /**
             * Update the billing address
             * @param addressData
             */
            updateBillingAddress: function (addressData) {
                var billingAddress = quote.billingAddress();

                //Copy form data to quote shipping address object
                for (var field in addressData) {

                    if (addressData.hasOwnProperty(field) &&
                        billingAddress.hasOwnProperty(field) &&
                        typeof addressData[field] != 'function' &&
                        _.isEqual(billingAddress[field], addressData[field])
                    ) {
                        billingAddress[field] = addressData[field];
                    } else if (typeof addressData[field] != 'function' &&
                        !_.isEqual(billingAddress[field], addressData[field])) {
                        billingAddress = addressData;
                        break;
                    }
                }

                if (customer.isLoggedIn()) {
                    billingAddress.save_in_address_book = 1;
                }
                selectShippingAddress(billingAddress);
            },

            /**
             * Edit address action
             */
            editAddress: function () {
                lastSelectedBillingAddress = quote.billingAddress();
                quote.billingAddress(null);
                this.isAddressDetailsVisible(false);
            },

            /**
             * Cancel address edit action
             */
            cancelAddressEdit: function () {
                this.restoreBillingAddress();

                if (quote.billingAddress()) {
                    // restore 'Same As Shipping' checkbox state
                    this.isAddressSameAsShipping(
                        quote.billingAddress() != null &&
                            quote.billingAddress().getCacheKey() == quote.shippingAddress().getCacheKey() &&
                            !quote.isVirtual()
                    );
                    this.isAddressDetailsVisible(true);
                }
            },

            /**
             * Restore billing address
             */
            restoreBillingAddress: function () {
                if (lastSelectedBillingAddress != null) {
                    selectBillingAddress(lastSelectedBillingAddress);
                }
            },

            /**
             * @param {Object} address
             */
            onAddressChange: function (address) {
                this.isAddressFormVisible(address == newAddressOption);
                this.updateAddress();
            },

            /**
             * @param {int} countryId
             * @return {*}
             */
            getCountryName: function (countryId) {
                return countryData()[countryId] != undefined ? countryData()[countryId].name : '';
            },

            /**
             * Trigger action to update shipping and billing addresses
             */
            updateAddresses: function () {
                if (window.checkoutConfig.reloadOnBillingAddress ||
                    !window.checkoutConfig.displayBillingOnPaymentMethod
                ) {
                    setBillingAddressAction(globalMessageList);
                }
            },

            /**
             * Get code
             * @param {Object} parent
             * @returns {String}
             */
            getCode: function (parent) {
                return _.isFunction(parent.getCode) ? parent.getCode() : 'shared';
            }
        });
    }
);
