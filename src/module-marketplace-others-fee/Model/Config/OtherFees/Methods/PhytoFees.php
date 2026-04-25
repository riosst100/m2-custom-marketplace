<?php

namespace TCGCollective\MarketplaceOthersFee\Model\Config\OtherFees\Methods;

use Magento\Framework\UrlInterface;

class PhytoFees extends \TCGCollective\MarketplaceOthersFee\Model\Config\OtherFees\Methods\AbstractModel
{
    /**
     * @var string
     */
    protected $_code = 'phyto_fees';

    /**
     * @var array
     */
    protected $_fields = [];

    /**
     * @var string
     */
    protected $_codeSeparator = '-';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Directory\Model\Config\Source\CountryFactory
     */
    protected $_countryFactory;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Directory\Model\Config\Source\CountryFactory $countryFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param UrlInterface $urlBuilder
     * @param array $data
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Directory\Model\Config\Source\CountryFactory $countryFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        UrlInterface $urlBuilder,
        array $data = []
    ) {
        $this->_storeManager = $storeManager;
        $this->_countryFactory = $countryFactory;
        $this->_scopeConfig = $scopeConfig;
        $this->_objectManager = $objectManager;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @return array
     */
    public function getFields()
    {
        $fields['amount'] = [
            'type' => 'text',
            'required' => false,
            'values' => '',
        ];

        $fields['no_fees_minimum_subtotal'] = [
            'type' => 'text',
            'required' => false,
            'values' => '',
        ];

        return $fields;
    }

    /**
     * @param $key
     * @return \Magento\Framework\Phrase
     */
    public function getLabel($key)
    {
        switch ($key) {
            case 'label':
                return __('Phyto Fees');
            case 'amount':
                return __('Fee Amount');
            case 'no_fees_minimum_subtotal':
                return __('Minimum Subtotal For Free / No Fees');
            default:
                return parent::getLabel($key);
        }
    }
}
