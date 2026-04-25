<?php
namespace TCGCollective\MarketPlace\Block\Adminhtml\Form\Field;

use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;

class StatusColumn extends Select
{
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    public function _toHtml(): string
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->getStatus());
        }
        return parent::_toHtml();
    }

    public function getStatus()
    {
        $options = [
            [
                'label' => __('Enable'),
                'value' => 1
            ],
            [
                'label' => __('Disable'),
                'value' => 0
            ]
        ];

        return $options;
    }
}