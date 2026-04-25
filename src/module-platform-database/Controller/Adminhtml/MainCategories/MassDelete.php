<?php

namespace TCGCollective\PlatformDatabase\Controller\Adminhtml\MainCategories;

class MassDelete extends \TCGCollective\PlatformDatabase\Controller\Adminhtml\Index
{
    public function execute()
    {
        $count = 0;

        $attributeId = $this->getRequest()->getParam('id') ?: null;
        $selectedIds = $this->getRequest()->getParam('selected') ?: null;
        $excluded = $this->getRequest()->getParam('excluded') ?: null;
        $filters = $this->getRequest()->getParam('filters') ?: null;

        // dd($this->getRequest()->getParams());
        // dd($excluded);
        
        if ($attributeId || $selectedIds || !$selectedIds && $excluded == "false") {
            try {
                if ($attributeId) {
                    $count += 1;

                    $model = $this->mainCategoriesConfigFactory->create()
                    ->load($attributeId);
                    $model->setData('is_filterable', 0);
                    $model->save();
                } elseif ($selectedIds) {
                    foreach($selectedIds as $attributeId) {
                        $count++;

                        $model = $this->mainCategoriesConfigFactory->create()
                        ->load($attributeId);
                        $model->setData('is_filterable', 0);
                        $model->save();
                    }
                } elseif ($excluded == "false" && $filters) {

                    $collection = $this->attributeCollectionFactory->create()->getCollection();
                    
                    foreach($filters as $key => $value) {
                        if ($key == "placeholder") {
                            continue;
                        }

                        $collection->addFieldToFilter($key, $value);
                    }

                    if ($collection->getSize()) {
                        foreach($collection as $model) {
                            $model = $this->attributeCollectionFactory->create()
                            ->load($model->getAttributeId());
                            $model->setData('is_filterable', 0);
                            $model->save();
                        }
                    }
                } else {
                    $this->messageManager->addError(__('Please select data to remove from Sidebar Filters.'));
                }

                if ($count > 0) {
                    $this->messageManager->addSuccess(__('A total of %1 attribute have been removed from Sidebar Filters.', $count));
                }
            } catch (\Exception $e) {
                $this->messageManager->addError(__($e->getMessage()));
            }
        } else {
            $this->messageManager->addError(__('Please select data to remove from Sidebar Filters.'));
        }

        return $this->_redirect('*/*/index');
    }
}