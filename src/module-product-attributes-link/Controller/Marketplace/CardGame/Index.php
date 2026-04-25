<?php

namespace TCGCollective\ProductAttributesLink\Controller\Marketplace\CardGame;

class Index extends \TCGCollective\ProductAttributesLink\Controller\Marketplace\Index
{
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Sets / Release Database | Collectible Card Games'));
        return $resultPage;
    }
}
