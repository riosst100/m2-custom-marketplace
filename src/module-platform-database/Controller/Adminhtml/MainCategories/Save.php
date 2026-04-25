<?php

namespace TCGCollective\PlatformDatabase\Controller\Adminhtml\MainCategories;

class Save extends \TCGCollective\PlatformDatabase\Controller\Adminhtml\Index 
{
    public function execute()
    {
        $params = $this->getRequest()->getParams() ?: null;
        // dd($params);
        if ($params) {
            try {
                $model = $this->mainCategoriesConfigFactory->create();
                $model->setData($params);
                $model->save();

                $this->messageManager->addSuccess(__('Data saved successfully.'));
            } catch (\Exception $e) {
                $this->messageManager->addError(__($e->getMessage()));
            }
        } else {
            $this->messageManager->addError(__('Please select data to add as Sidebar Filters.'));
        }

        return $this->_redirect('*/*/index');
    }
}