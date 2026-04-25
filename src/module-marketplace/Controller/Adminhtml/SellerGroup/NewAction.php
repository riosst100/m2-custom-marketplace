<?php

namespace TCGCollective\MarketPlace\Controller\Adminhtml\SellerGroup;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Customer\Controller\RegistryConstants;

class NewAction extends \TCGCollective\MarketPlace\Controller\Adminhtml\SellerGroup
{
    protected function _initGroup()
    {
        $groupId = $this->getRequest()->getParam('id');
        $this->_coreRegistry->register(RegistryConstants::CURRENT_GROUP_ID, $groupId);

        return $groupId;
    }

    public function execute()
    {
        // $this->_initGroup();

        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('TCGCollective_MarketPlace::seller_groups');
        $resultPage->getConfig()->getTitle()->prepend(__('New Seller Group'));

        $resultPage->addBreadcrumb(__('Marketplace'), __('Marketplace'));
        $resultPage->addBreadcrumb(__('Seller Groups'), __('Seller Groups'));
        $resultPage->addBreadcrumb(__('New'), __('New'));

        

        // return $resultPage;
        $groupId = $this->_initGroup();

        if ($groupId === null) {
            $resultPage->addBreadcrumb(__('New Group'), __('New Seller Groups'));
            $resultPage->getConfig()->getTitle()->prepend(__('New Seller Group'));
        } else {
            $resultPage->addBreadcrumb(__('Edit Group'), __('Edit Seller Group'));
            $resultPage->getConfig()->getTitle()->prepend(
                $this->groupRepository->getById($groupId)->getCode()
            );
        }

        // $resultPage->getLayout()->addBlock(\Magento\Customer\Block\Adminhtml\Group\Edit::class, 'group', 'content')
        //     ->setEditMode((bool)$groupId);
        $resultPage->getLayout()->addBlock(
            \TCGCollective\MarketPlace\Block\Adminhtml\SellerGroup\Edit::class,
            'seller_group_edit',
            'content'
        );

        return $resultPage;
    }
}
