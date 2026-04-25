<?php

namespace TCGCollective\ProductAttributesLink\Controller\Marketplace\MiniatureGame;

class SealedProducts extends \TCGCollective\ProductAttributesLink\Controller\Marketplace\Index
{
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Sealed Products Database | Miniature Games'));
        return $resultPage;
    }
}
