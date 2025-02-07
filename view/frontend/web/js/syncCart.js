define([
    'Magento_Customer/js/customer-data'
], function (customerData) {
    'use strict';
    return function () {
        const url = window.location.href;
        if (url.includes('?cart_id')) {
            customerData.reload(['cart'], true);
        }
    };
});
