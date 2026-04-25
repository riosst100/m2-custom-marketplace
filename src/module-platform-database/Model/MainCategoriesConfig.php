<?php

namespace TCGCollective\PlatformDatabase\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DataObject\IdentityInterface;

class MainCategoriesConfig extends AbstractModel implements IdentityInterface
{
    const CACHE_TAG = 'marketplace_maincategories_config';

    protected $_cacheTag = self::CACHE_TAG;
    protected $_eventPrefix = self::CACHE_TAG;

    protected function _construct()
    {
        $this->_init(
            \TCGCollective\PlatformDatabase\Model\ResourceModel\MainCategoriesConfig::class
        );
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /* =========================
     * JSON DECODERS
     * ========================= */

    public function getProductCreationSetsRows(): array
    {
        return $this->decodeJson('product_creation_sets_rows');
    }

    public function getProductCreationSinglesRows(): array
    {
        return $this->decodeJson('product_creation_singles_rows');
    }

    public function getProductCreationReleaseRows(): array
    {
        return $this->decodeJson('product_creation_release_rows');
    }

    public function getProductCreationSealedProductsRows(): array
    {
        return $this->decodeJson('product_creation_sealedproducts_rows');
    }

    private function decodeJson(string $field): array
    {
        $value = $this->getData($field);
        return $value ? json_decode($value, true) : [];
    }

    /* =========================
     * EXPORT CONFIG (SAMA SEPERTI HARDCODE LAMA)
     * ========================= */

    public function toConfigArray(): array
    {
        return [
            'categoryId' => $this->getData('category_id'),
            'entityCode' => $this->getData('entity_code'),
            'setsTableName' => $this->getData('sets_table_name'),
            'sealedProductsTableName' => $this->getData('sealedproducts_table_name'),
            'singlesTableName' => $this->getData('singles_table_name'),
            'customAttributesTableName' => $this->getData('customattributes_table_name'),

            'mainAttrCode' => $this->getData('main_attr_code'),
            'mainProductTypeAttrCode' => $this->getData('main_product_type_attr_code'),
            'mainSetsAttrCode' => $this->getData('main_sets_attr_code'),
            'mainSetsNumberAttrCode' => $this->getData('main_sets_number_attr_code'),
            'mainSinglesAttrCode' => $this->getData('main_singles_attr_code'),
            'mainSinglesNumberAttrCode' => $this->getData('main_singles_number_attr_code'),

            'productCreationDatabaseAttrCode' => $this->getData('product_creation_database_attr_code'),
            'productCreationDatabaseScope' => $this->getData('product_creation_database_scope'),

            'productCreationSetsRows' => $this->getProductCreationSetsRows(),
            'productCreationSinglesRows' => $this->getProductCreationSinglesRows(),
            'productCreationReleaseRows' => $this->getProductCreationReleaseRows(),
            'productCreationSealedProductsRows' => $this->getProductCreationSealedProductsRows(),

            // Custom Table Tabs
            'platformDbTabs' => $this->getData('platform_db_tabs')
        ];
    }
}
