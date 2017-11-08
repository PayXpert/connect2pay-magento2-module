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
                template: 'PayXpert_Connect2Pay/payment/payxpert'
            },
            getCode: function () {
                return 'payxpert';
            },

            afterPlaceOrder: function () {
                window.location.replace(url.build('payxpert/redirect/payxpert/'));
            }
        });
    }
);