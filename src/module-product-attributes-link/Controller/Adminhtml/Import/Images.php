<?php

namespace TCGCollective\ProductAttributesLink\Controller\Adminhtml\Import;

class Images extends \TCGCollective\ProductAttributesLink\Controller\Adminhtml\Index
{
    /**
     * @inheritDoc
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->renderLayout();

        return null;
    }
}
