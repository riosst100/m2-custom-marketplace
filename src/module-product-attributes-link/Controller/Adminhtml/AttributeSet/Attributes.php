<?php

namespace TCGCollective\ProductAttributesLink\Controller\Adminhtml\AttributeSet;

class Attributes extends \TCGCollective\ProductAttributesLink\Controller\Adminhtml\Index
{
    /**
     * Attribute Set Mapping list page
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Attributes - Attribute Set Mapping'));
        return $resultPage;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(
            'TCGCollective_ProductAttributesLink::attribute_set_mapping'
        );
    }
}
