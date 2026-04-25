<?php

namespace TCGCollective\MarketPlace\Block\Adminhtml\Product\Edit\Button;

use Magento\Catalog\Block\Adminhtml\Product\Edit\Button\Generic;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\UiComponent\Context;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class Back
 */
class Back extends Generic
{
    /**
     * Url Builder
     *
     * @var Context
     */
    protected $context;

    /**
     * Registry
     *
     * @var Registry
     */
    protected $registry;

    protected $helper;
    protected $catalogHelper;

    public function __construct(
        Context $context,
        Registry $registry,
        \TCGCollective\ProductAttributesLink\Helper\Data $helper,
        \TCGCollective\MarketPlace\Helper\Catalog $catalogHelper
    ) {
        $this->helper = $helper;
        $this->catalogHelper = $catalogHelper;
        parent::__construct($context,$registry);
    }

    /**
     * Get Button Data
     *
     * @return array
     */
    public function getButtonData()
    {
        if (!$this->context->getRequestParam('id')) {
            return null;
        }

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
        $categoryId  = null;

        $product = $this->getProduct();
        if ($product) {
            $productData = $product->toArray();
            $typeId = $product->getTypeId();
            $categoryIds = $product->getCategoryIds();
            if ($categoryIds) {
                $categoryId = $categoryIds[0];
            }
        }

        // get my products url
        $myProductsUrl = $this->catalogHelper->getMyProductsUrlByCategoryId($categoryId, $productData, $typeId);

        return $this->getUrl($myProductsUrl);
    }
}
