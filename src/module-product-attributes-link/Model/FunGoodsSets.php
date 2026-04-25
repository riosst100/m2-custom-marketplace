<?php

namespace TCGCollective\ProductAttributesLink\Model;

class FunGoodsSets extends \Magento\Framework\Model\AbstractModel implements \Magento\Framework\DataObject\IdentityInterface
{
    const NOROUTE_ENTITY_ID = 'no-route';
    const ENTITY_ID = 'entity_id';
    const CACHE_TAG = 'marketplace_fungoods_sets';
    
    /**
     * @var string
     */
    protected $_cacheTag = 'marketplace_fungoods_sets';

    /**
     * @var string
     */
    protected $_eventPrefix = 'marketplace_fungoods_sets';
    
    /**
     * Dependency Initilization
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(\TCGCollective\ProductAttributesLink\Model\ResourceModel\FunGoodsSets::class);
    }
    
    public function load($id, $field = null)
    {
        if ($id === null) {
            return $this->noRoute();
        }
        return parent::load($id, $field);
    }
    
    public function noRoute()
    {
        return $this->load(self::NOROUTE_ENTITY_ID, $this->getIdFieldName());
    }
    
    public function getIdentities()
    {
        return [self::CACHE_TAG.'_'.$this->getId()];
    }
    
    public function getId()
    {
        return parent::getData(self::ENTITY_ID);
    }

    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }
}