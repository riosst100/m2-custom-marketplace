<?php

namespace TCGCollective\MarketplaceConfigurableProduct\Block\Adminhtml\Product\Steps;

/**
 * Adminhtml block for fieldset of configurable product
 *
 * @api
 * @since 100.0.2
 */
class SelectSets extends \Magento\Ui\Block\Component\StepsWizard\StepAbstract
{
    /**
     * Get Add new Attribute button
     *
     * @param string $dataProvider
     * @return string
     */
    public function getAddNewAttributeButton($dataProvider = '')
    {
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getCaption()
    {
        return __('Select Sets');
    }
}
