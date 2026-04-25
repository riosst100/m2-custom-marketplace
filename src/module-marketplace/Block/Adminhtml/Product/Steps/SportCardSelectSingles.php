<?php

namespace TCGCollective\MarketPlace\Block\Adminhtml\Product\Steps;

/**
 * Adminhtml block for fieldset of configurable product
 *
 * @api
 * @since 100.0.2
 */
class SportCardSelectSingles extends \Magento\Ui\Block\Component\StepsWizard\StepAbstract
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
    public function getAddNewSinglesButton($dataProvider = '')
    {
        /** @var \Magento\Backend\Block\Widget\Button $attributeCreate */
        $attributeCreate = $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Button::class
        );
        // if ($attributeCreate->getAuthorization()->isAllowed('Magento_Catalog::attributes_attributes')) {
            // $attributeCreate->setDataAttribute(
            //     [
            //         'mage-init' => [
            //             'cardGameSingles' => [
            //                 'dataProvider' => $dataProvider,
            //                 'url' => $this->getUrl('productattributeslink/sportcardsingles/new', [
            //                     'store' => $this->coreRegistry->registry('current_product')->getStoreId(),
            //                     'product_tab' => 'variations',
            //                     'popup' => 1,
            //                     '_query' => [
            //                         'attribute' => [
            //                             'is_global' => 1,
            //                             'frontend_input' => 'select',
            //                         ],
            //                     ],                            
            //                 ]),
            //             ],
            //         ],
            //     ]
            // )->setType(
            //     'button'
            // )->setLabel(
            //     __('Add New Singles')
            // );
            // return $attributeCreate->toHtml();
        // } else {
            return '';
        // }
    }

    /**
     * {@inheritdoc}
     */
    public function getCaption()
    {
        return __('Select Singles');
    }
}
