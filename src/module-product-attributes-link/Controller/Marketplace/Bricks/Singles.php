<?php

namespace TCGCollective\ProductAttributesLink\Controller\Marketplace\Bricks;

class Singles extends \TCGCollective\ProductAttributesLink\Controller\Marketplace\Index
{
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Singles Database | Bricks'));
        return $resultPage;
    }
}
