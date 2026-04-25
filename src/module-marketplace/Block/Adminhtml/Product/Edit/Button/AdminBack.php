<?php

namespace TCGCollective\MarketPlace\Block\Adminhtml\Product\Edit\Button;

/**
 * Class Back
 */
class AdminBack extends \Magento\Catalog\Block\Adminhtml\Product\Edit\Button\Back
{
    /**
     * Get Button Data
     *
     * @return array
     */
    public function getButtonData()
    {
        return [
            'label' => __('Back'),
            'on_click' => sprintf("location.href = '%s';", $this->getBackUrl()),
            'class' => 'back',
            'sort_order' => 10
        ];
    }

    /**
     * Get URL for back
     *
     * @return string
     */
    private function getBackUrl()
    {
        $product = $this->getProduct();

        if ($product && $product->getTypeId() == "seller_membership") {
            return $this->getUrl(
                'lofmpsellermembership/product/index',
                []
            );
        }

        if ($this->context->getRequestParam('customerId')) {
            return $this->getUrl(
                'customer/index/edit',
                ['id' => $this->context->getRequestParam('customerId')]
            );
        }
        return $this->getUrl('*/*/');
    }
}
