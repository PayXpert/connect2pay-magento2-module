define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (Component,
              rendererList) {
        'use strict';
        rendererList.push(
            {
                type: 'payxpert',
                component: 'Payxpert_Connect2Pay/js/view/payment/method-renderer/payxpert-method'
            }
        );
        return Component.extend({});
    }
);