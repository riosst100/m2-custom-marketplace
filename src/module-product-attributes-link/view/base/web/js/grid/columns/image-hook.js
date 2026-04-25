define([
    'jquery',
    'Magento_Ui/js/grid/columns/column',
    'uiLayout',
    'underscore'
], function ($, Column, layout, _) {
    'use strict';

    return function (Column) {
        return Column.extend({
            reloadGrid: function () {
                var provider = this.provider(),
                    dataStorage = provider.storage();
    
                dataStorage.clearRequests();
                provider.reload();
            }
        });
    }
});