<?php

namespace TCGCollective\MarketPlace\Controller\Adminhtml\ProductListing\Configurations;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;
use Lof\Formbuilder\Controller\Adminhtml\Form;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\Session;
use Magento\Backend\Model\View\Result\Page;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Registry;

class Edit extends \TCGCollective\ProductAttributesLink\Controller\Adminhtml\Index
{
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $model = $this->_objectManager->create(\TCGCollective\MarketPlace\Model\ProductListingCategoryConfig::class);
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This config no longer exists.'));
                /** @var Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }
        $data = $this->_objectManager->get(Session::class)->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        // dd($model);
        $this->registry->register('product_listing_configuration_form', $model);

        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('TCGCollective_MarketPlace::product_listing_categories_config');
        // $resultPage->getConfig()->getTitle()->prepend(__('Forms'));
        // $resultPage->getConfig()->getTitle()->prepend($model->getId() ? $model->getTitle() : __('Edit Product Listing Configurations'));
        $resultPage->getConfig()->getTitle()->prepend(__('Edit Product Listing Configurations'));

        return $resultPage;
    }
}
