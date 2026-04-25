<?php

namespace TCGCollective\ProductAttributesLink\Controller\Marketplace\ModelKitsMilitarySets;

class Index extends \TCGCollective\ProductAttributesLink\Controller\Marketplace\PlatformDatabase
{
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(
            $this->platformDbHelper->getPlatformDbTitle()
        );
        return $resultPage;
    }
}
