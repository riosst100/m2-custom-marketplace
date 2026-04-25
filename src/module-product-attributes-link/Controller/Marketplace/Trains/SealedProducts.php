<?php

namespace TCGCollective\ProductAttributesLink\Controller\Marketplace\Trains;

class SealedProducts extends \TCGCollective\ProductAttributesLink\Controller\Marketplace\Index
{
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Sealed Products Database | Trains'));
        return $resultPage;
    }
}
