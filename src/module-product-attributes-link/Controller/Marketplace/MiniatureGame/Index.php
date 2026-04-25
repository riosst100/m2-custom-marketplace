<?php

namespace TCGCollective\ProductAttributesLink\Controller\Marketplace\MiniatureGame;

class Index extends \TCGCollective\ProductAttributesLink\Controller\Marketplace\Index
{
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Sets / Release Database | Miniature Games'));
        return $resultPage;
    }
}
