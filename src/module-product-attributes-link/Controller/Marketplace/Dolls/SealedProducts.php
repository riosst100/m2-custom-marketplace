<?php

namespace TCGCollective\ProductAttributesLink\Controller\Marketplace\Dolls;

class SealedProducts extends \TCGCollective\ProductAttributesLink\Controller\Marketplace\Index
{
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Sealed Products Database | Fashion Dolls'));
        return $resultPage;
    }
}
