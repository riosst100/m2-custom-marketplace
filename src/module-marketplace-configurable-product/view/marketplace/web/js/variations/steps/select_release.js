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
            attributesLabels: {},
            rowIndexForChange: undefined,
            changeProductData: [],
            modules: {
                productsProvider: '${ $.providerName }',
                productsColumns: '${ $.productsColumns }',
                productsMassAction: '${ $.productsMassAction }',
                modalWithGrid: '${ $.modalComponent }',
                variationsComponent: '${ $.variationsComponent }',
                productsFilters: '${ $.productsFilters }',
                multiselect: '${ $.multiselectName }',
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
                '${ $.productsMassAction }:selected': '_handleManualGridSelect',
                '${ $.multiselectName }:selected': 'doSelectedAttributesLabels',
            },
            notificationMessage: {
                text: null,
                error: null
            },
            selectedRelease: [],
            release: [],
            attributes: [],
        },
        render: function(wizard) {
            this.wizard = wizard;

            console.log('multiselect')
            console.log(this.multiselect())

            var productSource = registry.get('product_form.product_form_data_source');
            var is_having_singles = productSource ? productSource.data.product['is_having_singles'] : '';

            // if (!is_having_singles) {
            // var step2link = $('a[href="#select-cardgame-release-steps-wizard_step2"');
            var stepReleaseLink = $('a[href="#select-cardgame-release-steps-wizard_step.release');
            if (stepReleaseLink) {
                var stepRelease = stepReleaseLink.parent();
                if (stepRelease) {
                    console.log(stepRelease)
                    if (is_having_singles) {
                        stepRelease.hide();
                        // Hide progress bar after step done on singles
            //             stepRelease.addClass('no-after');
                    } else {
                        stepRelease.show();
                    }
                }
            }

            // Get release step
            var stepReleaseLink = $('a[href="#select-cardgame-release-steps-wizard_step.release');
            if (stepReleaseLink) {
                var stepRelease = stepReleaseLink.parent();
                if (stepRelease) {
                    if (is_having_singles) {
                        // Hide progress bar after step done on singles
                        stepRelease.addClass('no-after');
                    } else {
                        // Hide release step if no singles
                        // stepRelease.hide();
                    }
                }
            }

            var stepSealedProductsLink = $('a[href="#select-cardgame-release-steps-wizard_step.sealedproducts');
            if (stepSealedProductsLink) {
                var stepSealedProducts = stepSealedProductsLink.parent();
                if (stepSealedProducts) {
                    console.log(stepSealedProducts)
                    if (is_having_singles) {
                        stepSealedProducts.hide();
                    } else {
                        stepSealedProducts.show();
                    }
                }
            }

            // if (step3link) {
            //     var step3 = step3link.parent();
            //     if (step3) {
            //         if (!is_having_singles) {
            //             step3.hide();
            //         } else {
            //             step3.show();
            //         }
            //     }
            // }
            

            var currentIndex = wizard.index;
            var newIndex = currentIndex-1;

            console.log('currentIndex: '+currentIndex)
            console.log('newIndex: '+newIndex)
            // var switchIndex = newIndex+1;
            
            var nextStep = wizard.getStep(newIndex);
            if (nextStep) {
                if (
                    is_having_singles 
                    && nextStep.index == "select-cardgame-release-steps-wizard_step.release"
                ) {
                    var steps = wizard.steps;
                    if (steps) {
            //         //     var b = steps[newIndex];
            //         //     steps[newIndex] = steps[switchIndex];
            //         //     steps[switchIndex] = b;
            //             steps[newIndex] = steps[switchIndex];
            //             // steps[switchIndex] = b;
            //             // steps[switchIndex] = b;
                        // delete steps[newIndex];
                    }
                    wizard.steps = steps;
                }
            }

            document.body.style.counterReset = "i";
            
            var stepsWizard = registry.get('product_form.product_form.cardgame.releaseModal.wizard');
                if (stepsWizard) {
                    // stepsWizard.visible = false
                    // var steps = stepsWizard.steps;
                    // var stepsNames = stepsWizard.stepsNames;
                    // var _elems = stepsWizard._elems;
                    
                    // var elems = stepsWizard.elems();

                    // steps.splice(1, 1);
                    // stepsNames.splice(1, 1);
                    // elems.splice(1, 1);

                    // var step2 = elems[1];

                    // step2.disabled(true);

                    // stepsWizard.steps = steps;
                    // stepsWizard.stepsNames = stepsNames;
                    // stepsWizard._elems = elems;
                    // stepsWizard.elems = elems;
                }

            
            $(document).ready(function(){
                var test = setInterval(function() {
                    var filterWrap = $('.admin__data-grid-filters-wrap');
                    if (filterWrap.length) {
                        clearInterval(test);
                        filterWrap.css('padding','15px 0');
                        filterWrap.find('button[data-action="grid-filter-cancel"]').hide();
                        filterWrap.find('fieldset').css('padding','0px');
                        $('.admin__data-grid-filters-wrap').addClass('_show');
                    }
                }, 50);

                var test3 = setInterval(function() {
                    var filterWrap = $('.action-cancel');
                    if (filterWrap.length) {
                        clearInterval(test3);
                        filterWrap.hide()
                    }
                }, 50);

                var activeFilterBtn = setInterval(function() {
                    var filterAction = $('.data-grid-filters-action-wrap');
                    if (filterAction.length) {
                        clearInterval(activeFilterBtn);
                        filterAction.find('button').addClass('_active');
                    }
                }, 50);
            });

            // SETS GRID RENDER PARAMS
            var mainEntityCode = 'card_game';

            var productSource = registry.get('product_form.product_form_data_source');
            var mainEntityValue = productSource ? productSource.data.product[mainEntityCode] : '';
            var isHavingSingles = productSource ? productSource.data.product['is_having_singles'] : '';
            // var sc_set_type = productSource ? productSource.data.product['sc_set_type'] : '';
            // var release = wizard.data.release ?? null;

            var releaseGrid = this.productsProvider();
            if (releaseGrid) {
                // var setTypeField = registry.get('product_form.product_form.category-details.container_sc_set_type.sc_set_type');
                // console.log(setTypeField.options())
                // var sc_set_typeLabel = setTypeField.options().find(function (option) {
                //     return option.value === setTypeField.value._latestValue;
                // });

                // console.log('sc_set_typeLabel')
                // console.log(sc_set_typeLabel)
                releaseGrid.set('params.' + mainEntityCode, mainEntityValue);
                // // releaseGrid.set('params.sc_release', release['sc_release']);
                // if (sc_set_typeLabel) {
                //     releaseGrid.set('params.sc_set_type', sc_set_typeLabel.label);
                // }
                releaseGrid.set('params.is_singles', isHavingSingles ? 1 : 0);

                console.log(wizard.data.release)
                var releaseData = wizard.data.release;
                if (releaseData) {
                    var cardRelease = releaseData.card_release;
                    if (cardRelease) {
                        releaseGrid.set('params.card_release', cardRelease);
                    }
                }
                releaseGrid.reload();
            }
        },

        /**
         * Initialize observables.
         *
         * @returns {Object} Chainable.
         */
        initObservable: function () {
            this._super().observe(
                [
                    'changeProductData',
                    'selectedRelease',
                    'release',
                    'attributes'
                ]
            );

            this.showGridAssignProduct();

            return this;
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

            // if (!this.paramsUpdated) {
            //     this.gridInitialized = true;
            //     this.paramsUpdated = true;

            //     attrCodes = this._getAttributesCodes();

            //     if (this.currentProductId) {
            //         usedProductIds.push(this.currentProductId);
            //     }

            //     filterModifier['entity_id'] = {
            //         'condition_type': 'nin', value: usedProductIds
            //     };
            //     attrCodes.each(function (code) {
            //         filterModifier[code] = {
            //             'condition_type': 'notnull'
            //         };
            //     });

            //     if (this.typeGrid === 'changeProduct') {
            //         attributes = JSON.parse(this.product.attributes);

            //         filterModifier = _.extend(filterModifier, _.mapObject(attributes, function (value) {
            //             return {
            //                 'condition_type': 'eq',
            //                 'value': value
            //             };
            //         }));

            //         params.filters = attributes;
            //     } else {
            //         params.filters = {};
            //     }

            //     params['attributes_codes'] = attrCodes;

            //     this.set('externalProviderParams', params);
            //     this.set('externalFiltersModifier', filterModifier);
            // }
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
         * @param {*} selected
         */
        doSelectedAttributesLabels: function (selected) {
            var labels = [];

            var multiselect = this.multiselect()

            console.log('multiselect')
            console.log(multiselect)

            console.log('selected')
            console.log(selected)

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

                console.log('setttt')
                console.log(set)

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
            console.log('labels')
            console.log(labels)
            this.selectedRelease(labels.join(', '));

            console.log(this.selectedRelease())
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
            wizard.data.release = this.release();
            
            // console.log(this.selectedRelease())
            if (!this.selectedRelease().length) {
                throw new Error($.mage.__('Please select release.'));
            }
            
            // this.setNotificationMessage();

            var stepWizard = this.wizard.getStep(2);
            console.log(stepWizard)
            stepWizard.showGridAssignProduct();
        },
    });
});
