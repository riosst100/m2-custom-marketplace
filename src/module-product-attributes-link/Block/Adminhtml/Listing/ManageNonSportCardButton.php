<?php

namespace TCGCollective\ProductAttributesLink\Block\Adminhtml\Listing;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class ManageNonSportCardButton implements ButtonProviderInterface
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $redirect;

    protected $helper;

    /**
     * @param Context $context
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect,
     * @param \TCGCollective\ProductAttributesLink\Helper\Data $helper
     */
    public function __construct(
        Context $context,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \TCGCollective\ProductAttributesLink\Helper\Data $helper
    ) {
        $this->context = $context;
        $this->redirect = $redirect;
        $this->helper = $helper;
    }

    /**
     * Generate url by route and parameters
     *
     * @param string $route
     * @param array $params
     * @return string
     */
    protected function getUrl($route = '', $params = [])
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }

    public function getButtonData()
    {
        $attributeCode = 'nsc_categories';

        $attributeId = '';
        $label = '';

        $attribute = $this->helper->getAttribute($attributeCode);
        if ($attribute) {
            $attributeId = $attribute->getAttributeId();
            $label = $attribute->getFrontendLabel();
        }

        $url = $this->getUrl(
            'catalog/product_attribute/edit',
            ['attribute_id' => $attributeId]
        );

        return [
            'label' => __('Manage %1', $label),
            'on_click' => sprintf("location.href = '%s';", $url),
            'class' => 'primary',
            'sort_order' => 10
        ];
    }
}