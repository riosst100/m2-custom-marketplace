<?php

namespace TCGCollective\ProductAttributesLink\Controller\Adminhtml\AnimeToysAttributes;

class Index extends \TCGCollective\ProductAttributesLink\Controller\Adminhtml\Index
{
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Anime Attributes'));
        return $resultPage;
    }
}
