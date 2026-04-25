<?php

namespace TCGCollective\MarketPlace\Block\Adminhtml\Template\Grid\Filter;

use Magento\Backend\Block\Widget\Grid\Column\Filter\Select;

class EmailReceiver extends Select
{
    /**
     * Get filter options
     *
     * @return array
     */
    protected function _getOptions()
    {
        return [
            ['label' => __('All'),    'value' => ''],
            ['label' => __('Admin'),  'value' => 'admin'],
            ['label' => __('Seller'), 'value' => 'seller'],
        ];
    }

    /**
     * Apply filter condition to collection
     *
     * @return $this
     */
    public function getCondition()
    {
        $value = $this->getValue();

        if ($value === null || $value === '') {
            return null;
        }

        /**
         * Support both numeric & string values
         */
        if ($value === 'admin') {
            return ['in' => ['admin']];
        }

        if ($value === 'seller') {
            return ['in' => ['seller']];
        }

        return null;
    }
}
