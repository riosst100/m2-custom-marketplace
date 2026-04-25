<?php

namespace TCGCollective\ProductAttributesLink\Controller\Adminhtml\CustomAttributesValues;

use Magento\Backend\App\Action;

class MassDelete extends \TCGCollective\ProductAttributesLink\Controller\Adminhtml\Index
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \TCGCollective\ProductAttributesLink\Helper\Data
     */
    protected $helper;

    protected $cardGameSets;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry
     * @param \TCGCollective\ProductAttributesLink\Model\DollsSetsFactory $cardGameSets
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \TCGCollective\ProductAttributesLink\Model\DollsSetsFactory $cardGameSets
    ) {
        $this->cardGameSets = $cardGameSets;
        parent::__construct($context,$resultPageFactory,$registry);
    }

    public function execute()
    {
        try {
            $entityID = '';
            $selected = $this->getRequest()->getParam('selected') ?: null;
            
            die(print_r($this->getRequest()->getParams(),true));
            $id = $this->getRequest()->getParam('id') ?: null;
            if ($id || $selected) {
                $count = 0;

                $collection = $this->cardGameSets->create()
                ->getCollection();
                if ($id) {
                    $collection->addFieldToFilter('entity_id', $id);
                }
                if ($selected) {
                    $collection->addFieldToFilter('entity_id', ['in' => $selected]);
                }
                if ($collection->getSize()) {
                    foreach($collection as $model) {
                        $entityID = $model->getCardGame();
                        $model->delete();

                        $count++;
                    }
                }
                
                $this->messageManager->addSuccess(__('A total of %1 sets have been deleted.', $count));
            }
        } catch (\Exception $e) {   
            $this->messageManager->addError(__($e->getMessage()));
        }

        if ($entityID) {
            $this->_redirect('*/*/index/card_game/'.$entityID);
        } else {
            $this->_redirect($this->_redirect->getRefererUrl());
        }
    }
}