define([
    'jquery',
    'uiRegistry',
    'mage/url', 
    'Magento_Ui/js/modal/modal',
    'Magento_Ui/js/lib/key-codes',
], function($, uiRegistry, urlBuilder, modal, keyCodes) {
    'use strict';

    const LoaderManager = (() => {
        let count = 0;
    
        return {
            start() {
                if (count === 0) $('body').trigger('processStart');
                count++;
            },
            stop() {
                count--;
                if (count <= 0) {
                    count = 0;
                    $('body').trigger('processStop');
                }
            }
        };
    })();

    return {
        toggleVisibleProductDetails: function(visible) {
            const currentUrl = window.location.href;
            
            // Handle Custom Table Selection
            var source = '';
            var sourceField = uiRegistry.get('product_form.product_form.product-details.container_source.source');
            if (sourceField) {
                source = sourceField.value();
            }
            if (source != "database") {
                var fieldset = uiRegistry.get('product_form.product_form.product-details');
                if (fieldset) {
                    fieldset.visible(visible);
                }
                
                var contentFieldset = uiRegistry.get("product_form.product_form.content");
                if (contentFieldset) {
                    contentFieldset.visible(visible);
                }
                
                var galleryFieldset = uiRegistry.get("product_form.product_form.gallery");
                if (galleryFieldset) {
                    galleryFieldset.visible(visible);
                }
            }
        },
        isConfigurableProduct: function() {
            // Get the current URL
            const currentUrl = new URL( window.location.href);
            const pathSegments = currentUrl.pathname.split('/').filter(segment => segment);
            
            let typeValue = null;
            const typeIndex = pathSegments.indexOf('type');
            if (typeIndex !== -1 && typeIndex + 1 < pathSegments.length) {
                typeValue = pathSegments[typeIndex + 1]; // Get the value after 'type'
            }

            var productType = typeValue ?? '';
            
            return productType == "configurable" ? true : false;
        },
        // PRODUCT CREATION | Check Custom Table Exists
        checkCustomTableExists: function(field) {

            if (field.index == "attribute_set_id") {
                return false;
            }

            if (this.isConfigurableProduct()) {
                return false;
            } 
            
            // var code = field.code;
            // var setsAndSinglesFieldset = uiRegistry.get("product_form.product_form.sets");
            // var customTableCategories = ["card_product_type","bricks_brand"];
            // if (!customTableCategories.includes(code)) {
            //     // if (setsAndSinglesFieldset) {
            //     //     setsAndSinglesFieldset.visible(false)
            //     // }
            // }

            // var _this = this;
            // var onChangedAttributes = [
            //     'category_ids',
            //     'nsc_categories',
            //     'sc_categories',
            //     'card_game',
            //     'card_game_mtg',
            //     'miniature_game',
            //     'toys_brand',
            //     'living_card_game',
            //     'bricks_brand'
            // ];
            // if (onChangedAttributes.includes(code)) {
            //     this.hideAllCustomTableSelection();
            // }

            // // if (code == "toys_brand")

            // var resetAttributes = [
            //     'nsc_categories',
            //     'sc_categories',
            //     'card_game',
            //     'toys_brand',
            //     'miniature_game',
            //     'action_toys_sub_categories',
            //     'living_card_game',
            //     'bricks_brand'
            // ];
            // // if (resetAttributes.includes(code)) {
            // //     var categoryDetailFieldSet = uiRegistry.get('product_form.product_form.category-details');
            // //     if (categoryDetailFieldSet) {
            // //         categoryDetailFieldSet._elems.forEach((element) => {
            // //             var index = element.index;
            // //             if (index.includes('_line') || index.includes('product_type') || index.includes('_categories')) {
            // //                 element._elems.forEach((fieldElement) => {
            // //                     fieldElement.value([]);
            // //                 })
            // //             }
            // //         });
            // //     }
            // // }
            

            // // if (!entityVal) {
            // //     if (setsAndSinglesFieldset) {
            // //         setsAndSinglesFieldset.visible(false)
            // //     }
                
            // //     return false;
            // // }

            var code = field.index;
            var val = field.value._latestValue;

            if (code != "attribute_set_id" && !window.CUSTOM_TABLE_TYPE_ATTR_CODES.includes(code)) {
                const platformDbSelectScopes = window.PLATFORM_DB_SELECTION_SCOPES;
                platformDbSelectScopes.forEach(scope => {
                    var setsAndSinglesFieldset = uiRegistry.get("product_form.product_form." + scope);
                    if (setsAndSinglesFieldset) {
                        setsAndSinglesFieldset.visible(false)
                    }
                })
                
                return false;
            }

            // Check is custom table exist
            $('body').trigger('processStart');

            urlBuilder.setBaseUrl(BASE_URL);
            var url = urlBuilder.build("marketplace/productattributeslink/product/checkCustomTableExist");

            // Current field data
            var productSource = uiRegistry.get('product_form.product_form_data_source');
            var sourceCreated = productSource ? productSource.data.product['source'] : '';

            var mainAttrCode = null;
            var mainAttrVal = null;

            var categoryMetaData = this.getCategoryMetaData();
            if (categoryMetaData) {
                mainAttrCode = categoryMetaData.mainEntityCode;

                var productSource = uiRegistry.get('product_form.product_form_data_source');
                if (productSource) {
                    mainAttrVal = productSource.data.product[mainAttrCode];
                }
            }

            let dataParams = {
                form_key: window.FORM_KEY,
                code: code,
                val: val,
                mainAttrCode: mainAttrCode,
                mainAttrVal: mainAttrVal,
                source: sourceCreated,
                // product: productSource.data.product
            };

            let filters = {};

            var categoryDetailFieldSet = uiRegistry.get('product_form.product_form.category-details');
            if (categoryDetailFieldSet) {
                categoryDetailFieldSet._elems.forEach((element) => {
                    var index = element.index.replace('container_', '');
                    if (index != "category_ids" && index != mainAttrCode) {
                        element._elems.forEach((fieldElement) => {
                            var options = fieldElement.options._latestValue;
                            if (fieldElement.value()) {
                                const valueLabel = this.getLabelByValue(options, fieldElement.value());
                                if (valueLabel) {
                                    filters[index] = valueLabel;
                                }
                            }
                        })
                    }
                });
            }

            dataParams.filters = filters;

            const _this = this;
            
            $.ajax({
                url: url,
                data: dataParams,
                dataType: 'json',
                success: function(resp) {
                    if (resp.data) {
                        var data = resp.data;
                        if (data) {
                            var scope = data.sets_selection_scope;
                            var setsAndSinglesFieldset = uiRegistry.get("product_form.product_form." + scope);
                            var noDatabaseFieldset = uiRegistry.get("product_form.product_form.no_database");
                            var noDatabaseField = uiRegistry.get("product_form.product_form.no_database.no_database");
                            var productDetailsFieldset = uiRegistry.get('product_form.product_form.product-details');
                            
                            // if (fieldset) {
                            //     fieldset.visible(true);
                            // }
                            
                            var isCustomTableExist = data.is_custom_table_exist;
                            var is_next_step = data.is_next_step;
                            if (is_next_step) {
                                if (setsAndSinglesFieldset) {
                                    setsAndSinglesFieldset.visible(false)
                                }

                                if (noDatabaseFieldset) {
                                    noDatabaseFieldset.visible(false)
                                }
                            } else if (isCustomTableExist) {
                                if (setsAndSinglesFieldset) {
                                    setsAndSinglesFieldset.visible(true)
                                }

                                if (noDatabaseFieldset) {
                                    noDatabaseFieldset.visible(false)
                                }
                            } else {
                                if (setsAndSinglesFieldset) {
                                    setsAndSinglesFieldset.visible(false)
                                }
                                
                                if (noDatabaseFieldset) {
                                    _this.hideAllSetsReleaseFieldset();
                                    noDatabaseFieldset.visible(true)
                                }

                                // if (productDetailsFieldset) {
                                //     productDetailsFieldset.visible(false)
                                // }
                            }

                            var isHavingSinglesField = uiRegistry.get('product_form.product_form.product-details.is_having_singles');

                            var isHavingSingles = data.is_singles;
                            if (isHavingSinglesField) {
                                isHavingSinglesField.value(isHavingSingles);
                            }

                            var isHavingReleaseField = uiRegistry.get('product_form.product_form.product-details.is_having_release');

                            var isHavingRelease = data.is_having_release;
                            if (isHavingReleaseField) {
                                isHavingReleaseField.value(isHavingRelease);
                            }

                            var is_sealedproductsField = uiRegistry.get('product_form.product_form.product-details.is_sealedproducts');

                            var is_sealedproducts = data.is_sealedproducts;
                            if (is_sealedproductsField) {
                                is_sealedproductsField.value(is_sealedproducts);
                            }
                        }

                        // // if (options) {
                        // //     if (inputName == "attribute_code") {
                        // var field = uiRegistry.get(parentName + ".set_id");
                        // //     } else {
                        // //         var field = uiRegistry.get(parentName + ".target_attribute_options");
                        // //     }
                        // if (field) {
                        //     field.options(options);
                        //     field.cacheOptions.plain = options;
                        //     field.cacheOptions.tree = options;
                        //     field.isDisplayMissingValuePlaceholder = false;

                        //     if (field.multiple) {
                        //         field.toggleOptionSelected(field.value());
                        //     }

                        //     if (!options.length) {
                        //         currentField.error('No sets found. Please upload the sets first.')
                        //         field.disabled(true)
                        //     } else {
                        //         field.disabled(false)
                        //     }
                        // }

                        // if (field && isEdit) {
                        //     field.disabled(true)
                        // }
                        // } 
                    }
                },
                complete: function() {
                    $('body').trigger('processStop');

                    
                }
            });
        },
        hideAllSetsReleaseFieldset: function() {
            var allComponents = uiRegistry.filter(function (item) {
                return item.name && item.name.endsWith('_sets');
            });

            
        
            allComponents.forEach(function (component) {
                component.visible(false);
            });
            // var scopes = [
            //     'nonsportcard_sets',
            //     'actiontoy_sets',
            //     'anime_sets',
            //     'legosets_sets',
            //     'livingcardgame_sets',
            //     'miniaturegame_sets', 
            //     'sportcard_sets',
            //     'cardgame_sets',
            //     'nonsportcard_sets'
            // ];
            
            // scopes.forEach((scope) => {
            //     var customTableSelection = uiRegistry.get('product_form.product_form.' + scope);
            //     if (customTableSelection) {
            //         customTableSelection.visible(false)
            //     }
            // });
        },
        filterDataProvider: function(step, wizard) {
            // SETS GRID RENDER PARAMS
            var categoryMetaData = this.getCategoryMetaData();
            if (categoryMetaData) {
                var categories = categoryMetaData.categories;
                var mainEntityCode = categoryMetaData.mainEntityCode;
                var mainEntity = categoryMetaData.mainEntity;
                var isSingles = categoryMetaData.isSingles;
                var isSealedProducts = categoryMetaData.isSealedProducts;
    
                var listingGridProvider = step.attributeProvider();
                if (listingGridProvider && mainEntity) {
                    // Get current params
                    let currentParams = listingGridProvider.get('params') || {};

                    // Set main entity and is singles
                    let params = {
                        [mainEntityCode]: mainEntity,
                        is_singles: isSingles ? 1 : 0,
                        main_attr_code: mainEntityCode,
                        main_attr_val: mainEntity,
                        is_sealed_products: isSealedProducts ? 1 : 0,
                    };
                
                    // Filter by set id
                    var setsData = wizard.data.sets;
                    if (setsData) {
                        params.set_id = setsData.entity_id
                    }
                    var releaseData = wizard.data.release;

                    if (releaseData) {

                        const releaseValue = Object.keys(releaseData)
                          .filter(key => key.endsWith("_release") && !key.includes("_jp"))
                          .map(key => releaseData[key])[0];
                        params.release = releaseValue;

                    }

                    let newParams = Object.assign({}, currentParams, params);
                    

                    

                    // var productSource = uiRegistry.get('product_form.product_form_data_source');
                    // if (productSource) {
                    //     let scSetType = productSource.data.product['sc_set_type'];
                    //     if (scSetType) {
                    //         newParams.grid_filters = {};
                    //         newParams.grid_filters['sc_set_type'] = this.getOptionLabel('sc_set_type', scSetType);
                    //     }
                    // }

                    //     // var setTypeField = uiRegistry.get('product_form.product_form.category-details.container_sc_set_type.sc_set_type');
            //     // var sc_set_typeLabel = setTypeField.options().find(function (option) {
            //     //     return option.value === setTypeField.value._latestValue;
            //     // });

            //     setsGrid.set('params.' + mainEntityCode, mainEntityValue);
            //     // // setsGrid.set('params.sc_release', release['sc_release']);
            //     // if (sc_set_typeLabel) {
            //     //     setsGrid.set('params.sc_set_type', sc_set_typeLabel.label);
            //     // }
            //     setsGrid.set('params.is_singles', isHavingSingles ? 1 : 0);

            //     var releaseData = wizard.data.release;
            //     if (releaseData) {
            //         var cardRelease = releaseData.card_release;
            //         if (cardRelease) {
            //             setsGrid.set('params.card_release', cardRelease);
            //         }
            //     }
            //     setsGrid.reload();
    
                    listingGridProvider.set('params', newParams);
                    this.updateFilters(newParams, wizard);
                }
            }
        },
        modifyFilterElements: function() {
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
        },
        hideSteps: function(wizard) {
            var categoryMetaData = this.getCategoryMetaData();
            if (categoryMetaData) {
                var categories = categoryMetaData.categories;
                
                var productSource = uiRegistry.get('product_form.product_form_data_source');
                var is_having_singles = productSource ? productSource.data.product['is_having_singles'] : '';
                var is_having_release = productSource ? productSource.data.product['is_having_release'] : '';
                var is_sealedproducts = productSource ? productSource.data.product['is_sealedproducts'] : '';
    
                var stepReleaseLink = $('a[href="#select-' + categories + '-sets-steps-wizard_step.release');
                if (stepReleaseLink) {
                    var stepRelease = stepReleaseLink.parent();
                    if (stepRelease) {
                        if (is_having_singles || !is_having_release) {
                            stepRelease.hide();
                        } else {
                            // Hide progress bar after step done on singles
                            stepRelease.addClass('no-after');
                            stepRelease.show();
                        }
                    }
                }

                // Get sets step
                var stepSetsLink = $('a[href="#select-' + categories+'-sets-steps-wizard_step.sets');
                if (stepSetsLink) {
                    var stepSets = stepSetsLink.parent();
                    if (stepSets) {
                        if (is_having_singles || !is_having_release && is_sealedproducts) {
                            // Hide progress bar after step done on singles
                            stepSets.addClass('no-after');
                            stepSets.show();
                        } else {
                            stepSets.hide();
                        }
                    }
                }

                var stepSinglesLink = $('a[href="#select-'+categories+'-sets-steps-wizard_step.singles');
                if (stepSinglesLink) {
                    var stepSingles = stepSinglesLink.parent();
                    if (stepSingles) {
                        if (!is_having_singles) {
                            stepSingles.hide();
                        } else {
                            stepSingles.show();
                        }
                    }
                }
    
                var stepSealedProductsLink = $('a[href="#select-'+categories+'-sets-steps-wizard_step.sealedproducts');
                if (stepSealedProductsLink) {
                    var stepSealedProducts = stepSealedProductsLink.parent();
                    if (stepSealedProducts) {
                        if (is_having_singles) {
                            stepSealedProducts.hide();
                        } else {
                            stepSealedProducts.show();
                        }
                    }
                }
    
                // var currentIndex = wizard.index;
                // var newIndex = currentIndex-1;
                
                // var nextStep = wizard.getStep(newIndex);
                // if (nextStep) {
                //     if (
                //         is_having_singles 
                //         && nextStep.index == "select-"+categories+"-sets-steps-wizard_step.release"
                //     ) {
                //         var steps = wizard.steps;
                //         if (steps) {
                //         }
                //         wizard.steps = steps;
                //     }
                // }
                // var firstStep = wizard.getStep(0);
                // wizard.selectedStep = firstStep;
                // wizard.index = 0;
                
    
                // var currentIndex = wizard.index;
                // var newIndex = currentIndex+1;
                // var switchIndex = newIndex+1;
    
                
                
                // var nextStep = wizard.getStep(newIndex);
                // if (nextStep) {
                //     if (
                //         !is_having_singles 
                //         && nextStep.index == "select-sportcard-sets-steps-wizard_step.sets"
                //     ) {
                //         var steps = wizard.steps;
                //         if (steps) {
                //         //     var b = steps[newIndex];
                //             delete steps[newIndex];
                //             steps[newIndex] = steps[switchIndex];
                //         //     steps[switchIndex] = b;
                            
                //             // steps[currentIndex] = steps[newIndex];
                //             // steps[switchIndex] = b;
                //             // steps[switchIndex] = b;
                //         }
                //         wizard.steps = steps;
                //     }
                // }
    
                document.body.style.counterReset = "i";
            }
        },
        getCategoryMetaData: function () {
            const productSource = uiRegistry.get('product_form.product_form_data_source');
            const productData = productSource?.data?.product;

            const customTableMetadata = window.CUSTOM_TABLE_METADATA;
        
            if (productData) {
                // ambil flag is_having_singles (bisa undefined atau kosong)
                const isSingles = productData['is_having_singles'] || false;
                const isSealedProducts = productData['is_sealedproducts'] || false;

                // 🧠 Fungsi bantu untuk resolve alias "@"
                const resolveConfig = (key) => {
                    const config = customTableMetadata[key];
                    if (!config) return null;
                    if (typeof config === 'string' && config.startsWith('@')) {
                        const alias = config.substring(1);
                        return customTableMetadata[alias] || null;
                    }
                    return config;
                };

                // 🔍 Loop semua config, cari yang match dengan key produk
                for (const [categoryKey, configOrAlias] of Object.entries(customTableMetadata)) {
                    const config = resolveConfig(categoryKey);
                    if (!config || !config.mainAttrCode) continue;
                
                    const attrCode = config.mainAttrCode;
                    const value = productData[attrCode];
                    if (value && typeof value === "string" && value.trim() !== "") {
                        // alert(value);
                        return {
                            ...config,
                            categories: categoryKey,
                            mainEntity: value,
                            mainEntityCode: attrCode,
                            mainSetsCode: config.mainSetsAttrCode || '',
                            mainSinglesCode: config.mainSinglesAttrCode || '',
                            isSingles: isSingles,
                            isSealedProducts: isSealedProducts
                        };
                    }
                }
                
                // Jika tidak ada yang cocok
                return {
                    categories: null,
                    mainEntity: null,
                    mainEntityCode: null,
                    isSingles: isSingles,
                    isSealedProducts: isSealedProducts
                };
        
                // daftar kemungkinan category dan key-nya
                // const mappings = {
                //     toys_brand: 'actiontoy',
                //     toys_brand: 'anime',
                //     toys_brand: 'dolls',
                //     card_game: 'cardgame',
                //     living_card_game: 'livingcardgame',
                //     miniature_game: 'miniaturegame',
                //     gw_miniature_game: 'gamesworkshop',
                //     bricks_brand: 'bricks',
                //     diecast_brand: 'diecast',
                //     modelkits_brand: 'modelkits',
                //     trains_brand: 'trains'
                // };
        
                // for (const [key, category] of Object.entries(mappings)) {
                //     const value = productData[key];
                //     if (value) {
                //         // langsung stop di sini jika ketemu
                //         return {
                //             categories: category,
                //             mainEntity: value,
                //             mainEntityCode: key,
                //             mainSetsCode: '',
                //             mainSinglesCode: '',
                //             isSingles: isSingles
                //         };
                //     }
                // }
        
                // // tidak ada mainEntity yang cocok
                // return {
                //     categories: null,
                //     mainEntity: null,
                //     mainEntityCode: null,
                //     isSingles: isSingles
                // };
            }
        
            // fallback jika productData tidak ada
            return {
                categories: null,
                mainEntity: null,
                mainEntityCode: null,
                isSingles: false
            };
        },
        updateFilters: async function(params, wizard) {

            var step = wizard.steps[wizard.index];
            var stepIndex = step.index;
            stepIndex = stepIndex.replace(/\./g, "\\.");

            this.waitForFilters(stepIndex, function(filters) {
                let newParams = Object.assign({}, params, {
                    filter_codes: filters,
                    form_key: window.FORM_KEY
                });

                urlBuilder.setBaseUrl(BASE_URL);
                var url = urlBuilder.build("marketplace/productattributeslink/productCreation/getGridFilters");
                
                $.ajax({
                    url: url,
                    data: newParams,
                    showLoader: true,
                    dataType: 'json',
                    success: function(resp) {
                        if (resp.data) {
                            $.each(resp.data, function(selectName, options) {
                                var filterSelectElement = uiRegistry.get(
                                    'seller_database_cardgame_sets_listing.seller_database_cardgame_sets_listing.listing_top.listing_filters.' + selectName
                                );

                                if (filterSelectElement) {
                                    filterSelectElement.setOptions(options);
                                }
                            });
                        }
                    }
                });
            });
        },
        waitForFilters: function(stepIndex, callback) {
            let target = document.querySelector('#' + stepIndex + ' fieldset.admin__fieldset.admin__data-grid-filters');
        
            if (!target) {
                return;
            }
        
            // Cek langsung dulu, kalau select sudah ada tidak perlu observer
            let els = target.querySelectorAll('select.admin__control-select');
            if (els.length) {
                let filters = Array.from(els).map(el => el.getAttribute('name'));
                callback(filters);
                return; // langsung selesai
            }
        
            // Kalau belum ada, baru observe
            let observer = new MutationObserver(function(mutations, obs) {
                let els = target.querySelectorAll('select.admin__control-select');
                if (els.length) {
                    let filters = Array.from(els).map(el => el.getAttribute('name'));
                    callback(filters);
                    obs.disconnect(); // stop observing setelah ketemu
                }
            });
        
            observer.observe(target, {
                childList: true,
                subtree: true
            });
        },
        uploadImage: async function(imgUrl) {
            imgUrl = imgUrl.replace("/.thumbs","");

            $('body').trigger('processStart');
            
            try {
                urlBuilder.setBaseUrl(BASE_URL);
                var url = urlBuilder.build("marketplace/catalog/imageproxy/index");
                
                let response = await fetch(
                  url + '?url=' + encodeURIComponent(imgUrl)
                );
                
                let blob = await response.blob();
                let filename = imgUrl.split('/').pop();
                
                let file = new File([blob], filename, {
                    type: blob.type
                });
        
                $(document).find('input[type=file]').fileupload('send', {files: [file]});
                return true;
            } catch (e) {
                return false; // jangan lempar error
            }

            $('body').trigger('processStop');
        },

        uploadSingleImage: async function(imgUrl) {
            return new Promise((resolve) => {
                try {
                    const url = urlBuilder.build("marketplace/catalog/imageproxy/index");
        
                    fetch(url + '?url=' + encodeURIComponent(imgUrl))
                        .then(res => res.blob())
                        .then(blob => {
                            const file = new File([blob], imgUrl.split('/').pop(), {
                                type: blob.type
                            });
        
                            $(document)
                                .find('input[type=file]')
                                .fileupload('send', {
                                    files: [file],
                                    done: () => resolve(true),
                                    fail: () => resolve(false) // ⬅️ JANGAN reject
                                });
                        })
                        .catch(() => resolve(false));
                } catch (e) {
                    resolve(false);
                }
            });
        },

        uploadImagesBatch: function(imageUrls, maxConcurrent = 5) {
            if (!imageUrls.length) {
                return true; // ⬅️ PENTING
            }
        
            let index = 0;
            let running = 0;
            let completed = 0;

            const _this = this
        
            return new Promise((resolve) => {
        
                function next() {
                    // ✅ SEMUA SELESAI
                    if (completed === imageUrls.length) {
                        resolve(true);
                        return;
                    }
        
                    while (running < maxConcurrent && index < imageUrls.length) {
                        const url = imageUrls[index++];
                        running++;
        
                        _this.uploadSingleImage(url)
                            .catch(err => {
                                console.error('Upload failed:', url, err);
                            })
                            .finally(() => {
                                running--;
                                completed++;
                                next(); // ⬅️ LANJUTKAN
                            });
                    }
                }
        
                next();
            });
        },

        convertToSlug: function(Text) {
            return Text.toLowerCase()
                .replace(/ /g, "-")
                .replace(/[^\w-]+/g, "");
        },

        checkURL: function(url) {
            return (url.match(/\.(jpeg|jpg|gif|png)$/) != null);
        },

        getLabelByValue: function(data, value) {
            const item = data.find(entry => entry.value === value);
            return item ? item.label : null;
        },

        getOptionLabel: function(attrCode, value) {
            var attributeField = uiRegistry.get(
                'product_form.product_form.category-details.container_' + attrCode + '.' + attrCode
            );

            if (!attributeField) {
                var attributeField = uiRegistry.get(
                    'product_form.product_form.product-details.container_' + attrCode + '.' + attrCode
                );
            }

            if (attributeField) {
                var options = attributeField.options._latestValue;
                
                return this.getLabelByValue(options, value);
            }

            return null;
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

        getProductSource: function() {
            return uiRegistry.get('product_form.product_form_data_source');
        }
    };
});
