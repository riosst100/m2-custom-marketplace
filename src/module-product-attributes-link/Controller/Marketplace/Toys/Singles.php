<?php

namespace TCGCollective\ProductAttributesLink\Controller\Marketplace\Toys;

class Singles extends \TCGCollective\ProductAttributesLink\Controller\Marketplace\Index
{
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Singles Database | Action Toys / Anime'));
        return $resultPage;
    }
}
