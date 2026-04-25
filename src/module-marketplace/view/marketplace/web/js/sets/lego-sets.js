// jscs:disable requireDotNotation
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
// jscs:disable jsDoc
define([
    'uiComponent',
    'jquery',
    'ko',
    'underscore',
    'Magento_Ui/js/modal/alert',
    'uiRegistry',
    'mage/translate'
], function (Component, $, ko, _, alert, registry, $t) {
    'use strict';

    function UserException(message) {
        this.message = message;
        this.name = 'UserException';
    }
    UserException.prototype = Object.create(Error.prototype);

    return Component.extend({
        defaults: {
            opened: false,
            attributes: [],
            usedAttributes: [],
            attributeCodes: [],
            setsData: {},
            singlesData: {},
            productMatrix: [],
            variations: [],
            formSaveParams: [],
            productAttributes: [],
            disabledAttributes: [],
            fullAttributes: [],
            rowIndexToEdit: false,
            productAttributesMap: null,
            valueSets: [],
            valueSingles: [],
            modules: {
                associatedProductGrid: '${ $.configurableProductGrid }',
                wizardButtonElement: '${ $.wizardModalButtonName }',
                formElement: '${ $.formName }'
            },
            links: {
                valueSets: '${ $.provider }:${ $.dataScopeSets }',
                valueSingles: '${ $.provider }:${ $.dataScopeSingles }',
                usedAttributes: '${ $.provider }:${ $.dataScopeAttributes }',
                setsData: '${ $.provider }:${ $.dataScopeSetsData }',
                singlesData: '${ $.provider }:${ $.dataScopeSinglesData }',
                attributeCodes: '${ $.provider }:${ $.dataScopeAttributeCodes }'
            }
        },
        initialize: function () {
            this._super();

            var variations = this.variations;
            if (variations) {
                // this.render(variations, {});
            }
            
            // this.changeButtonWizard();
            // this.initProductAttributesMap();
            this.disableConfigurableAttributes(this.productAttributes);
        },
        initObservable: function () {
            this._super().observe(
                'actions opened attributes productMatrix valueSingles valueSets usedAttributes setsData singlesData attributeCodes'
            );

            return this;
        },
        render: function (set, singles) {
            // this.populateVariationMatrix(variations);
            this.handleValue(set, singles);
        },
        handleValue: function (set, singles) {
            var setsArr = [], singlesArr = [];

            if (set) {
                // var getImagesRegex = /<img[^>]+src="https:\/\/([^">]+)/g;
                // var setImages = getImagesRegex.exec(set.images);

                var thumb = '';
                
                var allImages = set.all_images;
                if (allImages) {
                    var str_array = allImages.split(',');
                    
                    thumb = str_array[0];
                }
                
                var setsObj = _.extend(set, {}, {
                    productId: null,
                    images: thumb,
                    release_year: set.release_year,
                    // release_type: set.release_type,
                    bricks_set_name: set.bricks_set_name,
                    // set_abbreviation: set.card_set_abbreviation,
                    // number_of_cards: set.number_of_cards
                });
                
                setsArr.push(setsObj);

                this.setsData(setsObj);
            }

            if (singles) {
                var thumb = '';
                
                var allImages = singles.all_images;
                if (allImages) {
                    var str_array = allImages.split(',');
                    
                    thumb = str_array[0];
                }
    
                var singlesObj = _.extend(singles, {}, {
                    productId: null,
                    images: thumb,
                    card_name: singles.card_name,
                    card_number: singles.card_number,
                    card_rarity: singles.card_rarity,
                    language: singles.language
                });
    
                singlesArr.push(singlesObj);

                this.singlesData(singlesObj);
            }
            
            this.valueSets(setsArr);
            this.valueSingles(singlesArr);
        },
        disableConfigurableAttributes: function (attributes) {
            // var element;

            // _.each(this.disabledAttributes, function (attribute) {
            //     registry.get('index = ' + attribute).disabled(false);
            // });
            // this.disabledAttributes = [];

            // _.each(attributes, function (attribute) {
            //     element = registry.get('index = ' + attribute.code);
            //     if (!_.isUndefined(element)) {
            //         element.disabled(true);
            //         this.disabledAttributes.push(attribute.code);
            //     }
            // }, this);
        }
    });
});
