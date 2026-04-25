<?php

namespace TCGCollective\ProductAttributesLink\Controller\Adminhtml\PlatformDatabase;

class SampleCsv extends \TCGCollective\ProductAttributesLink\Controller\Adminhtml\PlatformDatabase
{
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend('Download Sample CSV');
        return $resultPage;
    }
}
