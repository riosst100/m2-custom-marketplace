<?php

namespace TCGCollective\MarketplaceConfigurableProduct\Block\Adminhtml\Product\Steps;

/**
 * @api
 * @since 100.0.2
 */
class Summary extends \Magento\ConfigurableProduct\Block\Adminhtml\Product\Steps\Summary
{
    /**
     * {@inheritdoc}
     */
    public function getCaption()
    {
        return __('Summary');
    }

    /**
     * Get url to upload files
     *
     * @return string
     */
    public function getImageUploadUrl()
    {
        return $this->getUrl('catalog/product_gallery/upload');
    }
}
