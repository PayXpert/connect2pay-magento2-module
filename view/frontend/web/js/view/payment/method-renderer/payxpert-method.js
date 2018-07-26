define(
    [
        'Magento_Checkout/js/view/payment/default',
        'mage/url'

    ],
    function (Component, url) {
        'use strict';
        return Component.extend({
            redirectAfterPlaceOrder: false,
            defaults: {
                template: 'Payxpert_Connect2Pay/payment/payxpert'
            },
            getCode: function () {
                return 'payxpert';
            },

            afterPlaceOrder: function () {
                if (window.checkoutConfig.payment.payxpert.iframe == '0') {
                  window.location.replace(url.build('payxpert/redirect/payxpert/'));
                } else {
                  window.location.replace(url.build('payxpert/iframe/payxpert/'));
                }
            }
        });
    }
);