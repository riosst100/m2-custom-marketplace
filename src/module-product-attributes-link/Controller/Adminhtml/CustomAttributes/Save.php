<?php

namespace TCGCollective\ProductAttributesLink\Controller\Adminhtml\CustomAttributes;

use TCGCollective\ProductAttributesLink\Model\ResourceModel\CustomAttributes\CollectionFactory;

class Save extends \TCGCollective\ProductAttributesLink\Controller\Adminhtml\Index
{
    protected $collection;


    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;
    
    protected $collectionFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry
     * @param \TCGCollective\ProductAttributesLink\Helper\Data $helper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        CollectionFactory $collectionFactory
    ) {
        $this->collection = $collectionFactory;
        parent::__construct($context,$resultPageFactory,$registry);
    }

    public function execute()
    {
        try {
            $data = $this->getRequest()->getParams();
            $exist = false;

            $id = isset($data['entity_id']) ? $data['entity_id'] : null;
            $collection = $this->collection->create()
            ->addFieldToFilter('entity_id', $id);
            if ($collection->getSize()) {
                $model = $collection->getFirstItem();
                if ($model) {
                    $data['entity_id'] = $model->getId();

                    $exist = true;

                    $model->setData($data);
                    $model->save();
                }
            }
            
            if ($exist) {
                $this->messageManager->addSuccess(__('Option updated successfully'));
            } else {
                $this->messageManager->addSuccess(__('Option saved successfully'));
            }
        } catch (\Exception $e) {   
            $this->messageManager->addError(__($e->getMessage()));
        }

        $this->_redirect('*/*/values/attribute_code/'.$model->getData('attribute_code'));
    }
}