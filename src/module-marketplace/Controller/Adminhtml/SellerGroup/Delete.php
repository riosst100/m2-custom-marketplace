<?php

namespace TCGCollective\MarketPlace\Controller\Adminhtml\SellerGroup;

use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Delete
 */
class Delete extends \TCGCollective\MarketPlace\Controller\Adminhtml\SellerGroup implements HttpPostActionInterface
{
    /**
     * Delete customer group.
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            try {
                $this->groupRepository->deleteById($id);
                $this->messageManager->addSuccessMessage(__('You deleted the seller group.'));
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addErrorMessage(__('The seller group no longer exists.'));
                return $resultRedirect->setPath('marketplace/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('marketplace/sellergroup/edit', ['id' => $id]);
            }
        }
        return $resultRedirect->setPath('marketplace/sellergroup');
    }
}
