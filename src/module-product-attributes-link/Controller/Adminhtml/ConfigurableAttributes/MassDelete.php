<?php

namespace TCGCollective\ProductAttributesLink\Controller\Adminhtml\ConfigurableAttributes;

use Magento\Backend\App\Action;

class MassDelete extends \TCGCollective\ProductAttributesLink\Controller\Adminhtml\Index
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \TCGCollective\MarketplaceConfigurableProduct\Model\ProductConfigurableAttributesFactory
     */
    protected $mappingFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \TCGCollective\MarketplaceConfigurableProduct\Model\VariationsAttributesMappingFactory $mappingFactory
    ) {
        parent::__construct($context, $resultPageFactory, $registry);
        $this->mappingFactory = $mappingFactory;
    }

    public function execute()
    {
        try {
            $selected = $this->getRequest()->getParam('selected') ?: null;
            // die(print_r($selected,true));
            $id = $this->getRequest()->getParam('id') ?: null;
            if ($id || $selected) {
                $count = 0;

                $collection = $this->mappingFactory->create()
                ->getCollection();
                if ($id) {
                    $collection->addFieldToFilter('entity_id', $id);
                }
                if ($selected) {
                    $collection->addFieldToFilter('entity_id', ['in' => $selected]);
                }
                if ($collection->getSize()) {
                    foreach($collection as $model) {
                        $model->delete();

                        $count++;
                    }
                }
                
                $this->messageManager->addSuccess(__('A total of %1 record(s) have been deleted.', $count));
            }
        } catch (\Exception $e) {   
            $this->messageManager->addError(__($e->getMessage()));
        }

        $this->_redirect('*/*/index');
    }
}