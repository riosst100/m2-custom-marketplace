<?php

namespace TCGCollective\ProductAttributesLink\Controller\Marketplace\AnimeModelKits;

class Singles extends \TCGCollective\ProductAttributesLink\Controller\Marketplace\PlatformDatabase
{
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set($this->platformDbHelper->getPlatformDbTitle());
        return $resultPage;
    }
}
