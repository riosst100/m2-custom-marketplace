<?php

namespace TCGCollective\MarketplaceConfigurableProduct\Block\Adminhtml\Product\Steps;

/**
 * Adminhtml block for fieldset of configurable product
 *
 * @api
 * @since 100.0.2
 */
class SelectSealedProducts extends \Magento\Ui\Block\Component\StepsWizard\StepAbstract
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry
    ) {
        parent::__construct($context);
        $this->coreRegistry = $registry;
    }

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
        return __('Select Sealed Products');
    }
}
