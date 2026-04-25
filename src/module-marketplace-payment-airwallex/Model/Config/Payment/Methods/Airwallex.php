<?php

namespace TCGCollective\MarketplacePaymentAirwallex\Model\Config\Payment\Methods;

use Magento\Framework\UrlInterface;

class Airwallex extends \TCGCollective\MarketPlace\Model\Config\Payment\Methods\AbstractModel
{
    /**
     * @var string
     */
    protected $_code = 'airwallex';

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
        $fields['active'] = [
            'type' => 'hidden',
            'required' => false,
            // 'values' => [
            //     ['label' => __('No'), 'value' => 0],
            //     ['label' => __('Yes'), 'value' => 1]
            // ]
            'values' => 0
        ];

        $fields['client_id'] = [
            'type' => 'text',
            'required' => true,
            'values' => '',
        ];

        $fields['api_key'] = [
            'type' => 'text',
            'required' => true,
            'values' => '',
        ];

        $fields['webhook_secret_key'] = [
            'type' => 'text',
            'required' => true,
            'values' => '',
        ];

        $fields['test_success_payment_url'] = [
            'type' => 'text',
            'required' => false,
            'values' => $this->urlBuilder->getUrl('sellerairwallex/testpayment/success'),
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
                return __('Airwallex Payment Integration');
            case 'active':
                return __('Enabled');
            case 'client_id':
                return __('Client ID');
            case 'api_key':
                return __('API key');
            case 'webhook_secret_key':
                return __('Webhook Secret key');
            default:
                return parent::getLabel($key);
        }
    }
}
