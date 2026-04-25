<?php

namespace TCGCollective\MarketPlace\Block\Adminhtml\Template\Grid\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;
use Magento\Store\Model\StoreManagerInterface;

class Website extends AbstractRenderer
{
    protected StoreManagerInterface $storeManager;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        parent::__construct($context, $data);
    }

    public function render(DataObject $row)
    {
        $websiteId = (int)$row->getData($this->getColumn()->getIndex());

        if (!$websiteId) {
            return __('All Websites');
        }

        try {
            return $this->storeManager
                ->getWebsite($websiteId)
                ->getName();
        } catch (\Exception $e) {
            return __('N/A');
        }
    }
}
