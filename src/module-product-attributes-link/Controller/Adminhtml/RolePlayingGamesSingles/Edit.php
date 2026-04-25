<?php

namespace TCGCollective\ProductAttributesLink\Controller\Adminhtml\RolePlayingGamesSingles;

class Edit extends \TCGCollective\ProductAttributesLink\Controller\Adminhtml\PlatformDatabase
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