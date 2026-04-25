define([
    'Magento_Ui/js/grid/columns/column',
    'jquery'
], function(Column, $) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'ui/grid/cells/html',
            fieldClass: {
                'data-grid-html-cell': true
            }
        },
        getLabel: function(row) {
            return row[this.index + '_html']
        },
        getActionLink: function(row) {
            return row[this.index + '_action_link']
        },
        preview: function(row) {
            return window.location.href = this.getActionLink(row);
        },
        getFieldHandler: function(row) {
            return this.preview.bind(this, row);
        }
    });
});