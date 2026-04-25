<?php

namespace TCGCollective\MarketPlace\Block\Adminhtml\SidebarFilters;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class NewSidebarFiltersAttributesButton implements ButtonProviderInterface
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
        $attributeId = '';
        $label = '';

        // $attribute = $this->helper->getAttribute('anime_brand');
        // if ($attribute) {
        //     $attributeId = $attribute->getAttributeId();
        //     $label = $attribute->getFrontendLabel();
        // }

        $url = $this->getUrl(
            '*/*/new',
            []
        );

        return [
            'label' => __('Add Attribute to Sidebar Filters'),
            'on_click' => sprintf("location.href = '%s';", $url),
            'class' => 'primary',
            'sort_order' => 10
        ];
    }
}