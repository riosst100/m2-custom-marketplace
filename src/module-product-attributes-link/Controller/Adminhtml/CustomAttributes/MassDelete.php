<?php

namespace TCGCollective\ProductAttributesLink\Controller\Adminhtml\CustomAttributes;

use Magento\Backend\App\Action;
use TCGCollective\ProductAttributesLink\Model\ResourceModel\CustomAttributes\CollectionFactory;

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
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context,$resultPageFactory,$registry);
    }

    public function execute()
    {
        try {
            $attributeCode = '';
            $selected = $this->getRequest()->getParam('selected') ?: null;
            $excluded = $this->getRequest()->getParam('excluded') ?: null;
            
            $id = $this->getRequest()->getParam('id') ?: null;
            // dd($this->getRequest()->getParams());
            if ($id || $selected || $excluded) {
                $count = 0;

                $collection = $this->collectionFactory->create();
                if ($id) {
                    $collection->addFieldToFilter('entity_id', $id);
                } elseif ($selected) {
                    $collection->addFieldToFilter('entity_id', ['in' => $selected]);
                } elseif ($excluded == "false") {
                    $refererUrl = $this->_redirect->getRefererUrl();
                    // dd($refererUrl);

                    $categories = null;

                    if (preg_match('#/categories/([^/]+)/#', $refererUrl, $matches)) {
                        $categories = $matches[1]; // The first capturing group contains the set_id
                    }

                    // dd($categories);

                    $collection->addFieldToFilter('categories', ['eq' => $categories]);
                } else {
                    $this->messageManager->addError(__('Please select data to delete.'));
                    return $this->_redirect($this->_redirect->getRefererUrl());
                }

                // dd($collection);
                
                if ($collection->getSize()) {
                    foreach($collection as $model) {
                        $attributeCode = $model->getData('attribute_code');
                        $model->delete();

                        $count++;
                    }
                }
                
                $this->messageManager->addSuccess(__('A total of %1 record have been deleted.', $count));
            }
        } catch (\Exception $e) {   
            $this->messageManager->addError(__($e->getMessage()));
        }
        
        $this->_redirect($this->_redirect->getRefererUrl());
    }
}