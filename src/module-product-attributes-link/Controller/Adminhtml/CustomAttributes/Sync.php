<?php

namespace TCGCollective\ProductAttributesLink\Controller\Adminhtml\CustomAttributes;

class Sync extends \TCGCollective\ProductAttributesLink\Controller\Adminhtml\PlatformDatabase
{   
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(
            $this->platformDbHelper->syncPlatformDatabaseToCustomAttributes()
        );
        return $resultPage;
    }
}