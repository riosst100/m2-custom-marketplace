<?php

namespace TCGCollective\MarketPlace\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Catalog\Model\Product;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Eav\Api\AttributeGroupRepositoryInterface;
use Magento\Eav\Api\Data\AttributeGroupInterfaceFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;

class AddSellerSkuAttribute implements DataPatchInterface
{
    private $moduleDataSetup;
    private $eavSetupFactory;
    private $eavConfig;
    private $attributeSetRepository;
    private $attributeGroupRepository;
    private $attributeGroupFactory;
    private $searchCriteriaBuilder;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory,
        EavConfig $eavConfig,
        AttributeSetRepositoryInterface $attributeSetRepository,
        AttributeGroupRepositoryInterface $attributeGroupRepository,
        AttributeGroupInterfaceFactory $attributeGroupFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
        $this->eavConfig = $eavConfig;
        $this->attributeSetRepository = $attributeSetRepository;
        $this->attributeGroupRepository = $attributeGroupRepository;
        $this->attributeGroupFactory = $attributeGroupFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        // Add the attribute
        $eavSetup->addAttribute(
            Product::ENTITY,
            'seller_sku',
            [
                'type' => 'varchar',
                'label' => 'SKU',
                'input' => 'text',
                'required' => true,
                'sort_order' => 3,
                'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'user_defined' => true,
                'group' => 'Product Details',
                'visible_on_front' => true,
                'is_used_in_grid' => true,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
                'searchable' => true,
                'filterable' => true,
                'comparable' => true,
                'used_in_product_listing' => true,
                'unique' => false,
            ]
        );

        // Assign the attribute to all existing attribute sets
        $attribute = $this->eavConfig->getAttribute(Product::ENTITY, 'seller_sku');
        $searchCriteria = $this->searchCriteriaBuilder->create();
        $attributeSets = $this->attributeSetRepository->getList($searchCriteria);

        foreach ($attributeSets->getItems() as $attributeSet) {
            try {
                $groupId = $eavSetup->getAttributeGroupId(Product::ENTITY, $attributeSet->getAttributeSetId(), 'Product Details');
                $eavSetup->addAttributeToGroup(
                    Product::ENTITY,
                    $attributeSet->getAttributeSetId(),
                    $groupId,
                    $attribute->getAttributeId()
                );
            } catch (\Exception $e) {
                // Log or handle if needed
            }
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }
}
