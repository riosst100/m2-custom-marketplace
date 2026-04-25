<?php

namespace TCGCollective\ProductAttributesLink\Controller\Adminhtml\AnimeToysSingles;

class Save extends \TCGCollective\ProductAttributesLink\Controller\Adminhtml\PlatformDatabase 
{
    public function execute() {
        return $this->_redirect($this->platformDbHelper->updateCustomTableData());
    }
}