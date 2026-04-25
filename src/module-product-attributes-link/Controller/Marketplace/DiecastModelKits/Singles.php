<?php

namespace TCGCollective\ProductAttributesLink\Controller\Marketplace\DiecastModelKits;

class Singles extends \TCGCollective\ProductAttributesLink\Controller\Marketplace\Index
{
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Singles Database | Diecast / Model Kits'));
        return $resultPage;
    }
}
