<?php

namespace TCGCollective\MarketplacePaymentXendit\Model\Payment;

use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\OrderRepository;
use Xendit\M2Invoice\Logger\Logger as XenditLogger;
use Magento\Quote\Api\Data\CartInterface;

/**
 * Class Xendit
 * @package Xendit\M2Invoice\Model\Payment
 */
class Xendit extends \Xendit\M2Invoice\Model\Payment\Xendit
{
    /**
     * Payment code
     *
     * @var string
     */
    protected $_code = 'xendit';

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var XenditLogger
     */
    private $xenditLogger;

    protected $marketplaceData;

    protected $marketplaceConfig;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory
     * @param \Magento\Payment\Helper\Data $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Payment\Model\Method\Logger $logger
     * @param DirectoryHelper $directory
     * @param XenditLogger $xenditLogger
     * @param OrderRepository $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        DirectoryHelper $directory,
        XenditLogger $xenditLogger,
        OrderRepository $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        \Lof\MarketPlace\Helper\Data $marketplaceData,
        \Lof\MarketPlace\Model\ConfigFactory $marketplaceConfig
    ) {
        $this->xenditLogger = $xenditLogger;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->orderRepository = $orderRepository;
        $this->marketplaceData = $marketplaceData;
        $this->marketplaceConfig = $marketplaceConfig;

        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $directory,
            $xenditLogger,
            $orderRepository,
            $searchCriteriaBuilder
        );
    }

    /**
     * @return mixed
     */
    public function getApiKey()
    {
        if ($this->isLive()) {
            return $this->getConfigData('private_key');
        } else {
            return $this->getConfigData('test_private_key');
        }
    }

    /**
     * @return mixed
     */
    public function getPublicApiKey()
    {
        if ($this->isLive()) {
            return $this->getConfigData('public_key');
        } else {
            return $this->getConfigData('test_public_key');
        }
    }

    /**
     * @return bool
     */
    public function isLive()
    {
        $xenditEnv = $this->getConfigData('xendit_env');

        if ('live' == $xenditEnv) {
            return true;
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function getEnvironment()
    {
        return $this->getConfigData('xendit_env');
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->getConfigData('xendit_url');
    }

    /**
     * @return mixed
     */
    public function getUiUrl()
    {
        return $this->getConfigData('ui_url');
    }

    /**
     * @return mixed
     */
    public function getAllowedMethod()
    {
        return $this->getConfigData('allowed_method');
    }

    /**
     * @return mixed
     */
    public function getChosenMethods()
    {
        return $this->getConfigData('chosen_methods');
    }

    /**
     * @return mixed
     */
    public function getIsActive()
    {
        return $this->getConfigData('active');
    }

    /**
     * @return array
     */
    public function getEnabledPromo()
    {
        $promo = [];
        $bankCodes = [
            'bca',
            'bni',
            'bri',
            'cimb',
            'citibank',
            'danamon',
            'dbs',
            'hsbc',
            'mandiri',
            'maybank',
            'mega',
            'mnc',
            'permata',
            'sc',
            'uob',
        ];

        foreach ($bankCodes as $bankCode) {
            if ($this->getConfigData('card_promo_' . $bankCode . '_active')) {
                $binListCandidate = explode(',', $this->getConfigData('card_promo_' . $bankCode . '_bin_list'));
                $binList = $result = array_filter(
                    $binListCandidate,
                    function ($value) {
                        return strlen($value) === 6;
                    }
                );
                $promo[] = [
                    'rule_id' => $this->getConfigData('card_promo_' . $bankCode . '_rule'),
                    'bin_list' => $binList
                ];
            }
        }

        return $promo;
    }

    /**
     * Get order(s) by TransactionId
     *
     * @param string $transactionId
     * @return array
     */
    public function getOrderIdsByTransactionId(string $transactionId): array
    {
        $this->searchCriteriaBuilder->addFilter('xendit_transaction_id', $transactionId);
        $orders = $this->orderRepository->getList($this->searchCriteriaBuilder->create());

        if (!$orders->getTotalCount()) {
            $this->xenditLogger->log('error', __('No order(s) found for transaction id %1', $transactionId));
            return [];
        }

        return array_map(function (OrderInterface $order) {
            return $order->getId();
        }, $orders->getItems());
    }

    /**
     * @param string $incrementId
     * @return OrderInterface
     * @throws NoSuchEntityException
     */
    public function getOrderByIncrementId(string $incrementId): OrderInterface
    {
        $searchCriteria = $this->searchCriteriaBuilder->addFilter(
            'main_table.' . OrderInterface::INCREMENT_ID,
            $incrementId
        )->create();

        if (!($orderItems = $this->orderRepository->getList($searchCriteria)->getItems())) {
            throw new NoSuchEntityException(__('Requested order doesn\'t exist'));
        }

        return reset($orderItems);
    }

    /**
     * @param CartInterface|null $quote
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isAvailable(CartInterface $quote = null)
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/payment.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        // $logger->info('xendit isavailable override?'); 

        $quotes = [];
        foreach ($quote->getAllItems() as $item) {
            $product = $item->getProduct()->load($item->getProductId());
            if ($item->getParentItem() || $product->isVirtual()) {
                continue;
            }

            // Identify seller
            $sellerId = $item->getSellerId() ?: $product->getSellerId() ?: 'no_seller';            
            if (!isset($quotes[$sellerId])) {
                $quotes[$sellerId] = [];
            }
            $quotes[$sellerId][] = $item;
        }

        $config = null;

        // Process each seller’s items        
        foreach ($quotes as $sellerId => $items) {            
            // $logger->info("Check xendit for seller: $sellerId");
            try {
                $key = 'payment/xendit/active';
                $keyTable = $this->marketplaceData->getTableKey('key');
                $sellerKeyTable = $this->marketplaceData->getTableKey('seller_id');
                $config = $this->marketplaceConfig->create()
                    ->loadByField([$keyTable, $sellerKeyTable], [$key, $sellerId])->getValue();
            } catch (\Exception $e) {
                $config = 0;
            }
        }
        if ($config) {
            return true;
        } else {
            return false;
        }        
    }
}
