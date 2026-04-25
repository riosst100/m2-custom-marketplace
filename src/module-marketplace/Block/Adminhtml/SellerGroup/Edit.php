<?php

namespace TCGCollective\MarketPlace\Block\Adminhtml\SellerGroup;

use Magento\Backend\Block\Widget\Form\Container;

class Edit extends Container
{
    protected function _construct()
    {
        $this->_blockGroup = 'TCGCollective_MarketPlace';
        $this->_controller = 'adminhtml_sellerGroup';
        $this->_mode = 'edit';

        parent::_construct();

        $this->buttonList->update('save', 'label', __('Save Seller Group'));
        $this->buttonList->update('delete', 'label', __('Delete Seller Group'));
    }
}
