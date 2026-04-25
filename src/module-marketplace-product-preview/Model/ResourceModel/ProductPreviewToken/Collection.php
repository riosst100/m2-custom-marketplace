<?php

namespace TCGCollective\MarketplaceProductPreview\Model\ResourceModel\ProductPreviewToken;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';
    
    /**
     * Dependency Initilization
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(
            \TCGCollective\MarketplaceProductPreview\Model\ProductPreviewToken::class,
            \TCGCollective\MarketplaceProductPreview\Model\ResourceModel\ProductPreviewToken::class
        );
        $this->_map['fields']['entity_id'] = 'main_table.entity_id';
    }
}