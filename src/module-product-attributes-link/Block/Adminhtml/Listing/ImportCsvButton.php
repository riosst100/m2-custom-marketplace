<?php

namespace TCGCollective\ProductAttributesLink\Block\Adminhtml\Listing;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class ImportCsvButton implements ButtonProviderInterface
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $redirect;

    protected $platformDbHelper;

    /**
     * @param Context $context
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect,
     * @param \TCGCollective\PlatformDatabase\Helper\Data $platformDbHelper
     */
    public function __construct(
        Context $context,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \TCGCollective\PlatformDatabase\Helper\Data $platformDbHelper
    ) {
        $this->context = $context;
        $this->redirect = $redirect;
        $this->platformDbHelper = $platformDbHelper;
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
        $label = null;
        $url = null;

        $customTableType = $this->platformDbHelper->initCustomTableType();
        if (!$customTableType || $customTableType == "sets") {
            $url = $this->getUrl(
                'productattributeslink/import/sets'
            );
            $label = __('Upload Sets/Release');
        }

        if ($customTableType == "sealedproducts") {
            $url = $this->getUrl(
                'productattributeslink/import/sealedproducts'
            );
            $label = __('Upload Sealed Products');
        }

        if ($customTableType == "singles") {
            $url = $this->getUrl(
                'productattributeslink/import/singles'
            );
            $label = __('Upload Singles');
        }

        if (!$url && !$label) {
            return [];
        }

        return [
            'label' => $label,
            'on_click' => sprintf("location.href = '%s';", $url),
            'class' => 'primary',
            'sort_order' => 10
        ];
    }
}