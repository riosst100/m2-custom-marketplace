<?php

namespace TCGCollective\ProductAttributesLink\Controller\Adminhtml\PlatformDatabase;

class MassDelete extends \TCGCollective\ProductAttributesLink\Controller\Adminhtml\PlatformDatabase
{
    public function execute() {
        return $this->_redirect($this->platformDbHelper->deleteCustomTableData());
    }
}