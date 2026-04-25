<?php

namespace TCGCollective\ProductAttributesLink\Controller\Adminhtml\AttributeSet;

use Magento\Backend\App\Action;
use Magento\Ui\Component\MassAction\Filter;
use TCGCollective\ProductAttributesLink\Model\ResourceModel\ProductAttributesLink\CollectionFactory;

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
        $refererUrl = $this->_redirect->getRefererUrl();
        
        $count = 0;
        $entity = null;

        // dd($this->   getRequest()->getParams());

        $id = $this->getRequest()->getParam('id') ?: null;
        $selectedIds = $this->getRequest()->getParam('selected') ?: null;
        $excluded = $this->getRequest()->getParam('excluded') ?: null;
        $filters = $this->getRequest()->getParam('filters') ?: null;
        
        if ($id || $selectedIds || !$selectedIds && $excluded == "false") {
            try {
                $collection = $this->collectionFactory->create();
                if ($id) {
                    $collection->addFieldToFilter('entity_id', $id);
                } elseif ($selectedIds) {
                    $collection->addFieldToFilter('entity_id', ['in' => $selectedIds]);
                } elseif ($excluded == "false") {
                    if ($filters) {
                        foreach($filters as $field => $value) {
                            if ($field != "placeholder") {
                                $collection->addFieldToFilter($field, $value);
                            }
                        }
                    }

                    $collection->addFieldToFilter('mapping_type', 'attribute_set');
                } else {
                    $this->messageManager->addError(__('Please select data to delete.'));
                    return $this->_redirect($refererUrl);
                }

                if ($collection->getSize()) {
                    foreach($collection as $model) {
                        $model->delete();

                        $count++;
                    }
                }
                
                $this->messageManager->addSuccess(__('A total of %1 data have been deleted.', $count));
            } catch (\Exception $e) {
                $this->messageManager->addError(__($e->getMessage()));
            }
        } else {
            $this->messageManager->addError(__('Please select data to delete.'));
        }

        return $this->_redirect($refererUrl);
    }
}