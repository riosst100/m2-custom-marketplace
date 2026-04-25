<?php

namespace TCGCollective\MarketplaceRajaOngkir\Helper;

use Lof\MarketPlace\Model\SellerFactory;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $_customerSession;

    /**
     * @var SellerFactory
     */
    protected $sellerFactory;

    /**
     * @var \Lof\MarketPlace\Helper\Data
     */
    protected $marketplaceData;

    /**
     * @var \Lof\MarketPlace\Model\ConfigFactory
     */
    protected $marketplaceConfig;

    /**
     * @var array
     */
    protected $_seller = [];

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Customer\Model\SessionFactory $customerSession
     * @param SellerFactory $sellerFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Customer\Model\SessionFactory $customerSession,
        SellerFactory $sellerFactory,
        \Lof\MarketPlace\Helper\Data $marketplaceData,
        \Lof\MarketPlace\Model\ConfigFactory $marketplaceConfig
    ) {
        parent::__construct($context);
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_customerSession = $customerSession;
        $this->sellerFactory = $sellerFactory;
        $this->marketplaceData = $marketplaceData;
        $this->marketplaceConfig = $marketplaceConfig;
    }

    /**
     * @return \Lof\MarketPlace\Model\Seller|mixed|null
     */
    public function getSeller()
    {
        $customerId = $this->_customerSession->create()->getCustomerId();
        if ($customerId && !isset($this->_seller[$customerId])) {
            $this->_seller[$customerId] = $this->sellerFactory->create()->load($customerId, 'customer_id');
            if ($this->_seller[$customerId]->getId() && $this->_seller[$customerId]->getStatus() == 0) { //need approval
                $this->_seller[$customerId] = null;
            }
        }
        return $this->_seller[$customerId];
    }

    /**
     * @return array|mixed|null
     */
    public function getSellerByCustomer()
    {
        $seller = $this->getSeller();
        return $seller ? $seller->getData() : null;
    }

    /**
     * get shipping is enabled or not for system config.
     */
    public function getFlatRateShippingEnabled()
    {
        return $this->_scopeConfig->getValue(
            'carriers/raajaongkir/active',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * get multi shipping is enabled or not for system config.
     */
    public function getMpmultishippingEnabled()
    {
        return $this->_scopeConfig->getValue(
            'carriers/mp_multi_shipping/active',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * get table rate shipping title from system config.
     */
    public function getFlatRateShippingTitle()
    {
        return $this->_scopeConfig->getValue(
            'carriers/raajaongkir/title',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * get table rate shipping name from system config.
     */
    public function getFlatRateShippingName()
    {
        return $this->_scopeConfig->getValue(
            'carriers/raajaongkir/name',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * get table rate shipping allow admin settings from system config.
     */
    public function getFlatRateShippingAllowadmin()
    {
        return $this->_scopeConfig->getValue(
            'carriers/raajaongkir/allowadmin',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return int|mixed
     */
    public function getPartnerId()
    {
        $seller = $this->getSeller();
        return $seller ? $seller->getId() : 0;
    }

    /**
     * @param $sellerId
     * @return bool
     */
    public function isSellerEnabled($sellerId)
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/shipping.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);        

        try {
            $key = 'shipping/rajaongkir/active';
            $keyTable = $this->marketplaceData->getTableKey('key');
            $sellerKeyTable = $this->marketplaceData->getTableKey('seller_id');
            $config = $this->marketplaceConfig->create()
                ->loadByField([$keyTable, $sellerKeyTable], [$key, $sellerId])->getValue();            
            return $config == null ? true : !!$config;
        } catch (\Exception $e) {
            return true;
        }
    }

    public function getApiKey($sellerId)
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/shipping.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);        

        try {
            $key = 'shipping/rajaongkir/api_key';
            $keyTable = $this->marketplaceData->getTableKey('key');
            $sellerKeyTable = $this->marketplaceData->getTableKey('seller_id');
            $config = $this->marketplaceConfig->create()
                ->loadByField([$keyTable, $sellerKeyTable], [$key, $sellerId])->getValue();            
            return $config;
        } catch (\Exception $e) {
            return true;
        }
    }

    public function getSellerOriginPostcode($sellerId)
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/shipping.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);        

        try {
            $key = 'shipping/address/postcode';
            $keyTable = $this->marketplaceData->getTableKey('key');
            $sellerKeyTable = $this->marketplaceData->getTableKey('seller_id');
            $config = $this->marketplaceConfig->create()
                ->loadByField([$keyTable, $sellerKeyTable], [$key, $sellerId])->getValue();            
            return $config;
        } catch (\Exception $e) {
            return true;
        }
    }

    public function getSellerOriginCity($sellerId)
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/shipping.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);        

        try {
            $key = 'shipping/address/city';
            $keyTable = $this->marketplaceData->getTableKey('key');
            $sellerKeyTable = $this->marketplaceData->getTableKey('seller_id');
            $config = $this->marketplaceConfig->create()
                ->loadByField([$keyTable, $sellerKeyTable], [$key, $sellerId])->getValue();            
            return $config;
        } catch (\Exception $e) {
            return true;
        }
    }

    public function getSellerOriginRegion($sellerId)
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/shipping.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);        

        try {
            $key = 'shipping/address/region';
            $keyTable = $this->marketplaceData->getTableKey('key');
            $sellerKeyTable = $this->marketplaceData->getTableKey('seller_id');
            $config = $this->marketplaceConfig->create()
                ->loadByField([$keyTable, $sellerKeyTable], [$key, $sellerId])->getValue();            
            return $config;
        } catch (\Exception $e) {
            return true;
        }
    }
}
