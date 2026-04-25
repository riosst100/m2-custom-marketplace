/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'jquery',
    'underscore',
    'uiRegistry',
    'mage/url',
    'platformDatabaseUtils',
    'mage/translate'
], function (Component, $, _, registry, urlBuilder, platformDatabaseUtils) {
    
    'use strict';

    /**
     * @param {Function} provider
     */
    var initNewAttributeListener = function (provider) {
        $('[data-role=product-cardgame-release-matrix]').on('add', function () {
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
            selectedRelease: [],
            release: []
        },

        /** @inheritdoc */
        initialize: function () {
            this._super();
            this.selected = [];

            

            initNewAttributeListener(this.attributeProvider);
        },

        /** @inheritdoc */
        
        initObservable: function () {
            this._super().observe(['selectedRelease','release']);

            return this;
        },

        /**
         * @param {Object} wizard
         */
        render: function (wizard) {
            this.wizard = wizard;
            this.setNotificationMessage();

            var categoryMetaData = platformDatabaseUtils.getCategoryMetaData();
            if (categoryMetaData) {
                this.categoryMetaData = categoryMetaData;
                this.isSealedproducts = categoryMetaData.isSealedproducts;
                this.isSingles = categoryMetaData.isSingles;
                this.mainSetsCode = categoryMetaData.mainSetsCode;
                this.mainEntityCode = categoryMetaData.mainEntityCode;
            }

            platformDatabaseUtils.hideSteps(wizard);
            platformDatabaseUtils.modifyFilterElements(wizard);
            platformDatabaseUtils.filterDataProvider(this, wizard);
        },

        /**
         * Set notification message.
         */
        setNotificationMessage: function () {
            /*eslint-disable max-len*/
            // var msg = $.mage.__('Please select release or create new one if not exist.');

            /*eslint-enable max-len*/

            // if (this.mode === 'edit') {
            //     this.wizard.setNotificationMessage(msg);
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
            // if (!is_having_singles) {
                // this.release = [];
            // }
            _.each(selected, function (entityId) {
                var set;

                set = _.findWhere(this.multiselect().rows(), {
                    'entity_id': entityId
                });

                if (set) {
                    // if (!is_having_singles) {
                            // this.release.push(set);
                        // } else {
                            this.release(set);
                        // }
                    this.attributesLabels[set['entity_id']] = set['card_release'];
                }
                
                labels.push(this.attributesLabels[entityId]);
            }.bind(this));
            this.selectedRelease(labels.join(', '));
        },

        /**
         * @param {Object} wizard
         */
        force: function (wizard) {
            var releaseMatrixField = registry.get('product_form.product_form.cardgame_release.release-matrix');
            var productSource = registry.get('product_form.product_form_data_source');
            var is_having_singles = productSource ? productSource.data.product['is_having_singles'] : '';

            if (!is_having_singles) {
            
            wizard.data.release = this.release();

                console.log(wizard.data.release)
            
            if (!this.selectedRelease()) {
                throw new Error($.mage.__('Please select release.'));
            }

            // Check is secret lair release
            var release = this.release();
            var releaseData = this.release();
            }
            
            var _this = this;
            
            
            
            _this.setNotificationMessage();
        },
        back: function () {
        }
    });
});
