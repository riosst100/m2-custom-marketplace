<?php

namespace TCGCollective\MarketPlace\Controller\Adminhtml\SidebarFilters;

class Save extends \TCGCollective\ProductAttributesLink\Controller\Adminhtml\Index
{
    public function execute()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->attributeFactory = $objectManager->get(\Magento\Eav\Model\Entity\AttributeFactory::class);

        $attributeIds = $this->getRequest()->getParam('attribute_ids') ?: null;
        if ($attributeIds) {
            try {
                $count = 0;

                foreach($attributeIds as $attributeId) {
                    $count++;

                    $model = $this->attributeFactory->create()
                    ->load($attributeId);
                    $model->setData('is_filterable', 1);

                    if (!$model->getIsMasterAttribute()) {
                        $model->setData('is_master_attribute', 0);
                    }
                    
                    $model->save();
                }

                if ($count > 0) {
                    $this->messageManager->addSuccess(__('A total of %1 attribute have been added to Sidebar Filters.', $count));
                }
            } catch (\Exception $e) {
                $this->messageManager->addError(__($e->getMessage()));
            }
        } else {
            $this->messageManager->addError(__('Please select data to add as Sidebar Filters.'));
        }

        return $this->_redirect('*/*/index');
    }
}