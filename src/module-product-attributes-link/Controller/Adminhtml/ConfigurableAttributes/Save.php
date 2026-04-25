<?php

namespace TCGCollective\ProductAttributesLink\Controller\Adminhtml\ConfigurableAttributes;

use Magento\Backend\App\Action;

class Save extends \TCGCollective\ProductAttributesLink\Controller\Adminhtml\Index
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \TCGCollective\MarketplaceConfigurableProduct\Model\ProductConfigurableAttributesFactory
     */
    protected $mappingFactory;

    /**
     * @var \TCGCollective\MarketplaceConfigurableProduct\Model\ProductConfigurableAttributesGroupFactory
     */
    protected $mappingGroupFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \TCGCollective\MarketplaceConfigurableProduct\Model\VariationsAttributesMappingFactory $mappingFactory,
        \TCGCollective\MarketplaceConfigurableProduct\Model\ProductConfigurableAttributesGroupFactory $mappingGroupFactory
    ) {
        parent::__construct($context, $resultPageFactory, $registry);
        $this->mappingFactory = $mappingFactory;
        $this->mappingGroupFactory = $mappingGroupFactory;
    }

    public function execute()
    {
        try {
            $data = $this->getRequest()->getParams();

            $collection = $this->mappingFactory->create()
            ->getCollection();

            $id = isset($data['entity_id']) ? $data['entity_id'] : null;
            if ($id) {
                $collection->addFieldToFilter('entity_id', $id);
                if ($collection->getSize()) {
                    $model = $collection->getFirstItem();

                    $data['entity_id'] = $model->getId();
                }
            } else {
                $model = $this->mappingFactory->create();
            }

            $model->setData($data);
            $model->save();
            
            $this->messageManager->addSuccess(__('Data saved successfully'));
        } catch (\Exception $e) {   
            $this->messageManager->addError(__($e->getMessage()));
        }

        $this->_redirect('*/*/index');
    }
}