<?php

namespace TCGCollective\ProductAttributesLink\Controller\Marketplace\CardGameDrop;

class Index extends \TCGCollective\ProductAttributesLink\Controller\Marketplace\Index
{   
    public function execute()
    {
        $attrId = $this->getRequest()->getParam('card_game');
        $title = $this->helper->getOptionLabelById('card_game', $attrId);
        $title .= ' | Drop';
        
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend($title);
        return $resultPage;
    }
}
