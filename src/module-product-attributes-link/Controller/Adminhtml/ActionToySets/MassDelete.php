<?php

namespace TCGCollective\ProductAttributesLink\Controller\Adminhtml\ActionToySets;

class MassDelete extends \TCGCollective\ProductAttributesLink\Controller\Adminhtml\Index
{
    protected $resultPageFactory;
    protected $registry;
    protected $actionToySetsFactory;
    protected $actionToySinglesFactory;
    
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \TCGCollective\ProductAttributesLink\Model\ActionToySetsFactory $actionToySetsFactory,
        \TCGCollective\ProductAttributesLink\Model\ActionToySinglesFactory $actionToySinglesFactory
    ) {
        $this->actionToySetsFactory = $actionToySetsFactory;
        $this->actionToySinglesFactory = $actionToySinglesFactory;
        parent::__construct($context, $resultPageFactory, $registry);
    }

    public function execute()
    {
        $refererUrl = $this->_redirect->getRefererUrl();

        // Define variable
        $collection = $this->actionToySetsFactory->create()->getCollection();
        $setsListingUrl = '*/actiontoysets/index';
        $entityCode = 'toys_brand';
        $entity = null;
        $count = 0;

        if ($refererUrl) {
            if (preg_match('/'.$entityCode.'\/(\d+)/', $refererUrl, $matches)) {
                $entity = $matches[1];
            }
        }

        $id = $this->getRequest()->getParam('id') ?: null;
        $selectedIds = $this->getRequest()->getParam('selected') ?: null;
        $excluded = $this->getRequest()->getParam('excluded') ?: null;
        
        if ($id || $selectedIds || !$selectedIds && $excluded == "false") {
            try {
                if ($id) {
                    $collection->addFieldToFilter('entity_id', $id);
                } elseif ($selectedIds) {
                    $collection->addFieldToFilter('entity_id', ['in' => $selectedIds]);
                } elseif ($excluded == "false" && $entity) {
                    $collection->addFieldToFilter($entityCode, $entity);
                } else {
                    $this->messageManager->addError(__('Please select data to delete.'));
                    return $this->_redirect($refererUrl);
                }

                if ($collection->getSize()) {
                    foreach($collection as $model) {
                        $entity = $model->getData($entityCode);
                        $setId = $model->getId();

                        $model->delete();

                        $singlesDeleteCount = 0;

                        // Delete card
                        $collection = $this->actionToySinglesFactory->create()->getCollection();
                        $collection->addFieldToFilter('set_id', $setId);
                        if ($collection->getSize()) {
                            foreach($collection as $model) {
                                $model->delete();

                                $singlesDeleteCount++;
                            }
                        }
                        
                        if ($singlesDeleteCount > 0) {
                            $this->messageManager->addSuccess(__('A total of %1 singles have been deleted.', $singlesDeleteCount));
                        }

                        $count++;
                    }
                }
                
                $this->messageManager->addSuccess(__('A total of %1 sets have been deleted.', $count));
            } catch (\Exception $e) {
                $this->messageManager->addError(__($e->getMessage()));
            }

            if ($entity) {
                return $this->_redirect($setsListingUrl.'/'.$entityCode.'/'.$entity);
            }
        } else {
            $this->messageManager->addError(__('Please select data to delete.'));
        }

        return $this->_redirect($refererUrl);
    }
}