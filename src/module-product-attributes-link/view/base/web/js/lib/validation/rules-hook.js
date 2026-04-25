define([
    'jquery',
    'mage/translate',
    'Magento_Ui/js/lib/validation/rules'
], function ($, $t, rules) {
    'use strict';

    rules['validate-store-url'] = {
        handler: function (value) {
            if ($.mage.isEmptyNoTrim(value)) {
                return true; // kosong lolos
            }

            // cari store name dari row yang sama
            var storeName = $('input[name^="product[link_to_other_stores]"][name$="[product_link]"]')
                .filter(function () {
                    return $(this).val() === value;
                })
                .closest('tr.data-row')
                .find('span[data-index="stores"]')
                .text()
                .trim()
                .toLowerCase();

            if (!storeName) {
                return true; // kalau tidak ketemu, skip validasi
            }

            var isValid = true;

            if (storeName === 'shopee') {
                isValid = /^https?:\/\/(www\.)?shopee\.[a-z.]+\/.+/.test(value.trim());
            } else if (storeName === 'lazada') {
                isValid = /^https?:\/\/(www\.)?lazada\.[a-z.]+\/.+/.test(value.trim());
            }

            return isValid;
        },
        message: $t('Please enter a valid Shopee or Lazada product link.')
    };

    return rules;
});