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
            toditoCash: function () {
                return window.checkoutConfig.payment.payxpert.toditoCash == '1' ? true : false;
            },
            bankTransfer: function () {
                return window.checkoutConfig.payment.payxpert.bankTransfer == '1' ? true : false;
            },
            directDebit: function () {
                return window.checkoutConfig.payment.payxpert.directDebit == '1' ? true : false;
            },
            weChat: function () {
                return window.checkoutConfig.payment.payxpert.weChat == '1' ? true : false;
            },
            line: function () {
                return window.checkoutConfig.payment.payxpert.line == '1' ? true : false;
            },
            aliPay: function () {
                return window.checkoutConfig.payment.payxpert.aliPay == '1' ? true : false;

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
