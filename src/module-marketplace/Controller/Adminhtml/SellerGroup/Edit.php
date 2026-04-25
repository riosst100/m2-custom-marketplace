<?php

namespace TCGCollective\MarketPlace\Controller\Adminhtml\SellerGroup;

use Magento\Backend\App\Action;

class Edit extends \TCGCollective\MarketPlace\Controller\Adminhtml\SellerGroup
{
    /**
     * Edit customer group action. Forward to new action.
     *
     * @return \Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {
        return $this->resultForwardFactory->create()->forward('new');
    }
}
