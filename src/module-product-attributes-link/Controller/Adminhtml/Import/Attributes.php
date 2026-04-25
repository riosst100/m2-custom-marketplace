<?php

namespace TCGCollective\ProductAttributesLink\Controller\Adminhtml\Import;

class Attributes extends \TCGCollective\ProductAttributesLink\Controller\Adminhtml\Index
{
    public function execute()
    {
        // $attributeLabel = '';

        // $attributeCode = $this->getRequest()->getParam('attribute_code');
        // if ($attributeCode) {
        //     switch($attributeCode) {
        //         case 'brand':
        //             $attributeLabel = 'Brand';
        //     }
        // }

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Upload Attributes'));
        return $resultPage;
    }
}
