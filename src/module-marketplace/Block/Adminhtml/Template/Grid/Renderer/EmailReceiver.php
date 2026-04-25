<?php
namespace TCGCollective\MarketPlace\Block\Adminhtml\Template\Grid\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;

class EmailReceiver extends AbstractRenderer
{
    public function render(DataObject $row)
    {
        $value = $row->getData($this->getColumn()->getIndex());

        return match ((string)$value) {
            'admin'  => __('Admin'),
            'seller' => __('Seller'),
            default       => __('—'),
        };
    }
}
