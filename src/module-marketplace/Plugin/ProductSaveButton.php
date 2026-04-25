<?php

namespace TCGCollective\MarketPlace\Plugin;

class ProductSaveButton 
{
    public function afterGetButtonData(
        \Magento\ConfigurableProduct\Block\Adminhtml\Product\Edit\Button\Save $subject,
        $result
    ) {
        // dd($result);
        // Return an empty array to remove all options
        // $result['options'] = [];
        // unset($result['class_name']);
        // unset($result['dropdown_button_aria_label']);
        // dd($result);
        return $result;
    }
}
