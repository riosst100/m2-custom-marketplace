<?php
/**
 * Landofcoder
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * https://landofcoder.com/terms
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category   Landofcoder
 * @package    Lof_Formbuilder
 * @copyright  Copyright (c) 2021 Landofcoder (https://www.landofcoder.com/)
 * @license    https://landofcoder.com/terms
 */

namespace TCGCollective\MarketPlace\Block\Adminhtml\ProductListingConfigurations\Edit\Tab;

use Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Email\Model\ResourceModel\Template\CollectionFactory;
use Magento\Email\Model\Template\Config;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Convert\DataObject as ObjectConverter;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Store\Model\System\Store;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Framework\Option\ArrayInterface;

class CategoryConfiguration extends Generic implements TabInterface
{
    /**
     * @var Store
     */
    protected Store $systemStore;

    /**
     * @var GroupRepositoryInterface
     */
    protected GroupRepositoryInterface $groupRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @var ObjectConverter
     */
    protected ObjectConverter $objectConverter;
    protected CollectionFactory $templatesFactory;
    protected Config $emailConfig;
    protected CategoryCollectionFactory $categoryCollectionFactory;

    /**
     * Main constructor.
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param GroupRepositoryInterface $groupRepository
     * @param ObjectConverter $objectConverter
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Store $systemStore
     * @param CollectionFactory $templatesFactory
     * @param Config $emailConfig
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        GroupRepositoryInterface $groupRepository,
        ObjectConverter $objectConverter,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Store $systemStore,
        CollectionFactory $templatesFactory,
        Config $emailConfig,
        CategoryCollectionFactory $categoryCollectionFactory,
        array $data = []
    ) {
        $this->systemStore = $systemStore;
        $this->groupRepository = $groupRepository;
        $this->objectConverter = $objectConverter;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->templatesFactory = $templatesFactory;
        $this->emailConfig = $emailConfig;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function getCategoryOptions()
{
    $collection = $this->categoryCollectionFactory->create();
    $collection->addAttributeToSelect(['name', 'parent_id'])
        ->addIsActiveFilter()
        ->addFieldToFilter('level', 3);

    // Ambil parent ids
    $parentIds = array_unique($collection->getColumnValues('parent_id'));

    $parentCollection = $this->categoryCollectionFactory->create();
    $parentCollection->addAttributeToSelect('name')
        ->addFieldToFilter('entity_id', ['in' => $parentIds]);

    $parents = [];
    foreach ($parentCollection as $parent) {
        $parents[$parent->getId()] = $parent->getName();
    }

    $grouped = [];

    foreach ($collection as $category) {
        $parentName = $parents[$category->getParentId()] ?? '';

        $grouped[$parentName][] = [
            'label' => $parentName . ' - ' . $category->getName(),
            'value' => $category->getId(),
            'child_name' => $category->getName()
        ];
    }

    // Sort parent (L2)
    ksort($grouped, SORT_NATURAL | SORT_FLAG_CASE);

    $options = [];

    $options[] = [
        'label' => '--Select Category--',
        'value' => ''
    ];

    foreach ($grouped as $parentName => $children) {

        // Sort child (L3)
        usort($children, function ($a, $b) {
            return strcasecmp($a['child_name'], $b['child_name']);
        });

        foreach ($children as $child) {
            unset($child['child_name']);
            $options[] = $child;
        }
    }

    return $options;
}

    /**
     * @inheritdoc
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('product_listing_configuration_form');

        if ($this->isAllowedAction('Lof_Formbuilder::form_edit')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }
        $this->_eventManager->dispatch(
            'lof_check_license',
            ['obj' => $this,'ex' => 'Lof_Formbuilder']
        );
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('form_');
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Form Information')]);
        // if ($model->getId()) {
        //     $fieldset->addField('form_id', 'hidden', ['name' => 'form_id']);
        // }

        $fieldset->addField(
            'category_id',
            'select',
            [
                'name'     => 'category_id',
                'label'    => __('Category'),
                'title'    => __('Category'),
                'required' => true,
                'values'   => $this->getCategoryOptions(),
                'disabled' => $isElementDisabled
            ]
        );

        // $element = $fieldset->addField(
        //     'product_attributes',
        //     'note',
        //     [
        //         'name' => 'product_attributes',
        //     ]
        // );

        // $renderer = $this->getLayout()->createBlock(
        //     \TCGCollective\MarketPlace\Block\Adminhtml\ProductListingConfigurations\Edit\Tab\Renderer\Attributes::class
        // );

        // $element->setRenderer($renderer);
        $fieldset->addField(
    'product_attributes',
    'note',
    [
        'label' => __('Product Attributes'),
        'name'  => 'product_attributes'
    ]
)->setRenderer(
    $this->getLayout()->createBlock(
        \TCGCollective\MarketPlace\Block\Adminhtml\ProductListingConfigurations\Edit\Tab\Renderer\Attributes::class
    )
);

        $fieldset->addField(
            'attributes_js',
            'note',
            [
                'text' => '
                    <script>
                    require(["jquery"], function($){
                        $(document).ready(function(){

                            function toggleAttributes(){
                                var category = $("#form_category_id").val();

                                if(category && category !== ""){
                                    $(".field-product_attributes_grid").show();
                                } else {
                                    $(".field-product_attributes_grid").hide();
                                }
                            }

                            toggleAttributes();

                            $("#form_category_id").on("change", function(){
                                toggleAttributes();
                            });

                        });
                    });
                    </script>
                '
            ]
        );

        if ($model) {
            $form->setValues($model->getData());
        }

        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return Phrase
     */
    public function getTabLabel()
    {
        return __('Form Information');
    }

    /**
     * Prepare title for tab
     *
     * @return Phrase
     */
    public function getTabTitle()
    {
        return __('Form Information');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden(): bool
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function isAllowedAction(string $resourceId): bool
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}
