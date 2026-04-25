<?php

namespace TCGCollective\ProductAttributesLink\Controller\Adminhtml\AttributeOption;

use Magento\Backend\App\Action;

class MassDelete extends \TCGCollective\ProductAttributesLink\Controller\Adminhtml\Index
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \TCGCollective\ProductAttributesLink\Model\ProductAttributesLinkFactory
     */
    protected $productAttributesLinkFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \TCGCollective\ProductAttributesLink\Model\ProductAttributesLinkFactory $productAttributesLinkFactory,
    ) {
        parent::__construct($context, $resultPageFactory, $registry);
        $this->productAttributesLinkFactory = $productAttributesLinkFactory;
    }

    public function execute()
    {
        try {
            $mappingType = 'categories';
            $selected = $this->getRequest()->getParam('selected') ?: null;
            $id = $this->getRequest()->getParam('id') ?: null;
            if ($id || $selected) {
                $count = 0;

                $collection = $this->productAttributesLinkFactory->create()
                ->getCollection();
                if ($id) {
                    $collection->addFieldToFilter('entity_id', $id);
                }
                if ($selected) {
                    $collection->addFieldToFilter('entity_id', ['in' => $selected]);
                }
                if ($collection->getSize()) {
                    foreach($collection as $model) {
                        $mappingType = $model->getCategoryId() ? 'categories' : 'attributes';
                        $model->delete();

                        $count++;
                    }
                }
                
                $this->messageManager->addSuccess(__('A total of %1 record(s) have been deleted.', $count));
            }
        } catch (\Exception $e) {   
            $this->messageManager->addError(__($e->getMessage()));
        }

        $this->_redirect('*/*/'.$mappingType);
    }
}