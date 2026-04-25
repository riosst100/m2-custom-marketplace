<?php

namespace TCGCollective\ProductAttributesLink\Block\Adminhtml\PlatformDatabase;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class DownloadButton implements ButtonProviderInterface
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

    protected $request;

    /**
     * @param Context $context
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect,
     * @param \TCGCollective\ProductAttributesLink\Helper\Data $helper
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        Context $context,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \TCGCollective\ProductAttributesLink\Helper\Data $helper,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->context = $context;
        $this->request = $request;
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
        // $attributeId = '';
        // $label = '';

        // $attribute = $this->helper->getAttribute('anime_brand');
        // if ($attribute) {
        //     $attributeId = $attribute->getAttributeId();
        //     $label = $attribute->getFrontendLabel();
        // }

        $url = $this->getUrl(
            'database/platformDatabase/download',
            []
        );

        return [
            'label' => __('Download As CSV'),
            'on_click' => sprintf("location.href = '%s';", $url),
            'class' => 'primary',
            'sort_order' => 10
        ];
    }
}