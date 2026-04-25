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
    'mage/translate'
], function (Component, $, _, registry, urlBuilder) {
    'use strict';

    /**
     * @param {Function} provider
     */
    var initNewAttributeListener = function (provider) {
        $('[data-role=product-cardgamemtg-sets-matrix]').on('add', function () {
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

            var productSource = registry.get('product_form.product_form_data_source');
            var is_having_singles = productSource ? productSource.data.product['is_having_singles'] : '';
            var card_game_mtg = productSource ? productSource.data.product['card_game_mtg'] : '';

            // if (!is_having_singles) {
            // var step2link = $('a[href="#select-cardgamemtg-sets-steps-wizard_step2"');
            // if (step2link) {
            //     var step2 = step2link.parent();
            //     step2.hide();
            // }

            // if (card_game_mtg == 24820) {
                
            // }
            // console.log(card_game_mtg)
            // var step2link = $('a[href="#select-cardgamemtg-sets-steps-wizard_step2"');
            // if (step2link) {
            //     var step2 = step2link.parent();
            //     if (step2) {
            //         if (card_game_mtg == 24820) {
            //             step2.show();
            //         } else {
            //             step2.hide();
            //         }
            //     }
            // }

            console.log('is_having_singles'+is_having_singles)
            
            var step3link = $('a[href="#select-cardgamemtg-sets-steps-wizard_step3"');
            if (step3link) {
                var step3 = step3link.parent();
                if (step3) {
                    if (!is_having_singles) {
                        step3.hide();
                    } else {
                        step3.show();
                    }
                }
            }

            document.body.style.counterReset = "i";
            
            var stepsWizard = registry.get('product_form.product_form.cardgame.setsModal.wizard');
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
            })
            
            var productSource = registry.get('product_form.product_form_data_source');
            var card_game = productSource ? productSource.data.product['card_game'] : '';
            var card_game_mtg = productSource ? productSource.data.product['card_game_mtg'] : '';

            var setsGrid = this.attributeProvider();
            if (setsGrid) {
                setsGrid.set('params.card_game', card_game);
                var isHavingSingles = productSource ? productSource.data.product['is_having_singles'] : '';
                setsGrid.set('params.is_singles', isHavingSingles ? 1 : 0);
                setsGrid.reload();
            }

             // SETS GRID RENDER PARAMS
            var mainEntityCode = 'card_game';
            var subMainEntityCode = 'card_game_mtg';

            var productSource = registry.get('product_form.product_form_data_source');
            var mainEntityValue = productSource ? productSource.data.product[mainEntityCode] : '';
            var subMainEntityValue = productSource ? productSource.data.product[subMainEntityCode] : '';
            var isHavingSingles = productSource ? productSource.data.product['is_having_singles'] : '';

            var setsGrid = this.attributeProvider();
            if (setsGrid) {
                setsGrid.set('params.' + mainEntityCode, mainEntityValue);
                setsGrid.set('params.' + subMainEntityCode, subMainEntityValue);
                setsGrid.set('params.is_singles', isHavingSingles ? 1 : 0);
                setsGrid.reload();
            }
        },

        /**
         * Set notification message.
         */
        setNotificationMessage: function () {
            /*eslint-disable max-len*/
            // var msg = $.mage.__('Please select sets or create new one if not exist.');

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
                // this.sets = [];
            // }
            _.each(selected, function (entityId) {
                var set;

                set = _.findWhere(this.multiselect().rows(), {
                    'entity_id': entityId
                });

                if (set) {
                    // if (!is_having_singles) {
                            // this.sets.push(set);
                        // } else {
                            this.sets(set);
                        // }
                    this.attributesLabels[set['entity_id']] = set['card_set'];
                }
                
                labels.push(this.attributesLabels[entityId]);
            }.bind(this));
            this.selectedSets(labels.join(', '));
        },

        checkSecretLairSets: function(sets) {
            if (sets) {
                urlBuilder.setBaseUrl(BASE_URL);
                var url = urlBuilder.build("marketplace/productattributeslink/product/checkSecretLairSets");
                
                $.ajax({
                    url: url,
                    async: false,
                    data: {
                        form_key: window.FORM_KEY,
                        sets: sets
                    },
                    showLoader: true,
                    dataType: 'json',
                    success: function(resp) {
                        if (resp.data) {
                            return true;
                        }
                    },
                    complete: function() {
                        $('body').trigger('processStop');
                    }
                });
            }

            return false;
        },

        /**
         * @param {Object} wizard
         */
        force: function (wizard) {
            wizard.data.attributesIds = this.multiselect().selected;

            wizard.data.sets = this.sets();

            if (!wizard.data.attributesIds() || wizard.data.attributesIds().length === 0) {
                throw new Error($.mage.__('Please select drop.'));
            }

            // Check is secret lair sets
            var sets = this.sets();
            var setsData = this.sets();

            // urlBuilder.setBaseUrl(BASE_URL);
            // var url = urlBuilder.build("marketplace/productattributeslink/product/checkSecretLairSets");

            var _this = this;
            
            // $.ajax({
            //     url: url,
            //     data: {
            //         form_key: window.FORM_KEY,
            //         sets: sets
            //     },
            //     showLoader: true,
            //     dataType: 'json',
            //     success: function(resp) {
            // var step2link = $('a[href="#select-cardgamemtg-sets-steps-wizard_step2"');
                    var isSecretLairSets = false;
            //         if (sets.secret_lair_card_set) {
            // //         // if (resp.data) {
                        isSecretLairSets = true;
                        
            //             if (step2link) {
            //                 var step2 = step2link.parent();
            //                 step2.show();
            //             }

            //             // Add secret lair step
            //             var currentIndex = wizard.index;
            //             var newIndex = currentIndex+1;
            //             var switchIndex = newIndex+1;
                        
            //             var nextStep = wizard.getStep(newIndex);
            //             if (nextStep && nextStep.index != "select-cardgamemtg-sets-steps-wizard_step2") {
            //                 var steps = wizard.steps;
            //                 if (steps) {
            //                     var b = steps[newIndex];
            //                     steps[newIndex] = steps[switchIndex];
            //                     steps[switchIndex] = b;
            //                 }
            //                 wizard.steps = steps;
            //             }
            //         // }
            //         } else {
            //             if (step2link) {
            //                 var step2 = step2link.parent();
            //                 step2.hide();
            //             }

            //             // Skip secret lair step
            //             var currentIndex = wizard.index;
            //             var newIndex = currentIndex+1;
            //             var switchIndex = newIndex+1;
                        
            //             var nextStep = wizard.getStep(newIndex);
            //             if (nextStep && nextStep.index != "select-cardgamemtg-sets-steps-wizard_step3") {
            //                 var steps = wizard.steps;
            //                 if (steps) {
            //                     var b = steps[newIndex];
            //                     steps[newIndex] = steps[switchIndex];
            //                     steps[switchIndex] = b;
            //                 }
            //                 wizard.steps = steps;
            //             }
            //         }

            // document.body.style.counterReset = "i";
                    
                    var setsMatrixField = registry.get('product_form.product_form.cardgame_sets.sets-matrix');
            
            
                    var productSource = registry.get('product_form.product_form_data_source');
                    var is_having_singles = productSource ? productSource.data.product['is_having_singles'] : '';

                    
                    _this.setNotificationMessage();
                // },
                // complete: function() {
                //     $('body').trigger('processStop');
                // }
            // });
        },
        getLabelByValue: function(data, value) {
            const item = data.find(entry => entry.value === value);
            return item ? item.label : null;
        },
        uploadImage: async function(imgUrl) {
            imgUrl = imgUrl.replace("/.thumbs","");
            
            let response = await fetch(imgUrl);
              let data = await response.blob();
              let metadata = {
                type: 'image/jpeg'
              };
                var filename = imgUrl.match(/.*\/(.*)$/)[1];
              let file = new File([data], filename, metadata);
            $(document).find('input[type=file]').fileupload('send', {files: [file]});

            return true;
        },
        convertToSlug: function(Text) {
          return Text.toLowerCase()
            .replace(/ /g, "-")
            .replace(/[^\w-]+/g, "");
        },
        checkURL: function(url) {
            return(url.match(/\.(jpeg|jpg|gif|png)$/) != null);
        },
        makeid: function(length) {
            let result = '';
            const characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
            const charactersLength = characters.length;
            let counter = 0;
            while (counter < length) {
              result += characters.charAt(Math.floor(Math.random() * charactersLength));
              counter += 1;
            }
            return result;
        },

        /**
         * Back.
         */
        back: function () {
        }
    });
});
