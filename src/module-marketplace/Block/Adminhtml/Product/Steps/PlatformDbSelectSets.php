<?php

namespace TCGCollective\MarketPlace\Block\Adminhtml\Product\Steps;

class PlatformDbSelectSets extends \Magento\Ui\Block\Component\StepsWizard\StepAbstract
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;
    protected $catalogHelper;
    
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \TCGCollective\MarketPlace\Helper\Catalog $catalogHelper
    ) {
        parent::__construct($context);
        $this->coreRegistry = $registry;
        $this->catalogHelper = $catalogHelper;
    }

    /**
     * Get Add new Attribute button
     *
     * @param string $dataProvider
     * @return string
     */
    public function getAddNewSetsButton($dataProvider = '')
    {
        /** @var \Magento\Backend\Block\Widget\Button $attributeCreate */
        $attributeCreate = $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Button::class
        );
        // if ($attributeCreate->getAuthorization()->isAllowed('Magento_Catalog::attributes_attributes')) {
            // $attributeCreate->setDataAttribute(
            //     [
            //         'mage-init' => [
            //             'legoSets' => [
            //                 'dataProvider' => $dataProvider,
            //                 'url' => $this->getUrl('productattributeslink/sportcardsets/new', [
            //                     'store' => $this->coreRegistry->registry('current_product')->getStoreId(),
            //                     'popup' => 1,
            //                     // '_query' => [
            //                     //     'attribute' => [
            //                     //         'is_global' => 1,
            //                     //         'frontend_input' => 'select',
            //                     //     ],
            //                     // ],
            //                 ]),
            //             ],
            //         ],
            //     ]
            // )->setType(
            //     'button'
            // )->setLabel(
            //     __('Add New Sets')
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
        return __('Select Sets');
    }
}
