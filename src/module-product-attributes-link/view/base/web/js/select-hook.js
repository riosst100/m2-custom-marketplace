define([
    'jquery',
    'uiRegistry',
    'mage/url'
], function ($, uiRegistry, urlBuilder) {
    'use strict';

    return function (Select) {
        return Select.extend({
            /**
             * Callback that fires when 'value' property is updated.
             */
            onUpdate: function () {
                this._super();

                var code = this.code;

                if (code == "pre_order_payment_type") {
                    var preOrderDepositField = uiRegistry.get('product_form.product_form.pre-orders.container_pre_order_deposit.pre_order_deposit');
                    if (preOrderDepositField) {
                        var productSource = uiRegistry.get('product_form.product_form_data_source');
                        if (productSource) {
                            var preOrderPaymentType = productSource ? productSource.data.product['pre_order_payment_type'] : '';
                            if (preOrderPaymentType) {
                                if (preOrderPaymentType == "deposit") {
                                    preOrderDepositField.visible(true);
                                } else {
                                    preOrderDepositField.visible(false);
                                }
                            }
                        }
                    }
                }

                var option_id = this.value._latestValue
                if (option_id) {

                    // Attribute set mapping
                    var mappingFields = JSON.parse(ATTRIBUTE_SET_MAPPING_FIELDS);
                    if($.inArray(code, mappingFields) !== -1) {
                         var attributeSetField = uiRegistry.get('product_form.product_form.product-details.attribute_set_id');
                        var attribute_set_id = attributeSetField.value._latestValue;
        
                        // var attributeSetField = uiRegistry.get('product_form.product_form.product-details.attribute_set_id');
                        var attributeSetField = uiRegistry.get('product_form.product_form.product-details.attribute_set_id');
                        // attributeSetField.value('');
        
                        
                        var categoryIdsField = uiRegistry.get('product_form.product_form.product-details.container_category_ids.category_ids');
                        var category_id = categoryIdsField.value._latestValue;
                       
                        var mappingGroupIdField = uiRegistry.get('product_form.product_form.product-details.mapping_group_name');
                        var mapping_group_name = mappingGroupIdField.value;
                        var mappingPriorityField = uiRegistry.get('product_form.product_form.product-details.mapping_priority');
                        var mapping_priority = mappingPriorityField.value;
                        this.getAttributesMapping(option_id, category_id, attribute_set_id, code, mapping_group_name, mapping_priority);
                    }
                }

                // Attribute option mapping
                // var cardGamesMappingFields = JSON.parse(CARD_GAMES_MAPPING_FIELDS);
                // if($.inArray(code, cardGamesMappingFields) !== -1) {
                //     console.log('GAME23');
                //     this.getAttributeOptionsMapping(code, option_id);
                // }
            },
            getAttributeOptionsMapping: function (code, option_id) {
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
                    success: function (resp) {
                        if (resp.data) {
                            var mappingData = resp.data;
                            
                            console.log(mappingData)

                            if (mappingData) {
                                $.each(mappingData, function(code, options) {
                                    var field = $("select[name='product[" + code + "]']");
                                    if (field) {
                                        field.find('option').remove().end();
                                        if (options) {
                                            if (options.length > 1) {
                                                field.append(new Option(' ', ''));
                                            }
                                    
                                            $.each(options, function(index, item) {
                                                if (item) {
                                                field.append(new Option(item.label, item.value));
                                                }
                                            });
                                        }
                                    }
                                })
                            }
                        }
                    },
                    complete: function () {
                        $('body').trigger('processStop');
                    }
                });
        
                return true;
            },
            getAttributesMapping: function (option_id, category_id, attribute_set_id, code, mapping_group_name, mapping_priority) {
                $('body').trigger('processStart');

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
                        mapping_group_name: mapping_group_name,
                        mapping_priority: mapping_priority
                    },
                    dataType: 'json',
                    success: function (resp) {
                        if (resp.data) {
                            var attributeSetField = uiRegistry.get('product_form.product_form.product-details.attribute_set_id');
                            if (resp.data.attribute_set_id && resp.data.attribute_set_id != null) {
                                
                                attributeSetField.toggleOptionSelected({value: resp.data.attribute_set_id});
                            } else {
                                var attributeSetField = uiRegistry.get('product_form.product_form.product-details.attribute_set_id');
                                attributeSetField.value('');
                            }

                            if (resp.data.mapping_group_name && resp.data.mapping_group_name != null) {
                                var mappingGroupIdField = uiRegistry.get('product_form.product_form.product-details.mapping_group_name');
                                mappingGroupIdField.value = resp.data.mapping_group_name;
                            }

                            if (resp.data.mapping_priority && resp.data.mapping_priority != null) {
                                var mappingPriorityField = uiRegistry.get('product_form.product_form.product-details.mapping_priority');
                                mappingPriorityField.value = resp.data.mapping_priority;
                            }
                        }
                    },
                    complete: function () {
                        $('body').trigger('processStop');
                    }
                });

                // this.value = this.value;
        
                return true;
            }
        });
    }
});