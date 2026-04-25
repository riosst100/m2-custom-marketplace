/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'Magento_Ui/js/form/components/insert-listing',
    'uiRegistry',
    'ko',
    'mageUtils',
    'jquery'
], function (_, insertListing, registry, ko, utils, $) {
    'use strict';

    return insertListing.extend({
        defaults: {
            gridInitialized: false,
            paramsUpdated: false,
            currentProductId: 0,
            typeGrid: '',
            product: {},
            changeProductData: [],
            modules: {
                multiselect: '${ $.multiselectName }',
                productsProvider: '${ $.providerName }',
                productsColumns: '${ $.productsColumns }',
                modalWithGrid: '${ $.modalComponent }',
            },
            exports: {
                externalProviderParams: '${ $.externalProvider }:params'
            },
            links: {
                changeProductData: '${ $.provider }:${ $.changeProductProvider }'
            },
            listens: {
                '${ $.multiselectName }:selected': 'doSelectedAttributesLabels',
                '${ $.multiselectName }:rows': 'doSelectSavedAttributes'
            },
            notificationMessage: {
                text: null,
                error: null
            },
            selectedProducts: [],
            attributes: [],
            selectedSets: [],
            sets: []
        },
        render: function(wizard) {
            this.wizard = wizard;

            var productSource = registry.get('product_form.product_form_data_source');
            var product = productSource ? productSource.data.product : '';

            const keysToExclude = ['copy_to_stores','configurable_attributes_data','stock_data','website_ids'];
            const filteredProductData = Object.fromEntries(
              Object.entries(product).filter(([key]) => !keysToExclude.includes(key))
            );

            var listingGridProvider = this.productsProvider();

            listingGridProvider.set('params.product', filteredProductData);
            listingGridProvider.reload();

            // this.multiselect().selected([]);

            // if (selected.length) {
            //     selected.forEach(function(data, index) {
            //         if (index != selected.length-1) {
                        // this.multiselect().toggleSelect([]);
            //             selected.splice(index, 1);
            //         }
            //     });
            // }
        },

        /**
         * Initialize observables.
         *
         * @returns {Object} Chainable.
         */
        initObservable: function () {
            this._super().observe(['selectedSets','sets']);
            
            return this;
        },
        
        back: function () {
        },
        
        doSelectedAttributesLabels: function (selected) {
            var labels = [];
            var setsArr = [];

            this.selected = selected;

            if (selected) {
                _.each(selected, function (productId) {
                    var productData = _.findWhere(this.multiselect().rows(), {
                        entity_id: productId
                    });
    
                    if (productData) {
                        setsArr.push(productData.card_set);
                    }
                    labels.push(productData.card_set);
                }.bind(this));
            }
            
            this.sets = setsArr;
            this.selectedSets(labels.join(', '));
        },
        force: function (wizard) {
            wizard.data.sets = this.sets;

            if (!this.sets.length) {
                throw new Error($.mage.__('Please select a set.'));
            }
        },
    });
});
