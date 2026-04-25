<?php

namespace TCGCollective\ProductAttributesLink\Controller\Marketplace\NonSportCards;

class SealedProducts extends \TCGCollective\ProductAttributesLink\Controller\Marketplace\Index
{
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Sealed Products Database | Non Sports Cards'));
        return $resultPage;
    }
}
