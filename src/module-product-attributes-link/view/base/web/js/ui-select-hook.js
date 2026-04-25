define(['jquery', 'uiRegistry', 'mage/url', 'Magento_Ui/js/modal/modal','Magento_Ui/js/lib/key-codes','platformDatabaseUtils','ko'], function($, uiRegistry, urlBuilder, modal, keyCodes, platformDatabaseUtils,ko) {
    'use strict';

    var isTouchDevice = typeof document.ontouchstart !== 'undefined';

    return function(Select) {
        return Select.extend({
            defaults: {
                newOptionInputValue: '',
                newOptionPlaceholder: 'Input here...',
                ajaxCustomOptions: false,
                ajaxVariationsAttributesMappingCheck: false,
                addNewBtn: false,
                showNotes: false,
                debug: false,
                searchUrl: false,
                addNewBtnLabel: 'Add New Value',
                submitRequestText: '',
                saveBtnLabel: 'Save',
                cancelBtnLabel: 'Cancel',
                showAddNewValues: false,
                disableOutsideClick: false,
                newRequestModalTitle: 'New Request',
                filterPlaceholder: 'Search by keyword...',
                allowAddOptions: false
            },
            initialize: function () {
                this._super();

                this.populateCustomTableOptions(this);
    
                this.newRequestModalTitle("New " + this.label + " Request");
                this.addNewBtnLabel("Submit New " + this.label + " Request");
                this.submitRequestText("Can't find the " + this.label + " you looking for?");

                let isWaitingToHide = false;
                let hideTimeout = null;
                
                // Tangkap ketika request AJAX dikirim
                $(document).on('ajaxSend', function(event, xhr, settings) {
                    if (settings.url.includes('/product_gallery/upload/?isAjax=true')) {
                        // Saat ada request baru, batalkan proses hide loader
                        
                        
                        if (hideTimeout) {
                            clearTimeout(hideTimeout);
                            hideTimeout = null;
                            isWaitingToHide = false;
                        }
                    }
                });
                
                // Tangkap ketika request AJAX selesai
                $(document).on('ajaxComplete', function(event, xhr, settings) {
                    if (settings.url.includes('/product_gallery/upload/?isAjax=true')) {
                        if (!isWaitingToHide) {
                            isWaitingToHide = true;
                
                            // Tunggu 500ms, jika tidak ada request baru, stop loader
                            hideTimeout = setTimeout(() => {
                                $('body').trigger('processStop');
                                isWaitingToHide = false;
                                hideTimeout = null;
                            }, 300); // ⏱ delay-nya bisa disesuaikan (300–1000 ms)
                        } else {
                        }
                    }
                });
    
                return this;
            },
            initObservable: function () {
                this._super();
                
                this.observe([
                    'showAddNewValues',
                    'showNotes',
                    'searchUrl',
                    'searchOptions',
                    'disableOutsideClick',
                    'newOptionInputValue',
                    'newOptionPlaceholder',
                    'addNewBtnLabel',
                    'debug',
                    'submitRequestText',
                    'newRequestModalTitle',
                    'isScrollEnd',
                    'categoryLabel',
                    'allowAddOptions'
                ]);

                var gameOrBrandsRequestFormAttributeCodesArr = window.GAME_OR_BRANDS_REQUEST_FORM_ATTRIBUTE_CODES;
                if (gameOrBrandsRequestFormAttributeCodesArr && gameOrBrandsRequestFormAttributeCodesArr.length) {
                    if (gameOrBrandsRequestFormAttributeCodesArr.includes(this.code)) {
                        this.allowAddOptions(true);
                    }
                }

                this.isScrollEnd(false);

                // ambil label dari Magento (ini penting)
                this.categoryLabel = (this.label || 'item').toLowerCase();

                // detect no result
                this.isNoResult = ko.computed(() => {
                    return this.filterInputValue() && this.options().length === 0;
                });
                
                // control tombol muncul
                this.showRequestButton = ko.computed(() => {
                    return this.allowAddOptions() && (this.isNoResult() || this.isScrollEnd() || this.filterInputValue());
                });
                
                // dynamic CTA label
                this.getDynamicRequestLabel = ko.computed(() => {
                    const label = this.categoryLabel || 'item';
                    const input = this.filterInputValue();
                    if (input) {
                        return `Request to add "${input}"`;
                    }

                    return `Request new ${label}`;
                });

                // empty message
                this.emptyMessage = ko.computed(() => {
                    const input = this.filterInputValue();
                    if (input) {
                        return `No results for "${input}"`;
                    } else {
                        return 'No options found.';
                    }
                });

                // reset scroll kalau search berubah
                this.filterInputValue.subscribe(() => {
                    // if (!this.filterInputValue) {
                    //     console.log('END FALSE')
                        this.isScrollEnd(false);
                    // }
                });
                
                return this;
            },
            onScrollDown: function (data, event) {
    const el = event.target;

    if (el.scrollTop + el.clientHeight >= el.scrollHeight - 5) {
        this.isScrollEnd(true);
    } else {
        this.isScrollEnd(false);
    }
},
            outerClick: function () {
                if (this.disableOutsideClick()) {
                    this.disableOutsideClick(false);
                    return false;
                }

                this.showAddNewValues(false);

                this.newOptionInputValue('');
                
                this.listVisible() ? this.listVisible(false) : false;
                
                if (isTouchDevice) {
                    this.multiselectFocus(false);
                }
            },
            handleSaveNewValues: function() {
                this.showAddNewValues(true);
                this.disableOutsideClick(true);

                var _this = this;

                var value = this.newOptionInputValue().trim();

                urlBuilder.setBaseUrl(BASE_URL);
                var url = urlBuilder.build("marketplace/productattributeslink/product/saveNewAttributeOptions");

                $.ajax({
                    url: url,
                    data: {
                        form_key: window.FORM_KEY,
                        code: _this.code,
                        val: value
                    },
                    showLoader: true,
                    dataType: 'json',
                    success: function(resp) {
                        if (resp.data) {
                            if (resp.data.options) {
                                _this.newOptionInputValue('');
                                
                                _this.listVisible(false);
                                _this.showAddNewValues(false);

                                var options = resp.data.options;
    
                                _this.options(options);
                                _this.cacheOptions.plain = options;
                                _this.cacheOptions.tree = options;

                                if (resp.data.selected_option) {
                                    var selectedOption = resp.data.selected_option;
                                    if (selectedOption) {
                                        _this.toggleOptionSelected({
                                            value: selectedOption
                                        });
                                    }
                                }
                            }
                        }
                    }
                });

                return this;
            },
            handleAddNewValuesClick: function () {
                this.listVisible(false);
                this.showAddNewValues(false);
                // this.showAddNewValues(true);

                // this.disableOutsideClick(true);
                var categoryIdsField = uiRegistry.get('product_form.product_form.category-details.container_category_ids.category_ids');
                var categoryId = categoryIdsField.value._latestValue;

                var _this = this;

                urlBuilder.setBaseUrl(BASE_URL);
                var url = urlBuilder.build("marketplace/productattributeslink/product/getNewRequestForm");

                $.ajax({
                    url: url,
                    data: {
                        form_key: window.FORM_KEY,
                        attribute_code: _this.code,
                        category_id: categoryId instanceof Array ? categoryId[0] : categoryId
                    },
                    showLoader: true,
                    dataType: 'json',
                    success: function(resp) {
                        if (resp.result) {
                            var html = resp.result
                            .replace(/\\"/g, '"')
                            .replace(/\\n/g, '\n')
                            .replace(/\\r/g, '')
                            .replace(/\\/g, '');
                            
                            var previewPopup = $('<div id="new-request-form-modal"></div>').html(html);
                            previewPopup.modal({
                                title: _this.newRequestModalTitle(),
                                innerScroll: true,
                                modalClass: 'new-request-form-modal',
                                buttons: []
                            }).trigger('openModal');
                        }
                    }
                });

                return this;
            },
            handleAddNewValuesClose: function () {
                this.showAddNewValues(false);

                this.disableOutsideClick(true);

                return this;
            },
            hideAllCustomTableSelection: function() {
                var scopes = [
                    'nonsportcard_sets',
                    'actiontoy_sets',
                    'anime_sets',
                    'legosets_sets',
                    'livingcardgame_sets',
                    'miniaturegame_sets', 
                    'sportcard_sets',
                    'cardgame_sets',
                    'nonsportcard_sets'
                ];
                
                scopes.forEach((scope) => {
                    var customTableSelection = uiRegistry.get('product_form.product_form.' + scope);
                    if (customTableSelection) {
                        customTableSelection.visible(false)
                    }
                });
            },
            variationsAttributesMapping: function(field) {

                $('body').trigger('processStart');

                var productSource = uiRegistry.get('product_form.product_form_data_source');
                var product = productSource ? productSource.data.product : '';
                
                urlBuilder.setBaseUrl(BASE_URL);
                var url = urlBuilder.build("marketplace/productattributeslink/product/checkVariationsAttributesMapping");

                if (this.ajaxVariationsAttributesMappingCheck) {
                    this.ajaxVariationsAttributesMappingCheck.abort();
                }
                
                this.ajaxVariationsAttributesMappingCheck = $.ajax({
                    url: url,
                    data: {
                        form_key: window.FORM_KEY,
                        product: product
                    },
                    dataType: 'json',
                    error: function(jqXHR, textStatus, errorThrown){
                        if (jqXHR.aborted)
                            return;
                    },
                    success: function(resp) {
                        if (resp.data) {
                            var data = resp.data;
                            if (data) {
                                var configurableFieldset = uiRegistry.get("product_form.product_form.configurable");
                                if (configurableFieldset) {
                                    var isVariationsAttributesExist = data.is_variations_attributes_exist;
                                    if (isVariationsAttributesExist) {
                                        configurableFieldset.visible(true)

                                        var noVariationsFieldset = uiRegistry.get("product_form.product_form.no_variations");
                                        if (noVariationsFieldset) {
                                            noVariationsFieldset.visible(false)
                                        }

                                        platformDatabaseUtils.toggleVisibleProductDetails(true);
                                    } else {
                                        if (product.mapping_priority >= 3) {
                                            configurableFieldset.visible(false)
    
                                            var noVariationsFieldset = uiRegistry.get("product_form.product_form.no_variations");
                                            if (noVariationsFieldset) {
                                                noVariationsFieldset.visible(true)
                                            }

                                            platformDatabaseUtils.toggleVisibleProductDetails(false);
                                        }
                                    }
                                }
                            }
                        }
                    },
                    complete: function() {
                        $('body').trigger('processStop');
                    }
                });
            },
            getLabelByValue: function(data, value) {
                const item = data.find(entry => entry.value === value);
                return item ? item.label : null;
            },
            toggleOptionSelected: function(data) {
                var code = this.code;
                
                if (data.disabled_select && data.disabled_select == true && data.optgroup.length) {
                    this.openChildLevel(data);

                    return false;
                }

                if (this.code == "category_ids" || window.MAIN_ATTR_CODES && window.MAIN_ATTR_CODES.includes(code)) {
                    var categoryDetailFieldSet = uiRegistry.get('product_form.product_form.category-details');
                    if (categoryDetailFieldSet) {
                        categoryDetailFieldSet._elems.forEach((element) => {
                            var index = element.index.replace('container_', '');
                            if (index != "category_ids" && (!window.MAIN_ATTR_CODES || window.MAIN_ATTR_CODES && !window.MAIN_ATTR_CODES.includes(index))) {
                                element._elems.forEach((field) => {
                                    const options = [];
                                    
                                    field.options(options);
                                    field.cacheOptions.plain = options;
                                    field.cacheOptions.tree = options;
                                    field.isDisplayMissingValuePlaceholder = false;
                                    field.reset()
                                })
                            }
                        });
                    }
                }

                this._super();
                
                const url = window.location.href;

                // Handle Custom Table Selection
                const source = url.split('/source/')[1]?.split('/')[0];
                
                if (source && source == "database" || this.debug()) {
                    platformDatabaseUtils.checkCustomTableExists(this);
                }

                // Check Variations Attributes Mapping
                
                const productType = url.split('/type/')[1]?.split('/')[0];
                if (productType == "configurable") {
                    this.variationsAttributesMapping(this);
                }

                if (code == "websites") {
                    var priceField = uiRegistry.get('product_form.product_form.product-details.price');
                    // var priceFieldRecord = uiRegistry.get('product_form.product_form.product-details.price.record');

                    var websiteIds = this.value._latestValue;

                    var recordData = priceField.recordData._latestValue;

                    $.each(websiteIds, function(index, websiteId) {
                        // if (!recordData.includes(websiteId)) {
                        if (!recordData.some(e => e.website_id === websiteId)) {
                            
                            // var newPriceField = uiRegistry.get('product_form.product_form.product-details.price');
                            var newPriceField = priceField.addChild();
                            if (newPriceField) {
                                var records = newPriceField.elems._latestValue;
                                if (records) {
                                    var lastRecord = records[records.length-1];
                                    if (lastRecord) {
                                        var recordFields = lastRecord.elems._latestValue;
                                        if (recordFields) {
                                            var websiteField = recordFields[0];
                                            if (websiteField) {
                                                websiteField.value(websiteId);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    });

                    if (recordData.length) {
                        $.each(recordData, function(index, data) {
                            if (!websiteIds.includes(data.website_id)) {
                                priceField.setVisible(false, data.index);
                            }
                        });
                    }
                }

                // ADMIN - SETS & CARD - SINGLES FORM
                if (this.ns == "productattributeslink_cardgames_card_form") {
                    this.singlesForm(this);
                }

                if (this.ns == "productattributeslink_cardgames_sealedproducts_form") {
                    this.sealedProductsForm(this);
                }

                // Handle form
                if (this.ns == "import_release_form" || this.ns == "import_drop_form" || this.ns == "import_sets_form" || this.ns == "import_singles_form" || this.ns == "import_sealedproducts_form") {
                    this.importSetsForm(this);
                }

                if (this.ns == "import_sealedproducts_form" || this.ns == "import_singles_form") {
                    this.importSinglesForm(this);
                }

                var option_id = this.value._latestValue;

                var inputName = this.inputName;
                if (inputName && inputName == "attribute_code" || inputName && inputName == "target_attribute_code") {
                    this.populateAttributeOptions(this.value._latestValue, inputName, this.parentName, true);
                }

                // Lego Sets
                // var legoSetsFields = JSON.parse(LEGO_SETS_FIELDS);
                // if ($.inArray(code, legoSetsFields) !== -1) {
                //     this.getAttributeOptionsMapping(code, option_id);
                // }

                // Attribute option mapping
                // var cardGamesMappingFields = JSON.parse(CARD_GAMES_MAPPING_FIELDS);
                // if ($.inArray(code, cardGamesMappingFields) !== -1) {
                //     this.getAttributeOptionsMapping(code, option_id);
                // }

                var mappingFields = JSON.parse(ATTRIBUTE_SET_MAPPING_FIELDS);
                if ($.inArray(code, mappingFields) !== -1) {
                    var attributeSetField = uiRegistry.get('product_form.product_form.product-details.attribute_set_id');
                    var attribute_set_id = attributeSetField.value._latestValue;

                    // var attributeSetField = uiRegistry.get('product_form.product_form.product-details.attribute_set_id');
                    var attributeSetField = uiRegistry.get('product_form.product_form.product-details.attribute_set_id');
                    // attributeSetField.value('');

                    var categoryIdsField = uiRegistry.get('product_form.product_form.category-details.container_category_ids.category_ids');
                    var category_id = categoryIdsField.value._latestValue;

                    var mappingGroupIdField = uiRegistry.get('product_form.product_form.product-details.mapping_group_name');
                    var mapping_group_name = mappingGroupIdField.value;
                    var mappingPriorityField = uiRegistry.get('product_form.product_form.product-details.mapping_priority');
                    var mapping_priority = mappingPriorityField.value;
                    var sourceField = uiRegistry.get('product_form.product_form.product-details.container_source.source');
                    var sourceData = sourceField.value;
                    this.getAttributesMapping(option_id, category_id, attribute_set_id, code, mapping_group_name, mapping_priority, sourceData);
                }

                if (this.code == "category_ids") {
                    var category_id = this.value._latestValue;
                    var mappingGroupIdField = uiRegistry.get('product_form.product_form.product-details.mapping_group_name');
                    var mapping_group_name = mappingGroupIdField.value;
                    var mappingPriorityField = uiRegistry.get('product_form.product_form.product-details.mapping_priority');
                    var mapping_priority = mappingPriorityField.value;

                    this.getCategoryAttributeSetLink(category_id, mapping_group_name, mapping_priority);
                }
// 
                // this.populateCustomTableOptions(this);
            },
            setCaption: function() {
                var length, caption = '';

                if (!_.isArray(this.value()) && this.value()) {
                    length = 1;
                } else if (this.value()) {
                    length = this.value().length;
                } else {
                    this.value([]);
                    length = 0;
                }
                this.warn(caption);

                //check if option was removed
                if (this.isDisplayMissingValuePlaceholder && length && !this.getSelected().length) {
                    caption = this.missingValuePlaceholder.replace('%s', this.value());
                    this.placeholder(caption);
                    this.warn(caption);

                    return this.placeholder();
                }

                if (length > 1 && this.multiple) {
                    this.placeholder(length + ' ' + this.selectedPlaceholders.lotPlaceholders);
                } else if (length && this.getSelected().length) {
                    this.placeholder(this.getSelected()[0].label);
                } else {
                    this.placeholder(this.selectedPlaceholders.defaultPlaceholder);
                }

                return this.placeholder();
            },
            getSelected: function() {
                var selected = this.value();
                var inputName = this.inputName;

                if (this.ns == "productattributeslink_cardgames_card_form") {
                    this.singlesForm(this);
                }

                if (this.ns == "productattributeslink_cardgames_sealedproducts_form") {
                    this.sealedProductsForm(this);
                }

                // Handle form
                if (this.ns == "download_sample_csv_form" || this.ns == "import_sets_form" || this.ns == "import_singles_form") {
                    if (inputName != "custom_table_type") {
                        this.importSetsForm(this);
                    }
                }

                if (inputName && inputName == "attribute_code" || inputName && inputName == "target_attribute_code") {
                    this.populateAttributeOptions(this.value._latestValue, inputName, this.parentName, false);
                }

                if (inputName.includes('card_set')) {
                    // this.populateCustomTableOptions(this);
                }

                return this.cacheOptions.plain.filter(function(opt) {
                    return _.isArray(selected) ? _.contains(selected, opt.value) : selected == opt.value;
                    //eslint-disable-line eqeqeq
                });
            },
            populateSetsOptions: function(inputName, inputVal, parentName, currentField, isEdit=false) {
                $('body').trigger('processStart');
                
                $.ajax({
                    url: '/backoffice/productattributeslink/form/getSetsOptions',
                    data: {
                        form_key: window.FORM_KEY,
                        code: inputName,
                        val: inputVal
                    },
                    dataType: 'json',
                    success: function(resp) {
                        if (resp.data) {
                            var options = resp.data;

                            // if (options) {
                            //     if (inputName == "attribute_code") {
                            var field = uiRegistry.get(parentName + ".set_id");
                            //     } else {
                            //         var field = uiRegistry.get(parentName + ".target_attribute_options");
                            //     }
                            if (field) {
                                field.options(options);
                                field.cacheOptions.plain = options;
                                field.cacheOptions.tree = options;
                                field.isDisplayMissingValuePlaceholder = false;

                                if (field.multiple) {
                                    field.toggleOptionSelected(field.value());
                                }

                                if (!options.length) {
                                    currentField.error('No sets found. Please upload the sets first.')
                                    field.disabled(true)
                                } else {
                                    field.disabled(false)
                                }
                            }

                            if (field && isEdit) {
                                field.disabled(true)
                            }
                            // } 
                        }
                    },
                    complete: function() {
                        $('body').trigger('processStop');
                    }
                });
            },
            toggleFieldsetVisible: function(namespace, fieldsetName, isVisible) {
                var fieldset = uiRegistry.get(namespace + '.' + namespace + '.' + fieldsetName);
                if (fieldset) {
                    fieldset.visible(isVisible)
                }
            },
            importSinglesForm: function(field) {
                var inputName = field.inputName;
                var inputVal = field.value._latestValue;
                var parentName = field.parentName;
                var namespace = field.ns;

                if (inputName) {

                    // Main Categories Field
                    if (inputName == "dolls_toy_line" || inputName == "toys_line" || inputName == "sc_set_type" || inputName == "release_year" || inputName == "categories" || inputName == "release" || inputName == "toy_line") {

                        var categories = '';

                        // Hide field
                        if (inputName == "categories") {
                            var releaseYearField = uiRegistry.get(parentName + ".release_year");
                            if (releaseYearField) {
                                releaseYearField.value('');
                                releaseYearField.visible(false);
                            }
                            var releaseField = uiRegistry.get(parentName + ".release");
                            if (releaseField) {
                                releaseField.visible(false);
                                releaseField.value('');

                            }

                            categories = inputVal;
                        }

                        var release = '';

                        if (inputName == "release") {
                            var releaseField = uiRegistry.get(parentName + ".release");
                            if (releaseField) {
                                release = releaseField.value._latestValue;
                            }
                            var categoriesField = uiRegistry.get(parentName + ".categories");
                            if (categoriesField) {
                                categories = categoriesField.value._latestValue;
                            }
                        }

                        var release_year = '';

                        if (inputName == "release_year") {
                            var releaseField = uiRegistry.get(parentName + ".release_year");
                            if (releaseField) {
                                release_year = releaseField.value._latestValue;
                            }
                            var categoriesField = uiRegistry.get(parentName + ".categories");
                            if (categoriesField) {
                                categories = categoriesField.value._latestValue;
                            }
                        }

                         var sc_set_type = '';

                        if (inputName == "sc_set_type") {
                            var releaseField = uiRegistry.get(parentName + ".sc_set_type");
                            if (releaseField) {
                                sc_set_type = releaseField.value._latestValue;
                            }
                            var categoriesField = uiRegistry.get(parentName + ".categories");
                            if (categoriesField) {
                                categories = categoriesField.value._latestValue;
                            }
                        }

                        var toy_line = '';

                        if (inputName == "toys_line") {
                            var toyLineField = uiRegistry.get(parentName + ".toys_line");
                            if (toyLineField) {
                                toy_line = toyLineField.value._latestValue;
                            }
                            var categoriesField = uiRegistry.get(parentName + ".categories");
                            if (categoriesField) {
                                categories = categoriesField.value._latestValue;
                            }
                        }

                        var dolls_toy_line = '';

                        if (inputName == "dolls_toy_line") {
                            var toyLineField = uiRegistry.get(parentName + ".dolls_toy_line");
                            if (toyLineField) {
                                dolls_toy_line = toyLineField.value._latestValue;
                            }
                            var categoriesField = uiRegistry.get(parentName + ".categories");
                            if (categoriesField) {
                                categories = categoriesField.value._latestValue;
                            }
                        }

                        var entity = '';
                        var entityField = uiRegistry.get(parentName + ".category_id");
                        if (entityField) {
                            entity = entityField.value._latestValue;
                        }
                        $.ajax({
                            url: '/backoffice/productattributeslink/import/getSetsData',
                            data: {
                                form_key: window.FORM_KEY,
                                entity: entity,
                                categories: categories,
                                release: release,
                                release_year: release_year,
                                sc_set_type: sc_set_type,
                                toys_line: toy_line,
                                dolls_toy_line: dolls_toy_line
                            },
                            showLoader: true,
                            dataType: 'json',
                            success: function(resp) {
                                if (resp.data.length) {
                                    var attributesData = resp.data;
                                    if (attributesData) {
                                        $.each(attributesData, function(index, data) {
                                            var options = data.options;
                                            var attribute = data.attribute;
                                            var code = attribute.code;
                                            var label = attribute.label;

                                            $("div[data-index='" + code + "'] .admin__field-label span").text(label);

                                            var field = uiRegistry.get(parentName + "." + code);
                                            if (field) {
                                                // field.filterInputValue('');
                                                field.isDisplayMissingValuePlaceholder = false;
                                                if (!field.value && field.multiple) {
                                                    field.value(['']);
                                                }

                                                field.renderPath = false;

                                                field.options(options);
                                                field.cacheOptions.plain = options;
                                                field.cacheOptions.tree = options;
                                                field.isDisplayMissingValuePlaceholder = false;
                                                field.visible(true);
                                            }
                                        })
                                    }
                                }
                            },
                            complete: function() {
                                $('body').trigger('processStop');
                            }
                        });
                    }

                    // GET RELEASE DATA
                    if (inputName == "toy_brand") {
                        // Hide field
                        var releaseField = uiRegistry.get(parentName + ".release");
                        if (releaseField) {
                            releaseField.visible(false);
                            releaseField.value('');
                        }

                        var entity = '';
                        var entityField = uiRegistry.get(parentName + ".category_id");
                        if (entityField) {
                            entity = entityField.value._latestValue;
                        }
                        var categories = '';
                        var categoriesField = uiRegistry.get(parentName + ".categories");
                        if (categoriesField) {
                            categories = categoriesField.value._latestValue;
                        }
                        $.ajax({
                            url: '/backoffice/productattributeslink/import/getReleaseData',
                            data: {
                                form_key: window.FORM_KEY,
                                entity: entity,
                                categories: categories,
                                release_year: inputVal,
                                toy_brand: inputVal
                            },
                            showLoader: true,
                            dataType: 'json',
                            success: function(resp) {
                                if (resp && resp.data.length) {
                                    var attributesData = resp.data;
                                    if (attributesData) {
                                        $.each(attributesData, function(index, data) {
                                            var options = data.options;
                                            var attribute = data.attribute;
                                            var code = attribute.code;
                                            var label = attribute.label;

                                            $("div[data-index='" + code + "'] .admin__field-label span").text(label);

                                            var field = uiRegistry.get(parentName + "." + code);
                                            if (field) {
                                                // field.filterInputValue('');
                                                field.isDisplayMissingValuePlaceholder = false;
                                                if (!field.value && field.multiple) {
                                                    field.value(['']);
                                                }

                                                field.renderPath = false;

                                                field.options(options);
                                                field.cacheOptions.plain = options;
                                                field.cacheOptions.tree = options;
                                                field.isDisplayMissingValuePlaceholder = false;
                                                field.visible(true);
                                            }
                                        })
                                    }
                                }
                            },
                            complete: function() {
                                $('body').trigger('processStop');
                            }
                        });
                    }
                }
            },
            importSetsForm: function(field) {
                var inputName = field.inputName;
                var inputVal = field.value._latestValue;
                var parentName = field.parentName;
                var namespace = field.ns;

                if (inputName) {

                    // Categories Field
                    if (inputName == "category_id") {
                        // Hide field
                        var releaseYearField = uiRegistry.get(parentName + ".release_year");
                        if (releaseYearField) {
                            releaseYearField.visible(false);
                            releaseYearField.value('');
                        }
                        var releaseField = uiRegistry.get(parentName + ".release");
                        if (releaseField) {
                            releaseField.visible(false);
                            releaseField.value('');
                        }
                        var categoriesField = uiRegistry.get(parentName + ".categories");
                        if (categoriesField) {
                            categoriesField.visible(false);
                            categoriesField.value('');
                        }
                        var setsField = uiRegistry.get(parentName + ".set_id");
                        if (setsField) {
                            setsField.visible(false);
                            setsField.value('');
                        }

                        $.ajax({
                            url: '/backoffice/productattributeslink/import/getEntityData',
                            data: {
                                form_key: window.FORM_KEY,
                                entity: inputVal
                            },
                            showLoader: true,
                            dataType: 'json',
                            success: function(resp) {
                                if (resp.data.length) {
                                    var mappingData = resp.data;
                                    if (mappingData) {
                                        var categoriesField = uiRegistry.get(parentName + ".categories");
                                        if (categoriesField) {
                                            categoriesField.options(mappingData);
                                            categoriesField.cacheOptions.plain = mappingData;
                                            categoriesField.cacheOptions.tree = mappingData;
                                            categoriesField.isDisplayMissingValuePlaceholder = false;
                                            $("div[data-index='categories'] .admin__field-label span").text(resp.attribute.label);
                                            categoriesField.visible(true);
                                        }
                                    }
                                } else {
                                    // alert('No Attribute set mapping found for this categories.');
                                }
                            },
                            complete: function() {
                                $('body').trigger('processStop');
                            }
                        });
                    }
                }
            },
            sealedProductsForm: function(field) {
                // var inputName = field.inputName;
                // var inputVal = field.value._latestValue;
                // var parentName = field.parentName;
                // var namespace = field.ns;

                // var fieldset = uiRegistry.get(namespace + '.' + namespace);

                // var entityId = fieldset.source.data.entity_id;

                // if (inputName) {
                //     if (inputName == "card_game") {
                //         this.populateSetsOptions(inputName, inputVal, parentName, field, entityId ? true : false);
                //         this.toggleFieldsetVisible(namespace, "sealedproducts_section", false);
                //         if (entityId) {
                //             field.disabled(true)
                //         }
                //     }

                //     if (inputName == "set_id") {
                //         this.toggleFieldsetVisible(namespace, "sealedproducts_section", inputVal ? true : false);
                //     }
                // }
            },
            singlesForm: function(field) {
                var inputName = field.inputName;
                var inputVal = field.value._latestValue;
                var parentName = field.parentName;
                var namespace = field.ns;

                var fieldset = uiRegistry.get(namespace + '.' + namespace);

                var entityId = fieldset.source.data.entity_id;

                if (inputName) {
                    if (inputName == "card_game") {
                        this.populateSetsOptions(inputName, inputVal, parentName, field, entityId ? true : false);
                        this.toggleFieldsetVisible(namespace, "singles_section", false);
                        if (entityId) {
                            field.disabled(true)
                        }
                    }

                    if (inputName == "set_id") {
                        this.toggleFieldsetVisible(namespace, "singles_section", inputVal ? true : false);
                    }
                }
            },
            /**
             * Filtered options list by value from filter options list
             */
            filterOptionsList: function () {
                var value = this.filterInputValue().trim().toLowerCase(),
                    array = [];
    
                if (this.searchOptions()) {
                    return this.loadOptions(value);
                }
    
                this.cleanHoveredElement();
    
                if (!value) {
                    this.renderPath = false;
                    this.options(this.cacheOptions.tree);
                    this._setItemsQuantity(false);
    
                    return false;
                }
    
                this.showPath ? this.renderPath = true : false;
    
                if (this.filterInputValue()) {
    
                    array = this.selectType === 'optgroup' ?
                        this._getFilteredArray(this.cacheOptions.lastOptions, value) :
                        this._getFilteredArray(this.cacheOptions.plain, value);
    
                    if (!value.length) {
                        this.options(this.cacheOptions.plain);
                        this._setItemsQuantity(this.cacheOptions.plain.length);
                    } else {
                        this.options(array);
                        this._setItemsQuantity(array.length);
                    }
    
                    return false;
                }
    
                this.options(this.cacheOptions.plain);
            },
            populateCustomTableOptions: function(field) {
                if (field.code) {
                    var namespace = field.ns;
                    var fieldset = uiRegistry.get(namespace + '.' + namespace);
                    if (fieldset) {
                        var data = fieldset.source.data;
    
                        var code = field.code;
    
                        urlBuilder.setBaseUrl(BASE_URL);
                        var url = urlBuilder.build("marketplace/productattributeslink/mappingdata/getCustomTableOptions");
                        if (data) {
                            // if(!this.ajaxCustomOptions) {
                                if (window.ajaxCustomOptions) {
                                    window.ajaxCustomOptions.abort();
                                }
                                window.ajaxCustomOptions = $.ajax({
                                    url: url,
                                    showLoader: false,
                                    data: {
                                        form_key: window.FORM_KEY,
                                        // attribute: {
                                        //     code: field.code,
                                        //     value: field.value._latestValue
                                        // },
                                        data: data
                                    },
                                    dataType: 'json',
                                    error: function (jqXHR, textStatus, errorthrown) {
                                        if (jqXHR.readyState == 0) {
                                           //Network error, i.e.  server stopped, timeout, connection refused, CORS, etc.
                                        }
                                        else if (jqXHR.readyState == 4) {
                                           //HTTP error, i.e. 404 Not found, Internal Server 500, etc.
                                        }
                                    },
                                    success: function(resp) {
                                        if (resp.data) {
                                            if (resp.data.length) {
                                                var attributesData = resp.data;
                                                if (attributesData) {
                                                    $.each(attributesData, function(index, data) {
                                                        var options = data.options;
                                                        var attribute = data.attribute;
                                                        var code = attribute.code;
        
                                                        var field = uiRegistry.get("product_form.product_form.product-details.container_" + code + "." + code);
                                                        if (field) {
                                                            // if (!options.some(item => item.value === field.value._latestValue)) {
                                                            //     field.value('');
                                                            // }
                                                            
                                                            // field.isDisplayMissingValuePlaceholder = false;
                                                            // if (!field.value && field.multiple) {
                                                            //     field.value(['']);
                                                            // }
        
                                                            // field.renderPath = false;
        
                                                            // field.options(options);
                                                            // field.cacheOptions.plain = options;
                                                            // field.cacheOptions.tree = options;
                                                            // field.visible(true);
                                                            field.showNotes(true)
                                                            field.searchOptions(true)
                                                            if (data.searchUrl) {
                                                                field.searchUrl = data.searchUrl
                                                            }
                                                        }
                                                    });
                                                }
                                            }
                                        }
                                    }
                                });
                            // }
                        }
                    }
                }
            },
            /**
             * Returns options from cache or send request
             *
             * @param {String} searchKey
             */
            loadOptions: function (searchKey) {
                // var currentPage = searchKey === this.lastSearchKey ? this.lastSearchPage + 1 : 1,
                //     cachedSearchResult;
    
                // this.renderPath = !!this.showPath;
    
                // if (searchKey && this.isSearchKeyCached(searchKey)) {
                //     cachedSearchResult = this.getCachedSearchResults(searchKey);
                //     this.cacheOptions.plain = cachedSearchResult.options;
                //     this.options(cachedSearchResult.options);
                //     this.afterLoadOptions(searchKey, cachedSearchResult.lastPage, cachedSearchResult.total);
    
                //     return;
                // }
    
                // if (currentPage === 1) {
                //     this.options([]);
                // }
                // if (typeof this.searchUrl === "string" && this.searchUrl.trim() !== "") {
                //     this.processRequest(searchKey, currentPage);
                // }
                var currentPage = searchKey === this.lastSearchKey ? this.lastSearchPage + 1 : 1,
                cachedSearchResult;

                this.renderPath = !!this.showPath;
    
                if (this.isSearchKeyCached(searchKey)) {
                    cachedSearchResult = this.getCachedSearchResults(searchKey);
                    this.cacheOptions.plain = cachedSearchResult.options;
                    this.options(cachedSearchResult.options);
                    this.afterLoadOptions(searchKey, cachedSearchResult.lastPage, cachedSearchResult.total);
    
                    return;
                }
    
                if (currentPage === 1) {
                    this.options([]);
                }

                if (typeof this.searchUrl === "string" && this.searchUrl.trim() !== "") {
                    this.processRequest(searchKey, currentPage);
                }
            },
            onFocusIn: function (ctx, event) {
                !this.cacheUiSelect ? this.cacheUiSelect = event.target : false;
                this.multiselectFocus(true);
    
                if (!this.listVisible() && typeof this.searchUrl === "string" && this.searchUrl.trim() !== "") {
                    this.options([]);
                    this.processRequest('', 1);
                }
            },
            /**
             * Submit request to load data
             *
             * @param {String} searchKey
             * @param {Number} page
             */
            processRequest: function (searchKey, page) {
                // Kalau masih loading, bisa juga dicegah (opsional)
                // if (this.loading()) return;
            
                // Cek apakah request sama dengan sebelumnya
                if (this.lastRequest &&
                    this.lastRequest.searchKey === searchKey &&
                    this.lastRequest.page === page) {
                    return;
                }
            
                // Simpan request terakhir
                this.lastRequest = { searchKey: searchKey, page: page };
            
                this.loading(true);
                this.currentSearchKey = searchKey;
            
                var selectedOption = this.value._latestValue;
            
                $.ajax({
                    url: this.searchUrl,
                    type: 'get',
                    dataType: 'json',
                    context: this,
                    data: {
                        searchKey: searchKey,
                        selectedOption: selectedOption,
                        page: page,
                        limit: this.pageLimit
                    },
                    success: $.proxy(this.success, this),
                    error: $.proxy(this.error, this),
                    beforeSend: $.proxy(this.beforeSend, this),
                    complete: $.proxy(function (xhr, status) {
                        // reset loading
                        this.loading(false);
            
                        // kamu bisa reset lastRequest kalau mau biar bisa request ulang nanti
                        // this.lastRequest = null;
                    }, this)
                });
            },
            populateAttributeOptions: function(attribute_code, inputName, parentName, changedAttribute=false) {
                // $('body').trigger('processStart');

                // urlBuilder.setBaseUrl(ADMIN_BASE_URL);
                // var url = urlBuilder.build("productattributeslink/attributeSet/getAttributeOptions");

                if (inputName == "attribute_code") {
                    var field = uiRegistry.get(parentName + ".attribute_option_id");
                } else {
                    var field = uiRegistry.get(parentName + ".target_attribute_options");
                }
                var mappingData = [];
                field.options(mappingData);
                field.cacheOptions.plain = mappingData;
                field.cacheOptions.tree = mappingData;
                field.filterInputValue('');
                field.isDisplayMissingValuePlaceholder = false;
                if (!field.value && field.multiple || changedAttribute && field.multiple) {
                    field.value(['']);
                }

                $.ajax({
                    url: '/backoffice/productattributeslink/attributeSet/getAttributeOptions',
                    data: {
                        form_key: window.FORM_KEY,
                        code: attribute_code
                    },
                    dataType: 'json',
                    showLoader: true,
                    success: function(resp) {
                        if (resp.data) {
                            var mappingData = resp.data;
                            if (mappingData) {
                                if (inputName == "attribute_code") {
                                    var field = uiRegistry.get(parentName + ".attribute_option_id");
                                } else {
                                    var field = uiRegistry.get(parentName + ".target_attribute_options");
                                }
                                if (field) {
                                    field.options(mappingData);
                                    field.cacheOptions.plain = mappingData;
                                    field.cacheOptions.tree = mappingData;
                                    field.isDisplayMissingValuePlaceholder = false;

                                    if (field.multiple) {
                                        field.toggleOptionSelected(field.value());
                                    }
                                }
                            }
                        }
                    },
                    complete: function() {// $('body').trigger('processStop');
                    }
                });
            },
            getAttributeOptionsMapping: function(code, option_id) {
                $('body').trigger('processStart');

                urlBuilder.setBaseUrl(BASE_URL);
                var url = urlBuilder.build("marketplace/productattributeslink/mappingdata/attributeOptions");

                $.ajax({
                    url: url,
                    data: {
                        form_key: window.FORM_KEY,
                        option_id: option_id,
                        code: code
                    },
                    dataType: 'json',
                    success: function(resp) {
                        if (resp.data) {
                            var mappingData = resp.data;

                            if (mappingData) {
                                $.each(mappingData, function(code, options) {
                                    var field = uiRegistry.get("product_form.product_form.product-details.container_" + code + "." + code);
                                    if (field) {
                                        if (field.formElement == "input") {
                                            field.value(options);
                                        } else {
                                            // field.filterInputValue('');
                                            field.isDisplayMissingValuePlaceholder = false;
                                            if (!field.value && field.multiple) {
                                                field.value(['']);
                                            }

                                            field.renderPath = false;

                                            field.options(options);
                                            if (field.multiple) {
                                                field.cacheOptions.plain = options;
                                                field.cacheOptions.tree = options;
                                            }
                                            field.isDisplayMissingValuePlaceholder = false;

                                            if (field.multiple) {
                                                field.toggleOptionSelected(field.value());
                                            } else if (options.length == 1) {
                                                field.value(options[0].value);
                                            }
                                        }
                                    }
                                })
                            }
                        }
                    },
                    complete: function() {
                        $('body').trigger('processStop');
                    }
                });

                return true;
            },
            getAttributesMapping: function(option_id, category_id, attribute_set_id, code, mapping_group_name, mapping_priority, source) {
                // $('body').trigger('processStart');

                urlBuilder.setBaseUrl(BASE_URL);
                var url = urlBuilder.build("marketplace/productattributeslink/mappingdata/attributes");

                $.ajax({
                    url: url,
                    data: {
                        form_key: window.FORM_KEY,
                        category_id: category_id,
                        option_id: option_id,
                        attribute_set_id: attribute_set_id,
                        code: code,
                        source: source,
                        mapping_group_name: mapping_group_name,
                        mapping_priority: mapping_priority
                    },
                    dataType: 'json',
                    success: function(resp) {
                        if (resp.data) {
                            if (resp.data.last_priority) {
                                platformDatabaseUtils.toggleVisibleProductDetails(true)
                            } else {
                                platformDatabaseUtils.toggleVisibleProductDetails(false)
                            }

                            if (resp.data.mapping_group_name && resp.data.mapping_group_name != null) {
                                var mappingGroupIdField = uiRegistry.get('product_form.product_form.product-details.mapping_group_name');
                                mappingGroupIdField.value(resp.data.mapping_group_name);
                            }

                            if (resp.data.mapping_priority && resp.data.mapping_priority != null) {
                                var mappingPriorityField = uiRegistry.get('product_form.product_form.product-details.mapping_priority');
                                mappingPriorityField.value(resp.data.mapping_priority);
                            }
                            
                            var attributeSetField = uiRegistry.get('product_form.product_form.product-details.attribute_set_id');
                            if (resp.data.attribute_set_id && resp.data.attribute_set_id != null) {
                                attributeSetField.toggleOptionSelected({
                                    value: resp.data.attribute_set_id
                                });
                            } else {
                                var attributeSetField = uiRegistry.get('product_form.product_form.product-details.attribute_set_id');
                                attributeSetField.value('');
                            }
                        }
                    },
                    complete: function() {// $('body').trigger('processStop');
                    }
                });

                return true;
            },
            getCategoryAttributeSetLink: function(category_id, mapping_group_name, mapping_priority) {

                // $('body').trigger('processStart');

                var attributeSetField = uiRegistry.get('product_form.product_form.product-details.attribute_set_id');
                attributeSetField.toggleOptionSelected({
                    value: ""
                });

                urlBuilder.setBaseUrl(BASE_URL);
                var url = urlBuilder.build("marketplace/productattributeslink/mappingdata/categories");
                $.ajax({
                    url: url,
                    data: {
                        form_key: window.FORM_KEY,
                        category_id: category_id,
                        mapping_group_name: mapping_group_name,
                        mapping_priority: mapping_priority
                    },
                    dataType: 'json',
                    success: function(resp) {
                        if (resp.data) {
                            platformDatabaseUtils.toggleVisibleProductDetails(false);
                            
                            if (resp.data.attribute_set_id) {
                                var attributeSetField = uiRegistry.get('product_form.product_form.product-details.attribute_set_id');
                                attributeSetField.toggleOptionSelected({
                                    value: resp.data.attribute_set_id
                                });
                            }

                            if (resp.data.mapping_group_name) {
                                var mappingGroupIdField = uiRegistry.get('product_form.product_form.product-details.mapping_group_name');
                                mappingGroupIdField.value(resp.data.mapping_group_name);
                            }

                            if (resp.data.mapping_priority) {
                                var mappingPriorityField = uiRegistry.get('product_form.product_form.product-details.mapping_priority');
                                mappingPriorityField.value(resp.data.mapping_priority);
                            }
                        }
                    },
                    complete: function() {// $('body').trigger('processStop');
                    }
                });

                return true;
            }
        });
    }
});
