/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'jquery',
    'underscore',
    'uiRegistry',
    'platformDatabaseUtils',
    'mage/translate'
], function (Component, $, _, registry, platformDatabaseUtils) {
    'use strict';

    /**
     * @param {Function} provider
     */
    var initNewAttributeListener = function (provider) {
        $('[data-role=product-cardgame-sets-matrix]').on('add', function () {
            provider().reload();
        });
    };

    return Component.extend({
        attributesLabels: {},
        stepInitialized: false,
        defaults: {
            modules: {
                multiselect: '${ $.multiselectName }',
                attributeProvider: '${ $.providerName }',
                setsAndSinglesComponent: '${ $.setsAndSinglesComponent }',
                modalComponent: '${ $.modalComponent }',
            },
            listens: {
                '${ $.multiselectName }:selected': 'doSelectedAttributesLabels',
                '${ $.multiselectName }:rows': 'doSelectSavedAttributes'
            },
            notificationMessage: {
                text: null,
                error: null
            },
            selectedSets: [],
            sets: []
        },

        /** @inheritdoc */
        initialize: function () {
            this._super();
            this.selected = [];

            

            initNewAttributeListener(this.attributeProvider);
        },

        /** @inheritdoc */
        
        initObservable: function () {
            this._super().observe(['selectedSets','sets']);

            return this;
        },

        /**
         * @param {Object} wizard
         */
        render: function (wizard) {
            this.wizard = wizard;
            this.setNotificationMessage();

            console.log('RENDER | SETS STEP')

            platformDatabaseUtils.hideSteps(wizard);
            platformDatabaseUtils.modifyFilterElements(wizard);
            platformDatabaseUtils.filterDataProvider(this, wizard);
        },

        /**
         * Set notification message.
         */
        setNotificationMessage: function () {
            /*eslint-disable max-len*/
            var msg = $.mage.__('Please select sets.');

            /*eslint-enable max-len*/

            // if (this.mode === 'edit') {
                this.wizard.setNotificationMessage(msg);
            // }
        },

        /**
         * Do select saved attributes.
         */
        doSelectSavedAttributes: function () {
            if (this.stepInitialized === false) {
                this.stepInitialized = true;
                //cache attributes labels, which can be present on the 2nd page
                _.each(this.initData.attributes, function (attribute) {
                    this.attributesLabels[attribute.id] = attribute.label;
                }.bind(this));
                this.multiselect().selected(_.pluck(this.initData.attributes, 'id'));
            }
        },

        /**
         * @param {*} selected
         */
        doSelectedAttributesLabels: function (selected) {
            var labels = [];

            var multiselect = this.multiselect()
            
            if (selected.length) {
                selected.forEach(function(data, index) {
                    if (index != selected.length-1) {
                        multiselect.toggleSelect(data);
                        selected.splice(index, 1);
                    }
                });
            }
            
            this.selected = selected;
            
            _.each(selected, function (entityId) {
                var set;

                set = _.findWhere(this.multiselect().rows(), {
                    'entity_id': entityId
                });

                if (set) {
                    this.sets(set);
                    this.attributesLabels[set['entity_id']] = set['card_set'] ?? 'Untitled';
                }
                
                labels.push(this.attributesLabels[entityId]);
            }.bind(this));
            this.selectedSets(labels.join(', '));
        },

        /**
         * @param {Object} wizard
         */
        force: function (wizard) {
            wizard.data.sets = this.sets();

            if (!this.selectedSets()) {
                throw new Error($.mage.__('Please select sets.'));
            }
            
            var setsData = this.sets();

            var _this = this;

            var setsMatrixField = registry.get('product_form.product_form.cardgame_sets.sets-matrix');
            
            var productSource = registry.get('product_form.product_form_data_source');
            var is_having_singles = productSource ? productSource.data.product['is_having_singles'] : '';

            var productSource = registry.get('product_form.product_form_data_source');
            var is_sealedproducts = productSource ? productSource.data.product['is_sealedproducts'] : '';
            
            wizard.data.sets = this.sets();
            this.setNotificationMessage();
        },
        back: function () {}
    });
});
