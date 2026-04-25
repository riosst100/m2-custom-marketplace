define([
    'jquery',
    'uiRegistry',
    'mage/url'
], function ($, uiRegistry, urlBuilder) {
    'use strict';

    return function (Form) {
        return Form.extend({
            /**
             * Updates data from server.
             */
            reload: function () {
                var dataParams = this.source.data.product;
                var dataParams = $.extend({}, this.params, dataParams);
                makeRequest(dataParams, this.data, this.reloadUrl).then(function (data) {
                    app(data, true);
                });
            }
        });
    }
});