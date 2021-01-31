define(
    [
        'Magento_Checkout/js/view/payment/default',
        'mage/url',
        'ko',
        'jquery'

    ],
    function (Component, url, ko, $) {
        'use strict';
        return Component.extend({
            redirectAfterPlaceOrder: false,
            defaults: {
                template: 'Payxpert_Connect2Pay/payment/payxpert',
                alipay: Boolean(parseInt(window.checkoutConfig.payment.payxpert.alipay)),
                aliPayImageUrl: window.checkoutConfig.payment.payxpert.aliPayImageUrl,

                creditCard: true,
                creditCardPayImageUrl: window.checkoutConfig.payment.payxpert.creditCardPayImageUrl,

                weChat: Boolean(parseInt(window.checkoutConfig.payment.payxpert.weChat)),
                weChatImageUrl: window.checkoutConfig.payment.payxpert.weChatImageUrl,

                ideal: Boolean(parseInt(window.checkoutConfig.payment.payxpert.ideal)),
                idealImageUrl: window.checkoutConfig.payment.payxpert.idealImageUrl,

                giropay: Boolean(parseInt(window.checkoutConfig.payment.payxpert.giropay)),
                giroPayImageUrl: window.checkoutConfig.payment.payxpert.giroPayImageUrl,

                sofort: Boolean(parseInt(window.checkoutConfig.payment.payxpert.sofort)),
                sofortImageUrl: window.checkoutConfig.payment.payxpert.sofortImageUrl,

                przelewy24: Boolean(parseInt(window.checkoutConfig.payment.payxpert.przelewy24)),
                przelewy24ImageUrl: window.checkoutConfig.payment.payxpert.przelewy24ImageUrl,

                isCheckedPaymentMethod: ko.observable("CreditCard"),
            },
            getCode: function () {
                return 'payxpert';
            },
            initialize: function () {
                this._super(); // This is required ＼（〇_ｏ）／
                this.selectPaymentMethod();

            },
            afterPlaceOrder: function () {
                window.location.replace(url.build('payxpert/redirect/payxpert/?paymentMethod=' + this.isCheckedPaymentMethod()));
            }
        });
    }
);
