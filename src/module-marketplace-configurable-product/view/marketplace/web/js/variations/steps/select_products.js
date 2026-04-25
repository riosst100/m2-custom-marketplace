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
            showMassActionColumn: true,
            currentProductId: 0,
            dataScopeAssociatedProduct: 'data.associated_product_ids',
            typeGrid: '',
            product: {},
            rowIndexForChange: undefined,
            changeProductData: [],
            modules: {
                productsProvider: '${ $.providerName }',
                productsColumns: '${ $.productsColumns }',
                productsMassAction: '${ $.productsMassAction }',
                modalWithGrid: '${ $.modalComponent }',
                variationsComponent: '${ $.variationsComponent }',
                productsFilters: '${ $.productsFilters }'
            },
            exports: {
                externalProviderParams: '${ $.externalProvider }:params'
            },
            links: {
                changeProductData: '${ $.provider }:${ $.changeProductProvider }'
            },
            listens: {
                '${ $.externalProvider }:params': '_setFilters _setVisibilityMassActionColumn',
                '${ $.productsProvider }:data': '_handleManualGridOpening',
                '${ $.productsMassAction }:selected': '_handleManualGridSelect'
            },
            notificationMessage: {
                text: null,
                error: null
            },
            selectedProducts: [],
            attributes: [],
        },
        render: function(wizard) {
            console.log('render wizard')
            console.log(wizard)
            this.wizard = wizard;

            this.doRender(true, 'assignProduct');

            // var setsGrid = this.productsProvider();
            // if (setsGrid) {
            //     var set = wizard.data.sets;
            //     console.log('set',set)
            //     setsGrid.set('filters_modifier[mtg_card_set][condition_type]', 'eq');
            //     setsGrid.set('filters_modifier[mtg_card_set][value]', set.mtg_card_set);
            // }
            var listingGridProvider = this.productsProvider();
            
            var sets = wizard.data.sets;
            sets = sets.filter((v, i, a) => a.indexOf(v) === i);
            if (sets) {
                if (listingGridProvider) {
                    listingGridProvider.set('params.filters_modifier[card_set][condition_type]', 'in');
                    listingGridProvider.set('params.filters_modifier[card_set][value]', sets);
                }
            }

            listingGridProvider.reload();
        },

        /**
         * Initialize observables.
         *
         * @returns {Object} Chainable.
         */
        initObservable: function () {
            this._super().observe(
                'changeProductData',
                'attributes'
            );

            this.showGridAssignProduct();

            return this;
        },

        /**
         * Get ids of used products.
         *
         * @returns {Array}
         */
        getUsedProductIds: function () {
            var source = registry.get('product_form.product_form_data_source');
            var usedProductsIds = source.get(this.dataScopeAssociatedProduct);
            return usedProductsIds.slice();
        },

        /**
         * Request for render content.
         *
         * @returns {Object}
         */
        doRender: function (showMassActionColumn, typeGrid) {
            this.typeGrid = typeGrid;
            this.showMassActionColumn = showMassActionColumn;

            if (this.gridInitialized) {
                this.paramsUpdated = false;
                this.productsFilters().clear();
                this._setFilters(this.externalProviderParams);
                this._setVisibilityMassActionColumn();
            }

            // return this.render();
            return this;
        },

        /**
         * Show grid with assigned product.
         *
         * @returns {Object}
         */
        showGridAssignProduct: function () {
            this.product = {};
            this.rowIndexForChange = undefined;

            return this.doRender(true, 'assignProduct');
        },

        /**
         * Show grid with changed product.
         *
         * @param {String} rowIndex
         * @param {String} product
         */
        showGridChangeProduct: function (rowIndex, product) {
            this.rowIndexForChange = rowIndex;
            this.product = product;
            this.doRender(false, 'changeProduct');
        },

        /**
         * Select product.
         *
         * @param {String} rowIndex
         */
        selectProduct: function (rowIndex) {
            this.changeProductData({
                rowIndex: this.rowIndexForChange,
                product: this.productsProvider().data.items[rowIndex]
            });
            this.modalWithGrid().closeModal();
        },

        back: function () {
        },

        /**
         * Set visibility state for mass action column
         *
         * @private
         */
        _setVisibilityMassActionColumn: function () {
            this.productsMassAction(function (massActionComponent) {
                this.productsColumns().elems().each(function (rowElement) {
                    rowElement.disableAction = this.showMassActionColumn;
                }, this);
                massActionComponent.visible = this.showMassActionColumn;
            }.bind(this));
        },

        /**
         * Set filters.
         *
         * @param {Object} params
         * @private
         */
        _setFilters: function (params) {
            var filterModifier = {},
                attrCodes,
                usedProductIds,
                attributes;

            params = _.omit(params);

            if (!this.paramsUpdated) {
                this.gridInitialized = true;
                this.paramsUpdated = true;

                attrCodes = this._getAttributesCodes();
                usedProductIds = this.getUsedProductIds();

                if (this.currentProductId) {
                    usedProductIds.push(this.currentProductId);
                }

                filterModifier['entity_id'] = {
                    'condition_type': 'nin', value: usedProductIds
                };
                attrCodes.each(function (code) {
                    filterModifier[code] = {
                        'condition_type': 'notnull'
                    };
                });

                if (this.typeGrid === 'changeProduct') {
                    attributes = JSON.parse(this.product.attributes);

                    filterModifier = _.extend(filterModifier, _.mapObject(attributes, function (value) {
                        return {
                            'condition_type': 'eq',
                            'value': value
                        };
                    }));

                    params.filters = attributes;
                } else {
                    params.filters = {};
                }

                params['attributes_codes'] = attrCodes;


                if (this.wizard) {
                    // var sets = this.wizard.data.sets;
    
                    // attributes = {
                    //     'card_set': sets.card_set
                    // };
    
                    // filterModifier = _.extend(filterModifier, _.mapObject(attributes, function (value) {
                    //     return {
                    //         'condition_type': 'eq',
                    //         'value': value
                    //     };
                    // }));
    
                    // params.filters = attributes;
                }

                console.log('params')
                console.log(params)

                var productSource = registry.get('product_form.product_form_data_source');
                params.product = productSource.data.product;
                

                this.set('externalProviderParams', params);
                this.set('externalFiltersModifier', filterModifier);
            }
        },

        /**
         * Get attribute codes.
         *
         * @returns {Array}
         * @private
         */
        _getAttributesCodes: function () {
            var source = registry.get('product_form.product_form_data_source');
            var attrCodes = source.get('data.attribute_codes');

            return attrCodes ? attrCodes : [];
        },

        /**
         * Get product variations.
         *
         * @returns {Array}
         * @private
         */
        _getProductVariations: function () {
            var source = registry.get('product_form.product_form_data_source');
            var matrix = source.get('data.configurable-matrix');

            return matrix ? matrix : [];
        },

        /**
         * Handle manual grid after opening
         * @private
         */
        _handleManualGridOpening: function (data) {
            if (data.items.length && this.typeGrid === 'assignProduct') {
                this.productsColumns().elems().each(function (rowElement) {
                    rowElement.disableAction = true;
                });

                this._disableRows(data.items);
            }
        },

        /**
         * Handle manual selection.
         *
         * @param {Array} selected
         * @private
         */
        _handleManualGridSelect: function (selected) {
            var selectedRows,
                selectedVariationKeys;

            if (this.typeGrid === 'assignProduct') {
                selectedRows = _.filter(this.productsProvider().data.items, function (row) {
                    return selected.indexOf(row['entity_id']) !== -1;
                });
                selectedVariationKeys = _.values(this._getVariationKeyMap(selectedRows));
                this._disableRows(this.productsProvider().data.items, selectedVariationKeys, selected);
            }
        },

        /**
         * Disable rows in grid for products with the same variation key
         *
         * @param {Array} items
         * @param {Array} selectedVariationKeys
         * @param {Array} selected
         * @private
         */
        _disableRows: function (items, selectedVariationKeys, selected) {
            selectedVariationKeys = selectedVariationKeys === undefined ? [] : selectedVariationKeys;
            selected = selected === undefined ? [] : selected;
            this.productsMassAction(function (massaction) {
                var configurableVariationKeys = _.union(
                    selectedVariationKeys,
                    _.pluck(this._getProductVariations(), 'variationKey')
                    ),
                    variationKeyMap = this._getVariationKeyMap(items),
                    rowsForDisable = _.keys(_.pick(
                        variationKeyMap,
                        function (variationKey) {
                            return configurableVariationKeys.indexOf(variationKey) !== -1;
                        }
                    ));

                massaction.disabled(_.difference(rowsForDisable, selected));
            }.bind(this));
        },

        /**
         * Get variation key map used in manual grid.
         *
         * @param {Array} items
         * @returns {Array} [{entity_id: variation-key}, ...]
         * @private
         */
        _getVariationKeyMap: function (items) {
            var variationKeyMap = {};

            _.each(items, function (row) {
                variationKeyMap[row['entity_id']] = _.values(
                    _.pick(row, this._getAttributesCodes())
                ).sort().join('-');

            }, this);

            return variationKeyMap;
        },

        force: function (wizard) {
            var modalComponent = this.modalWithGrid();

            var source = registry.get('product_form.product_form_data_source');
            var action = {"targetName": "ns=configurable_associated_product_listing, index=configurable_associated_product_listing","actionName": "save"};
            modalComponent.triggerAction(action);

            var attributes = wizard.data.attributes();

            _.each(attributes, function (attribute) {
                attribute.chosen = [];

                if (!attribute.chosenOptions.length) {
                    throw new Error(errorMessage);
                }
                _.each(attribute.chosenOptions, function (id) {
                    attribute.chosen.push(attribute.options.findWhere({
                        value: id
                    }));
                });
            });

            this.variationsComponent().render([], attributes);

            this.modalWithGrid().closeModal();
        },
    });
});
