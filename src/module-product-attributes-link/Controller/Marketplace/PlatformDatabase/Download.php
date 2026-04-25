<?php

namespace TCGCollective\ProductAttributesLink\Controller\Marketplace\PlatformDatabase;

class Download extends \TCGCollective\ProductAttributesLink\Controller\Marketplace\PlatformDatabase
{   
    public function execute()
    {
        $result = $this->platformDbHelper->marketplaceDownloadPlatformDatabase();
        if ($result['success']) {
            return $this->fileFactory->create($result['name'], $result['file'], 'var');
        }
        
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath($result['refererUrl']);
    }
}
