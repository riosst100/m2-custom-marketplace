<?php

namespace TCGCollective\ProductAttributesLink\Controller\Marketplace\CardGame;

class SealedProducts extends \TCGCollective\ProductAttributesLink\Controller\Marketplace\Index
{
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Sealed Products Database | Collectible Card Games'));
        return $resultPage;
    }
}
