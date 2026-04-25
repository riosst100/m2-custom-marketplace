/**
 * @api
 */
define([
    'uiElement',
    'uiRegistry',
    'uiLayout',
    'mageUtils',
    'underscore'
], function (Element, registry, layout, utils, _) {
    'use strict';

    return Element.extend({
        defaults: {
            buttonClasses: {},
            additionalClasses: {},
            displayArea: 'outsideGroup',
            displayAsLink: false,
            elementTmpl: 'ui/form/element/button',
            template: 'ui/form/components/button/simple',
            visible: true,
            disabled: false,
            title: '',
            buttonTextId: '',
            ariLabelledby: ''
        },

        /**
         * Initializes component.
         *
         * @returns {Object} Chainable.
         */
        initialize: function () {
            return this._super()
                ._setClasses()
                ._setButtonClasses();
        },

        /** @inheritdoc */
        initObservable: function () {
            return this._super()
                .observe([
                    'visible',
                    'disabled',
                    'title',
                    'childError'
                ]);
        },

        /**
         * Performs configured actions
         */
        action: function () {
            var sourceField = registry.get('product_form.product_form.product-details.container_source.source');
            if (sourceField) {
                sourceField.value('');
            }

            var noDatabaseFieldset = registry.get("product_form.product_form.no_database");
            if (noDatabaseFieldset) {
                noDatabaseFieldset.visible(false);
            }

            var fieldset = registry.get('product_form.product_form.product-details');
            if (fieldset) {
                fieldset.visible(true);
            }
        },

        /**
         * Extends 'additionalClasses' object.
         *
         * @returns {Object} Chainable.
         */
        _setClasses: function () {
            if (typeof this.additionalClasses === 'string') {
                if (this.additionalClasses === '') {
                    this.additionalClasses = {};

                    return this;
                }

                this.additionalClasses = this.additionalClasses
                    .trim()
                    .split(' ')
                    .reduce(function (classes, name) {
                        classes[name] = true;

                        return classes;
                    }, {}
                );
            }

            return this;
        },

        /**
         * Extends 'buttonClasses' object.
         *
         * @returns {Object} Chainable.
         */
        _setButtonClasses: function () {
            var additional = this.buttonClasses;

            if (_.isString(additional)) {
                this.buttonClasses = {};

                if (additional.trim().length) {
                    additional = additional.trim().split(' ');

                    additional.forEach(function (name) {
                        if (name.length) {
                            this.buttonClasses[name] = true;
                        }
                    }, this);
                }
            }

            _.extend(this.buttonClasses, {
                'action-basic': !this.displayAsLink,
                'action-additional': this.displayAsLink
            });

            return this;
        }
    });
});
