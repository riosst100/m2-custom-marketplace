<?php

namespace TCGCollective\ProductAttributesLink\Controller\Marketplace\LivingCardGameSets;

class Index extends \TCGCollective\ProductAttributesLink\Controller\Marketplace\Index
{
    public function execute()
    {
        $attrId = $this->getRequest()->getParam('living_card_game');
        $title = $this->helper->getOptionLabelById('living_card_game', $attrId);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set($title . ' | Sets/Release');
        return $resultPage;
    }
}
