/**
 * Landofcoder
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * https://landofcoder.com/terms
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category   Landofcoder
 * @package    Lof_MarketPlace
 * @copyright  Copyright (c) 2021 Landofcoder (https://www.landofcoder.com/)
 * @license    https://landofcoder.com/terms
 */

define([
    'Magento_Ui/js/form/element/abstract',
    'underscore',
    'uiRegistry'
], function (Abstract, _, registry) {
    'use strict';

    return Abstract.extend({
        defaults: {
            allowImport: true,
            autoImportIfEmpty: false,
            values: {},
            mask: '',
            queryTemplate: 'ns = ${ $.ns }, index = '
        },

        /** @inheritdoc */
        initialize: function () {
            this._super();

            if (this.allowImport) {
                this.setHandlers();
            }
        },

        /**
         * Split mask placeholder and attach events to placeholder fields.
         */
        setHandlers: function () {
            var str = this.mask || '',
                placeholders;

            placeholders = str.match(/{{(.*?)}}/g); // Get placeholders

            _.each(placeholders, function (placeholder) {
                placeholder = placeholder.replace(/[{{}}]/g, ''); // Remove curly braces

                registry.get(this.queryTemplate + placeholder, function (component) {
                    this.values[placeholder] = component.getPreview();
                    component.on('value', this.updateValue.bind(this, placeholder, component));
                    component.valueUpdate = 'keyup';
                }.bind(this));
            }, this);
        },
        /**
         * Update field with mask value, if it's allowed.
         *
         * @param {Object} placeholder
         * @param {Object} component
         */
        updateValue: function (placeholder, component) {
    var string = this.mask || '',
        nonEmptyValueFlag = false;

    if (placeholder) {
        this.values[placeholder] = component.getPreview() || '';
    }

    if (!this.allowImport) {
        return;
    }

    _.each(this.values, function (propertyValue, propertyName) {
        var formatted = propertyValue
            .replace(/[^a-zA-Z0-9\s]/g, '') // Hapus karakter khusus
            .replace(/\s+/g, '-')           // Ganti spasi dengan -
            .toUpperCase();                 // Kapital semua

        string = string.replace('{{' + propertyName + '}}', formatted);
        nonEmptyValueFlag = nonEmptyValueFlag || !!propertyValue;
    });

    if (nonEmptyValueFlag) {
        string = string.replace(/(<([^>]+)>)/ig, ''); // Hapus tag HTML
        string = string.substring(0, 60);             // 💥 Batasi setelah final
        
        this.value(string);
    } else {
        this.value('');
    }
},
        /**
         * Disallow import when initial value isn't empty string
         *
         * @returns {*}
         */
        setInitialValue: function () {
            this._super();

            if (this.initialValue !== '') {
                this.allowImport = false;
            }

            return this;
        },

        /**
         *  Callback when value is changed by user,
         *  and disallow/allow import value
         */
        userChanges: function () {

            /**
             *  As userChanges is called before updateValue,
             *  we forced to get value from component by reference
             */
            var actualValue = arguments[1].currentTarget.value;

            this._super();

            if (actualValue === '') {
                this.allowImport = true;

                if (this.autoImportIfEmpty) {
                    this.updateValue(null, null);
                }
            } else {
                this.allowImport = false;
            }
        }
    });
});
