<?php

namespace TCGCollective\ProductAttributesLink\Controller\Marketplace\ModelKitsVehiclesSets;

class Singles extends \TCGCollective\ProductAttributesLink\Controller\Marketplace\PlatformDatabase
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
