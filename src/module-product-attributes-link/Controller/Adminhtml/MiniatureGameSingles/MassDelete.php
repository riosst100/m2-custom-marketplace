<?php

namespace TCGCollective\ProductAttributesLink\Controller\Adminhtml\MiniatureGameSingles;

use Magento\Backend\App\Action;
use Magento\Ui\Component\MassAction\Filter;
use TCGCollective\ProductAttributesLink\Model\ResourceModel\MiniatureGameSingles\CollectionFactory;

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
    
    protected $collectionFactory;
    
    protected $filter;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        CollectionFactory $collectionFactory,
        Filter $filter
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context,$resultPageFactory,$registry);
    }

    public function execute()
    {
        $entityCode = 'miniature_game';
        $categoryPage = '*/legosets/index/miniature_game';

        $refererUrl = $this->_redirect->getRefererUrl();

        $setId = null;

        if ($refererUrl) {
            if (preg_match('/set_id\/(\d+)/', $refererUrl, $matches)) {
                $setId = $matches[1]; // The first capturing group contains the set_id
            }
        }
        
        $count = 0;
        $entity = null;

        $id = $this->getRequest()->getParam('id') ?: null;
        $selectedIds = $this->getRequest()->getParam('selected') ?: null;
        $excluded = $this->getRequest()->getParam('excluded') ?: null;
        
        if ($id || $selectedIds || !$selectedIds && $excluded == "false") {
            try {
                $collection = $this->collectionFactory->create();
                if ($id) {
                    $collection->addFieldToFilter('entity_id', $id);
                } elseif ($selectedIds) {
                    $collection->addFieldToFilter('entity_id', ['in' => $selectedIds]);
                } elseif ($excluded == "false" && $setId) {
                    $collection->addFieldToFilter('set_id', $setId);
                } else {
                    $this->messageManager->addError(__('Please select data to delete.'));
                    return $this->_redirect($refererUrl);
                }

                if ($collection->getSize()) {
                    foreach($collection as $model) {
                        $entity = $model->getData($entityCode);
                        if ($model->getSetId()) {
                            $setId = $model->getSetId();
                        }
                        $model->delete();

                        $count++;
                    }
                }
                
                $this->messageManager->addSuccess(__('A total of %1 data have been deleted.', $count));
            } catch (\Exception $e) {
                $this->messageManager->addError(__($e->getMessage()));
            }

            if ($setId) {
                return $this->_redirect('*/*/index/set_id/'.$setId);
            }

            if ($entity) {
                return $this->_redirect($categoryPage.'/'.$entity);
            }
        } else {
            $this->messageManager->addError(__('Please select data to delete.'));
        }

        return $this->_redirect($refererUrl);
    }
}