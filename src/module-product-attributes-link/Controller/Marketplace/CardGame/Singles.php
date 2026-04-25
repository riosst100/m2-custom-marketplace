<?php

namespace TCGCollective\ProductAttributesLink\Controller\Marketplace\CardGame;

class Singles extends \TCGCollective\ProductAttributesLink\Controller\Marketplace\Index
{
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Singles Database | Collectible Card Games'));
        return $resultPage;
    }
}
