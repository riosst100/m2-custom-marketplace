/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
// jscs:disable jsDoc
define([
    'uiComponent',
    'jquery',
    'ko',
    'mageUtils',
    'uiRegistry',
    'underscore',
    'mage/translate'
], function (Component, $, ko, utils, uiRegistry, _) {
    'use strict';

    var initNewAttributeListener = function (provider) {
        $('[data-role=product-variations-matrix]').on('add', function () {
            provider().reload();
        });
    };

    return Component.extend({
        attributesLabels: {},
        attributesCodes: null,
        attributesIds: null,
        attributesCodesArr: {},
        stepInitialized: false,
        defaults: {
            modules: {
                multiselect: '${ $.multiselectName }',
                attributeProvider: '${ $.providerName }'
            },
            listens: {
                '${ $.multiselectName }:selected': 'doSelectedAttributesLabels',
                '${ $.multiselectName }:rows': 'doSelectSavedAttributes'
            },
            notificationMessage: {
                text: null,
                error: null
            },
            attributes: [],
            selectedAttributes: []
        },
        initialize: function () {
            this._super();
            this.selected = [];

            // initNewAttributeListener(this.attributeProvider);
        },
        initObservable: function () {
            this._super().observe(['selectedAttributes','attributes']);

            return this;
        },
        getLabelByValue: function(data, value) {
            const item = data.find(entry => entry.value === value);
            return item ? item.label : null;
        },
        render: function (wizard) {
            this.wizard = wizard;
            this.setNotificationMessage();

            var productSource = uiRegistry.get('product_form.product_form_data_source');
            var card_game = productSource ? productSource.data.product['card_game'] : '';
            var category_ids = productSource ? productSource.data.product['category_ids'] : '';
            var card_product_type = productSource ? productSource.data.product['card_product_type'] : '';
            var product = productSource ? productSource.data.product : '';

            const keysToExclude = ['copy_to_stores','configurable_attributes_data','stock_data','website_ids'];
            const filteredProductData = Object.fromEntries(
              Object.entries(product).filter(([key]) => !keysToExclude.includes(key))
            );

            var productTypeField = uiRegistry.get('product_form.product_form.category-details.container_card_product_type.card_product_type');

            var options = productTypeField.options._latestValue;
            var productTypeLabel = this.getLabelByValue(options, card_product_type);
            
            var stepReleaseLink = $('a[href="#variation-steps-wizard_step.release"');
            if (stepReleaseLink) {
                var stepRelease = stepReleaseLink.parent();
                if (stepRelease) {
                    if (productTypeLabel == "Singles") {
                        stepRelease.hide();
                    } else {
                        stepRelease.show();
                    }
                }
            }

            var stepReleaseLink2 = $('a[href="#variation-steps-wizard_step.sets"');
            if (stepReleaseLink2) {
                var stepRelease2 = stepReleaseLink2.parent();
                if (stepRelease2) {
                    if (productTypeLabel == "Sealed Products" || productTypeLabel == "Pre Orders" || productTypeLabel == "Supplies") {
                        stepRelease2.hide();
                    } else {
                        stepRelease2.show();
                    }
                }
            }

            var sealedproductsStepLink = $('a[href="#variation-steps-wizard_step.sealedproducts"');
            if (sealedproductsStepLink) {
                var sealedproductsStep = sealedproductsStepLink.parent();
                if (sealedproductsStep) {
                    if (productTypeLabel == "Sealed Products" || productTypeLabel == "Pre Orders" || productTypeLabel == "Supplies") {
                        sealedproductsStep.show();
                    } else {
                        sealedproductsStep.hide();
                    }
                }
            }

            var singlesStepLink = $('a[href="#variation-steps-wizard_step2"');
            if (singlesStepLink) {
                var singlesStep = singlesStepLink.parent();
                if (singlesStep) {
                    if (productTypeLabel == "Singles") {
                        singlesStep.show();
                    } else {
                        singlesStep.hide();
                    }
                }
            }

            document.body.style.counterReset = "i";

            var setsGrid = this.attributeProvider();
            setsGrid.set('params.product', filteredProductData);
            setsGrid.set('params.category_ids', category_ids);
            setsGrid.set('params.card_product_type', card_product_type);
            setsGrid.set('params.card_game', card_game);
            setsGrid.reload();
        },
        setNotificationMessage: function () {
            // if (this.mode === 'edit') {
                // this.wizard.setNotificationMessage($.mage.__('When you remove or add an attribute, we automatically ' +
                // 'update all configurations and you will need to recreate current configurations manually.'));
            // }
        },
        doSelectSavedAttributes: function () {
            if (this.stepInitialized === false) {
                this.stepInitialized = true;
                //cache attributes labels, which can be present on the 2nd page
                _.each(this.initData.attributes, function (attribute) {
                    this.attributesLabels[attribute.id] = attribute.label;
                    this.attributeCodes[attribute.id] = attribute.attribute_code;
                }.bind(this));
                this.multiselect().selected(_.pluck(this.initData.attributes, 'id'));
            }
        },
        doSelectedAttributesLabels: function (selected) {
            var labels = [];

            var multiselect = this.multiselect();

            if (selected.length) {
                selected.forEach(function(data, index) {
                    if (index != selected.length-1) {
                        multiselect.toggleSelect(data);
                        selected.splice(index, 1);
                    }
                });
            }

            this.selected = selected;
            _.each(selected, function (attributeId) {
                var attribute;

                if (!this.attributesLabels[attributeId]) {
                    attribute = _.findWhere(this.multiselect().rows(), {
                        attribute_id: attributeId
                    });

                    if (attribute) {
                        this.attributesLabels[attribute.attribute_id] = attribute.frontend_label;
                        this.attributesCodesArr[attribute.attribute_id] = attribute.attribute_code;
                        this.attributesCodes = attribute.attribute_code;
                        this.attributesIds = attribute.attribute_id;
                    }
                }

                if (!this.attributesCodesArr[attributeId]) {
                    attribute = _.findWhere(this.multiselect().rows(), {
                        attribute_id: attributeId
                    });

                    if (attribute) {
                        this.attributesCodesArr[attribute.attribute_id] = attribute.attribute_code;
                        this.attributesCodes = attribute.attribute_code;
                        this.attributesIds = attribute.attribute_id;
                    }
                }
                labels = this.attributesLabels[attributeId];
            }.bind(this));
            this.selectedAttributes(labels);

            // var setsGridProvider = uiRegistry.get('product_form.product_form.configurable_associated_product_modal.variations_sets_product_listing');
            // console.log(setsGridProvider)
            // setsGridProvider.multiselect().selected([]);
        },
        createAttribute: function (attribute, index) {
            attribute.chosenOptions = _.pluck(attribute.options, 'value');
            attribute.options = ko.observableArray(_.map(attribute.options, function (option) {
                option.id = utils.uniqueid();

                return option;
            }));
        
            return attribute;
        },
        requestAttributes: function (attributeIds) {
            $.ajax({
                type: 'POST',
                url: this.optionsUrl,
                data: {
                    attributes: attributeIds
                },
                showLoader: true
            }).done(function (attributes) {
                attributes = _.sortBy(attributes, function (attribute) {
                    return this.wizard.data.attributesIds.indexOf(attribute.id);
                }.bind(this));
                
                this.attributes(_.map(attributes, this.createAttribute));
            }.bind(this));
        },
        force: function (wizard) {
            wizard.data.attributesIds = this.multiselect().selected;
            wizard.data.attributesCodes = this.attributesCodesArr;

            this.requestAttributes(wizard.data.attributesIds());

            wizard.data.attributes = this.attributes;

            var source = uiRegistry.get('product_form.product_form_data_source');
            source.set('data.attribute_codes', [this.attributesCodes]);
            source.set('data.attributes', [this.attributesIds]);

            if (!wizard.data.attributesIds() || wizard.data.attributesIds().length === 0) {
                throw new Error($.mage.__('Please select at least 1 variation(s).'));
            }
        },
        back: function () {
        }
    });
});
