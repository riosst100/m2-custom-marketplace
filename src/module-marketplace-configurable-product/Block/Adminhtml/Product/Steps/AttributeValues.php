<?php

namespace TCGCollective\MarketplaceConfigurableProduct\Block\Adminhtml\Product\Steps;

/**
 * Adminhtml block for fieldset of configurable product
 *
 * @api
 * @since 100.0.2
 */
class AttributeValues extends \Magento\ConfigurableProduct\Block\Adminhtml\Product\Steps\AttributeValues
{
    /**
     * {@inheritdoc}
     */
    public function getCaption()
    {
        return __('Attribute Values');
    }
}
