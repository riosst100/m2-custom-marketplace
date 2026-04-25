<?php

namespace TCGCollective\ProductAttributesLink\Controller\Adminhtml\PlatformDatabase;

class Download extends \TCGCollective\ProductAttributesLink\Controller\Adminhtml\PlatformDatabase
{
    public function execute()
    {
        $result = $this->platformDbHelper->downloadPlatformDatabase();
        // dd($result);
        if (!$result) {
            // $this->messageManager->addError(__('Attribute Set or Attribute Set Mapping doesn\'t exist.'));
            return $this->_redirect($this->_redirect->getRefererUrl());
        }

        if (isset($result['error']) && $result['error']) {
            $this->messageManager->addError(__($result['error']));
            return $this->_redirect($this->_redirect->getRefererUrl());
        }

        if ($result['success']) {
            return $this->fileFactory->create($result['name'], $result['file'], 'var');
        }
        
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath($result['refererUrl']);
    }
}
