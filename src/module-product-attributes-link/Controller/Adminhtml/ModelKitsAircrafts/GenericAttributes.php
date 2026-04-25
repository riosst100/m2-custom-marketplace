<?php

namespace TCGCollective\ProductAttributesLink\Controller\Adminhtml\ModelKitsAircrafts;

class GenericAttributes extends \TCGCollective\ProductAttributesLink\Controller\Adminhtml\PlatformDatabase
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