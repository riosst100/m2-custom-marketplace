/**
 * @api
 */
define([
    'uiElement',
    'mageUtils'
], function (Element, utils) {
    'use strict';

    return Element.extend({
        defaults: {
            visible: true,
            uid: utils.uniqueid(),
            disabled: false,
            links: {
                value: '${ $.provider }:${ $.dataScope }'
            }
        },

        /**
         * Has service
         *
         * @returns {Boolean} false.
         */
        hasService: function () {
            return false;
        },

        /**
         * Has addons
         *
         * @returns {Boolean} false.
         */
        hasAddons: function () {
            return false;
        },

        /**
         * Calls 'initObservable' of parent
         *
         * @returns {Object} Chainable.
         */
        initObservable: function () {
            this._super()
                .observe('visible value');

            return this;
        },
        initialize: function () {
            this._super();

            return this;
        }
    });
});