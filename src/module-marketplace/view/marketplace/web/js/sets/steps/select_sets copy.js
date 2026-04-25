/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'jquery',
    'ko',
    'underscore',
    'Magento_Ui/js/grid/paging/paging',
    'Magento_Ui/js/grid/filters/filters',
    'Magento_Ui/js/grid/columns/column',
    'mage/url',
    'mage/translate'
], function (Component, $, ko, _, paging, filters, column, urlBuilder) {
    'use strict';

    return Component.extend({
        defaults: {
            modules: {
                variationsComponent: '${ $.variationsComponent }',
                modalComponent: '${ $.modalComponent }',
                matrixGridComponent: '${ $.matrixGridComponent }'
            },
            notificationMessage: {
                text: null,
                error: null
            },
            selectedSets: [],
            gridNew: [],
            variationsNew: [],
            pagingNew: paging({
                name: 'configurableWizard.pagingNew',
                sizesConfig: {
                    component: 'Magento_ConfigurableProduct/js/variations/paging/sizes',
                    name: 'configurableWizard.pagingNew_sizes'
                }
            }),
            filtersNew: filters({
                name: 'configurableWizard.filtersNew'
            }),
            attributes: [],
            attributesName: [$.mage.__('Images'), $.mage.__('SKU'), $.mage.__('Quantity'), $.mage.__('Price')],
            sections: [],
            gridTemplate: 'TCGCollective_MarketPlace/sets/steps/sets-grid',
            quantityFieldName: 'quantity'
        },

        /** @inheritdoc */
        initObservable: function () {
            this._super().observe(['selectedSets']);
            var pagingObservables = {
                currentNew: ko.getObservable(this.pagingNew, 'current'),
                pageSizeNew: ko.getObservable(this.pagingNew, 'pageSize')
            };

            var filtersObservables = {
                activeNew: ko.getObservable(this.filtersNew, 'active')
            };

            this._super().observe('gridNew attributes sections');
            this.gridNew.columns = ko.observableArray();

            _.each(pagingObservables, function (observable) {
                observable.subscribe(function () {
                    this.generateGrid();
                }, this);
            }, this);

            _.each(filtersObservables, function (observable) {
                observable.subscribe(function () {
                    this.generateGrid();
                }, this);
            }, this);

            return this;
        },
        variations: [],

        /**
         * @param {*} variations
         * @param {Function} getSectionValue
         */
        calculate: function (variations, getSectionValue) {
            var productSku = this.variationsComponent().getProductValue('sku'),
                productPrice = this.variationsComponent().getProductPrice(),
                productWeight = this.variationsComponent().getProductValue('weight'),
                productName = this.variationsComponent().getProductValue('name'),
                variationsKeys = [],
                gridNew = [],
                matrixGridData = this.matrixGridComponent() ?
                    _.indexBy(this.matrixGridComponent().getUnionInsertData(), 'variationKey') : {};

            this.variations = [];
            _.each(variations, function (options) {
                    var result = [];

for(var i in options)
    if (i == "images") {
        result.push({'images': {'length': 1}, 'preview': options[i]});
    } else {
        result.push([options [i]]);
    }

                    
                    gridNew.push(result);
            }, this);
            
            this.variationsNew = gridNew;
        },

        /**
         * Generate grid.
         */
        generateGrid: function () {
            var pageNew = this.pagingNew.pageSize * this.pagingNew.current;
            
            this.pagingNew.totalRecords = this.variationsNew.length;
            
            console.log(this.filtersNew.filters)
            var newColl = column();
            newColl.filter = 'text';
            newColl.index = "frontend_label";
            newColl.label = "Attribute Label";

            newColl.dataType = "text";
            newColl.componentType = "column.text";
            newColl.component = "Magento_Ui/js/grid/columns/column";
            newColl.label = "Attribute Label";
            
            this.filtersNew.addFilter(newColl);
            this.gridNew(this.variationsNew.slice(pageNew - this.pagingNew.pageSize, pageNew));
            console.log(this.gridNew.columns())
        },

        /**
         * @return {String|*}
         */
        getGridTemplate: function () {
            return this.gridTemplate;
        },

        /**
         * @return {*|String}
         */
        getGridId: function () {
            return _.uniqueId('grid_');
        },

        /**
         * @param {Object} wizard
         */
        render: function (wizard) {
            this.wizard = wizard;

            var grid = this;

            urlBuilder.setBaseUrl(BASE_URL);
            var url = urlBuilder.build("marketplace/productattributeslink/product/setsData");
            $.ajax({
                url: url,
                data: {
                    form_key: window.FORM_KEY
                },
                showLoader: true,
                dataType: 'json',
                success: function(resp) {
                    if (resp.data) {
                        wizard.data.variations = resp.data.grid_data;
                        
                        grid.gridNew([]);
                        grid.gridNew.columns(resp.data.grid_column);
                        grid.calculate(wizard.data.variations, wizard.data.sectionHelper);
                        grid.generateGrid();
                    }
                },
                complete: function() {
                    $('body').trigger('processStop');
                }
            });
        },

        /**
         * Set notification message.
         */
        setNotificationMessage: function () {
            /*eslint-disable max-len*/
            var msg = $.mage.__('Please select the singles from the list below. You can use filters to refine the selection. If the desired singles are not found, you can also create new ones.');

            /*eslint-enable max-len*/

            if (this.mode === 'edit') {
                this.wizard.setNotificationMessage(msg);
            }
        },

        /**
         * @param {Object} wizard
         */
        force: function (wizard) {
            wizard.data.selectedSets = null;

            // if (!wizard.data.selectedSets) {
            //     throw new Error($.mage.__('Please select the sets.'));
            // }
            // this.setNotificationMessage();
        },

        /**
         * Back.
         */
        back: function () {
        }
    });
});
