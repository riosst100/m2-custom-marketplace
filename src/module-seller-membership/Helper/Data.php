<?php

namespace TCGCollective\SellerMembership\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Catalog\Model\ProductCategoryList;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\App\ObjectManager;

class Data extends \Lofmp\SellerMembership\Helper\Data
{
    protected $priceHelper;
    protected $_customerSession;


    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_dateTime;

    /**
     * @var TimezoneInterface
     */
    protected $_timezoneInterface;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customer;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $priceFormatter;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productCollectionFactory;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    protected $membershipAddonFactory;

    protected $membershipAddonTransactionFactory;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param TimezoneInterface $timezoneInterface
     * @param PriceHelper $priceHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\ProductFactory $productCollectionFactory
     * @param \Magento\Customer\Model\CustomerFactory $customer
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceFormatter
     * @param CustomerSession $customerSession
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        TimezoneInterface $timezoneInterface,
        PriceHelper $priceHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ProductFactory $productCollectionFactory,
        \Magento\Customer\Model\CustomerFactory $customer,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceFormatter,
        CustomerSession $customerSession,
        \TCGCollective\SellerMembership\Model\MembershipAddonFactory $membershipAddonFactory,
        \TCGCollective\SellerMembership\Model\AdditionalTransactionFactory $membershipAddonTransactionFactory,
        SerializerInterface $serializer = null
    ) {
        $this->_storeManager = $storeManager;
        $this->_dateTime = $dateTime;
        $this->_timezoneInterface = $timezoneInterface;
        $this->priceHelper = $priceHelper;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->_customerSession = $customerSession;
        $this->customer = $customer;
        $this->priceFormatter = $priceFormatter;
        $this->membershipAddonFactory = $membershipAddonFactory;
        $this->membershipAddonTransactionFactory = $membershipAddonTransactionFactory;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(SerializerInterface::class);
        parent::__construct(
            $context,
            $dateTime,
            $timezoneInterface,
            $priceHelper,
            $storeManager,
            $productCollectionFactory,
            $customer,
            $priceFormatter,
            $customerSession
        );
    }

    public function setCurrentCurrencyCode($currencyCode) {
        $store = $this->_storeManager->getStore();
        // dd(get_class_methods($store));
        // $store->setBaseCurrencyCode('USD');
        // dd($store->getBaseCurrencyCode());
        $store->setCurrentCurrencyCode($currencyCode);
        // dd($store->getCurrentCurrencyCode());
        // $store->setCurrentCurrencyCode($currencyCode);
    }

    public function getMembershipDurationTime($orderItem, $duration, $durationUnit) 
    {
        $time = '';
        $duration = $duration * ($orderItem->getQty() ?? 1); // Custom Fix qty is 0

        switch ($durationUnit) {
            case 'day':
                $time = "+$duration days";
                break;
            case 'week':
                $duration = $duration * 7;
                $time = "+$duration days";
                break;
            case 'year':
                $time = "+$duration years";
                break;
            case 'month':
            default:
                $time = "+$duration months";
                break;
        }

        return $time;
    }

    public function getMembershipOption($orderItem) 
    {
        $membershipOption = null;

        $membershipOptionByRequest = $orderItem->getProductOptions();
        if ($membershipOptionByRequest) {
            if (!is_array($membershipOptionByRequest)) {
                $membershipOptionByRequest = $this->serializer->unserialize($membershipOptionByRequest);
            }
            
            if ($membershipOptionByRequest) {
                $membershipOption = $membershipOptionByRequest['info_buyRequest']['seller_membership'] ?? [];
                if (isset($membershipOption['duration'])) {
                    $membershipOption = explode("|", $membershipOption['duration']);
                }
            }
        }

        return $membershipOption;
    }

    public function getMembershipAddon($sellerId, $buyListingWebsiteId) 
    {
        $membershipAddon = $this->membershipAddonFactory->create()
        ->getCollection()
        ->addFieldToFilter('seller_id', $sellerId)
        ->addFieldToFilter('website_id', $buyListingWebsiteId);
        
        if ($membershipAddon->getSize()) {
            return $membershipAddon->getFirstItem();
        }

        return $this->createDefaultMembershipAddon($sellerId);
    }

    public function getMembershipExpirationDate($membershipAddon, $buyListingWebsiteId, $currentSellerListingWebsiteIds, $time) 
    {
        $date = null;

        if (in_array($buyListingWebsiteId, $currentSellerListingWebsiteIds)) {
            /*Renew the current package*/
            $currentTime = $membershipAddon->getData('expiration_date');
            if (!$currentTime || $currentTime == "0000-00-00 00:00:00") {
                $currentTime = $this->getTimezoneDateTime();
            }
        } else {
            /*Upgrade to new package*/
            $currentTime = $this->getTimezoneDateTime();
        }
        
        if ($currentTime) {
            $expiryTime = strtotime($currentTime . $time);
            if ($expiryTime) {
                $date = date('Y-m-d h:i:s A', $expiryTime);
            }
        }
        
        return $date;
    }

    public function getSellerListingWebsiteIds($seller) 
    {
        $listingWebsiteIdsArr = [];

        $currentListingCountry = $seller->getListingWebsiteIds();
        if ($currentListingCountry) {
            $listingWebsiteIdsArr = explode(",", $currentListingCountry);
        }

        return $listingWebsiteIdsArr;
    }

    public function getOrderItemProductOptions($orderItem) 
    {
        $productOptions = $orderItem->getProductOptions();
        $productOptions = is_array($productOptions)?$this->serializer->serialize($productOptions):$productOptions;

        return $productOptions;
    }

    public function processBuyMembershipAddon($orderItem, $product, $seller, $order)
    {
        // Get website id from membership product
        $buyListingWebsiteId = (int)$product->getData('membership_country');
        if (!$buyListingWebsiteId) {
            return;
        }

        $membershipOption = $this->getMembershipOption($orderItem);
        if (!$membershipOption) {
            return;
        }

        $duration = isset($membershipOption[0]) && $membershipOption[0] ? (int)$membershipOption[0] : self::DEFAULT_DURATION_MONTH;
        $durationUnit = isset($membershipOption[1]) && trim($membershipOption[1]) ? trim($membershipOption[1]) : 'month';
        $price = $orderItem->getPrice();
        if (!$duration || !$durationUnit) {
            return;
        }

        $time = $this->getMembershipDurationTime($orderItem, $duration, $durationUnit);
        if (!$time) {
            return;
        }

        $membershipAddon = $this->getMembershipAddon($seller->getId(), $buyListingWebsiteId);
        if (!$membershipAddon) {
            return;
        }

        $currentSellerListingWebsiteIds = $this->getSellerListingWebsiteIds($seller);
        $expDate = $this->getMembershipExpirationDate($membershipAddon, $buyListingWebsiteId, $currentSellerListingWebsiteIds, $time);
        
        try {
            $productOptions = $this->getOrderItemProductOptions($orderItem);

            $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/productOptions2.log');
                $logger = new \Zend_Log();
                $logger->addWriter($writer);
                $logger->info('productOptions: '.print_r($productOptions,true));

            // Update membership addon
            $membershipAddon->setWebsiteId($buyListingWebsiteId);
            $membershipAddon->setSellerId($seller->getId());
            $membershipAddon->setExpirationDate($expDate);
            $membershipAddon->setStatus(\Lofmp\SellerMembership\Model\Membership::DISABLE);
            $membershipAddon->setDuration($duration . ' ' . $durationUnit);
            $membershipAddon->setPrice($price);
            $membershipAddon->setName($orderItem->getName());
            $membershipAddon->setProductId($orderItem->getProductId());
            $membershipAddon->setItemId($orderItem->getId());
            $membershipAddon->setProductOptions($productOptions);
            $membershipAddon->save();

            $trans = $this->membershipAddonTransactionFactory->create();
            $trans->setData([
                'seller_id' => $seller->getId(),
                'package' => $orderItem->getName(),
                'amount' => $orderItem->getBaseRowTotal(),
                'duration' => $duration,
                'duration_unit' => $durationUnit,
                'additional_info' => 'order|' . $order->getEntityId() . '||item|' . $orderItem->getId(),
                'created_at' => $this->_dateTime->timestamp(),
                'website_id' => $buyListingWebsiteId,
                'order_id' => $order->getEntityId(),
                'order_increment_id' => $order->getIncrementId(),
                'product_options' => $productOptions,
                'product_id' => $orderItem->getProductId(),
                'item_id' => $orderItem->getId()
            ]);
            $trans->save();

            $newSellerListingWebsiteIds = $currentSellerListingWebsiteIds;
            if (!in_array($buyListingWebsiteId, $currentSellerListingWebsiteIds)) {
                array_push($newSellerListingWebsiteIds, $buyListingWebsiteId);
            }

            // Custom Set seller data
            if ($newSellerListingWebsiteIds) {
                $seller->setListingWebsiteIds(implode(",", $newSellerListingWebsiteIds));
            }

            $seller->save();
        } catch(\Exception $e) {
            $this->_helperData->writeLog([$e->getMessage()]);
        }
    }

    public function processInvoiceMembershipAddon($orderItem, $product, $seller)
    {
        // Get website id from membership product
        $buyListingWebsiteId = (int)$product->getData('membership_country');
        if (!$buyListingWebsiteId) {
            return;
        }

        $membershipOption = $this->getMembershipOption($orderItem);
        if (!$membershipOption) {
            return;
        }

        $duration = isset($membershipOption[0]) && $membershipOption[0] ? (int)$membershipOption[0] : self::DEFAULT_DURATION_MONTH;
        $durationUnit = isset($membershipOption[1]) && trim($membershipOption[1]) ? trim($membershipOption[1]) : 'month';
        $price = $orderItem->getPrice();
        if (!$duration || !$durationUnit) {
            return;
        }

        $time = $this->getMembershipDurationTime($orderItem, $duration, $durationUnit);
        if (!$time) {
            return;
        }

        $membershipAddon = $this->getMembershipAddon($seller->getId(), $buyListingWebsiteId);
        if (!$membershipAddon) {
            return;
        }

        $currentSellerListingWebsiteIds = $this->getSellerListingWebsiteIds($seller);
        $expDate = $this->getMembershipExpirationDate($membershipAddon, $buyListingWebsiteId, $currentSellerListingWebsiteIds, $time);

        try {
            $productOptions = $this->getOrderItemProductOptions($orderItem);

            // if ($productOptions) {
                // dd($productOptions);
                
                $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/productOptions.log');
                $logger = new \Zend_Log();
                $logger->addWriter($writer);
                $logger->info('productOptions: '.print_r($productOptions,true));

            // }

            //upgrade membership plan
            $membershipAddon->setWebsiteId($buyListingWebsiteId);
            $membershipAddon->setSellerId($seller->getId());
            $membershipAddon->setExpirationDate($expDate);
            $membershipAddon->setStatus(\Lofmp\SellerMembership\Model\Membership::DISABLE);
            $membershipAddon->setDuration($duration . ' ' . $durationUnit);
            $membershipAddon->setPrice($price);
            $membershipAddon->setName($orderItem->getName());
            $membershipAddon->setProductId($orderItem->getProductId());
            $membershipAddon->setItemId($orderItem->getId());
            $membershipAddon->setProductOptions($productOptions);
            $membershipAddon->save();

            //upgrade transaction
            // $this->updateMembershipAddonTransactionData($seller, $orderItem);

            // public function updateMembershipAddonTransactionData($seller, $orderItem) 
            // {
            //     $transCollection = $this->_transactionFactory->create()->getCollection()
            //     ->addFieldToFilter("seller_id", $seller->getId())
            //     ->addFieldtoFilter("item_id", $orderItem->getId())
            //     ->addFieldtoFilter("order_id", $order->getEntityId());

            //     $trans = $this->_transactionFactory->create();
            //     if ($transCollection->count()) {
            //         $transId = $transCollection->getFirstItem()->getData("transaction_id");
            //         $trans = $this->_transactionFactory->create()->load((int)$transId);
            //     }

            //     $trans->setData([
            //         'seller_id' => $seller->getId(),
            //         'package' => $item->getName(),
            //         'amount' => $item->getBaseRowTotal(),
            //         'duration' => $duration,
            //         'duration_unit' => $durationUnit,
            //         'additional_info' => 'invoice|' . $invoice->getId() . '||item|' . $orderItem->getId(),
            //         'created_at' => $this->_dateTime->timestamp(),
            //         'group_id' => $order->getCustomerGroupId(),
            //         'order_id' => $order->getEntityId(),
            //         'order_increment_id' => $order->getIncrementId(),
            //         'product_options' => $productOptions,
            //         'product_id' => $orderItem->getProductId(),
            //         'item_id' => $orderItem->getId()
            //     ]);

            //     $trans->save();
            // }

            $newSellerListingWebsiteIds = $currentSellerListingWebsiteIds;
            if (!in_array($buyListingWebsiteId, $currentSellerListingWebsiteIds)) {
                array_push($newSellerListingWebsiteIds, $buyListingWebsiteId);
            }

            // Custom Set seller data
            if ($newSellerListingWebsiteIds) {
                $seller->setListingWebsiteIds(implode(",", $newSellerListingWebsiteIds));
            }

            $seller->save();
        } catch(\Exception $e) {
            $this->_helperData->writeLog([$e->getMessage()]);
        }
    }

    /**
     * @param $seller_id
     * @return \Magento\Framework\DataObject
     */
    public function createDefaultMembershipAddon($seller_id)
    {
        $data = [];
        // $group_id = $this->helper->getConfig('seller_settings/default_seller_group');
        // $group = $this->_sellerGroupFactory->create()->load($group_id);
        $membershipModel = $this->membershipAddonFactory->create();
        // if ($group && $group->getGroupId()) {
        //     try{
                $data['seller_id'] = $seller_id;
        //         $data['website_id'] = 1;
        //         $collection = $this->_productCollectionFactory->create()
        //                         ->addAttributeToFilter('seller_group', $group_id)
        //                         ->getFirstItem();
        //         $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($collection->getId());
        //         $productDuration = $product->getSellerDuration();
        //         $productDuration = isset($productDuration[0]) ? $productDuration[0] : [];

        //         $data['name'] = $product->getName();
        //         $data['price'] = !empty($productDuration) ? $productDuration['membership_price'] : $product->getPrice();
        //         $data['duration'] = !empty($productDuration) ? ($productDuration['membership_duration'] . ' ' . $productDuration['membership_unit']) : self::DEFAULT_DURATION_MONTH;

        //         $expiration_date = $this->_helperData->getExpirationDate(
        //             !empty($productDuration) ? $productDuration['membership_duration'] : self::DEFAULT_DURATION_MONTH,
        //             !empty($productDuration) ? $productDuration['membership_unit'] : "month"
        //         );
        //         $time = time() + $expiration_date;
        //         $date = date('Y-m-d h:i:s A', $time);
        //         $data['expiration_date'] = $date;
                $membershipModel->setData($data)->save();

        //         $this->_eventManager->dispatch(
        //             'controller_action_lofmp_seller_membership_default_save_after',
        //             ['controller' => $this, 'membership' => $membershipModel, 'group' => $group, 'product' => $product]
        //         );
        //     } catch(\Exception $e) {
        //         $this->_helperData->writeLog([$e->getMessage()]);
        //     }
        // }

        return $membershipModel;
    }

    public function getCurrentWebsiteId() 
    {
        return $this->_storeManager->getStore()->getWebsiteId();
    }

    public function filterMembershipDurationByWebsite($durations) 
    {
        if ($durations && is_array($durations)) {
            $filteredDurations = [];

            foreach($durations as $index => $duration) {
                if (isset($duration['website_id']) && $duration['website_id'] != $this->getCurrentWebsiteId() && $duration['website_id'] != 0) {
                    continue;
                }

                // dd($duration);
                if (is_array($duration)) {
                    $duration['currency_code'] = 'USD';
                }
                
                $filteredDurations[$index] = $duration;
            }

            return $filteredDurations;
        }

        return $durations;
    }

    /**
     * @param $product_id
     * @return \Magento\Catalog\Model\Product
     */
    public function getProductById($product_id)
    {
        $collection = $this->productCollectionFactory->create()->load($product_id);
        return $collection;
    }

    /**
     * @param $membership_duration
     * @param $membership_unit
     * @return float|int
     */
    public function getExpirationDate($membership_duration, $membership_unit)
    {
        if ($membership_unit == 'day') {
            $time = $membership_duration * 24 * 60 * 60;
        } elseif ($membership_unit == 'week') {
            $time = $membership_duration * 7 * 24 * 60 * 60;
        } elseif ($membership_unit == 'month') {
            $time = $membership_duration * 30 * 24 * 60 * 60;
        } elseif ($membership_unit == 'year') {
            $time = $membership_duration * 30 * 24 * 60 * 60;
        } else {
            $time = 0;
        }

        return $time;
    }

    /**
     * @return mixed
     */
    public function getCurrentCurrencyCode()
    {
        return $this->priceFormatter->getCurrency()->getCurrencyCode();
    }

    /**
     * @param $price
     * @param int $scale
     * @return float
     */
    public function getPriceFomat($price, $scale = 2)
    {
        $currencyCode = $this->getCurrentCurrencyCode();
        return $this->priceFormatter->format(
            $price,
            false,
            $scale,
            null,
            $currencyCode
        );
    }

    /**
     * @param $customer_id
     * @return \Magento\Customer\Model\Customer
     */
    public function getCustomerById($customer_id)
    {
        $collection = $this->customer->create()->load($customer_id);
        return $collection;
    }

    /**
     * @param $key
     * @param null $store
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getConfig($key, $store = null)
    {
        $store = $this->_storeManager->getStore($store);
        $result = $this->scopeConfig->getValue(
            'lofmarketplace/' . $key,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
        return $result;
    }

    /**
     * Is enabled
     *
     * @return bool|int
     */
    public function isEnabled()
    {
        return (int)$this->getConfig("buy_membership_page/enabled");
    }

    public function getBaseUrl()
    {
        try {
            return $this->_storeManager->getStore()->getBaseUrl();
        } catch (NoSuchEntityException $e) {
            return $e;
        }
    }

    /**
     * keep cart products
     *
     * @return int|bool
     */
    public function keepCartProducts()
    {
        $keepCart = $this->getConfig("buy_membership_page/keep_cart");
        $keepCart = ( $keepCart == "" || $keepCart == false || $keepCart == null ) ? 0 : (int) $keepCart;

        return $keepCart;
    }

    /**
     * Get cancel request label
     *
     * @param int|string $value
     * @return string
     */
    public function getCancelRequestLabel($value = '')
    {
        $label = '';
        if ($value == \Lofmp\SellerMembership\Model\Cancelrequest::PENDING) {
            $label = __('Pending');
        } elseif ($value == \Lofmp\SellerMembership\Model\Cancelrequest::APPROVED) {
            $label = __('Approved');
        } elseif ($value == \Lofmp\SellerMembership\Model\Cancelrequest::CHECKING) {
            $label = __('Checking');
        } elseif ($value == \Lofmp\SellerMembership\Model\Cancelrequest::DECLIDED) {
            $label = __('Declided');
        }

        return $label;
    }

    /**
     * set duration array
     *
     * @param mixed|object $productModel
     * @return void
     */
    public function setDurationArray($productModel)
    {
        $duration = $productModel->getData('seller_duration');
        if ($duration && !is_array($duration)) {
            $duration = @json_decode($duration, true);
        }
        $productModel->setData('seller_duration_array', $duration);
    }

    /**
     * get duration decoded
     *
     * @param mixed|array|object $duration
     * @return mixed
     */
    public function getDurationDecoded($duration)
    {
        if ($duration && !is_array($duration)) {
            $duration = @json_decode($duration, true);
        }
        return $duration;
    }

    /**
     * Get Date Time
     * @return DateTime
     */
    public function getDateTime()
    {
        return $this->_dateTime;
    }

    /**
     * Get Timezone date time
     * @param string $dateTime
     * @return string|null
     */
    public function getTimezoneDateTime($dateTime = "today")
    {
        if($dateTime === "today" || !$dateTime){
            $dateTime = $this->_dateTime->gmtDate();
        }

        $today = $this->_timezoneInterface
            ->date(
                new \DateTime($dateTime)
            )->format('Y-m-d H:i:s');
        return $today;
    }

    /**
     * Get Timezone Name
     * @return string
     */
    public function getTimezoneName()
    {
        return $this->_timezoneInterface->getConfigTimezone(\Magento\Store\Model\ScopeInterface::SCOPE_STORES);
    }

    /**
     * write system log
     *
     * @param array|mixed
     * @return void
     */
    public function writeLog($data)
    {
        $enabledLog = $this->getConfig('buy_membership_page/debug_mode');
        if ($enabledLog) {
            //$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/lof_sellermembership.log');
            //$logger = new \Zend\Log\Logger();
            $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/lof_sellermembership.log');
            $logger = new \Zend_Log();
            $logger->addWriter($writer);
            if ($data) {
                $message_log = "--- Start ---";
                $message_log .= "\nTrace: ";
                if (is_array($data)) {
                    foreach ($data as $key => $value) {
                        if (!is_object($value)) {
                            $message_log .= "\n";
                            $message_log .= is_array($value)?implode(", ", $value):$value;
                        }
                    }
                } elseif (!is_object($data)) {
                    $message_log .= "\n".$data;
                }
                $message_log .= "\n--- End ---";
                $logger->info($message_log);
            }
        }
    }

    /**
     * @param $data_array
     * @return array
     */
    public function xss_clean_array($data_array)
    {
        $result = [];
        if (is_array($data_array)) {
            foreach ($data_array as $key => $val) {
                $val = $this->xss_clean($val);
                $result[$key] = $val;
            }
        }
        return $result;
    }

    /**
     * @param $data
     * @return string|string[]|null
     */
    public function xss_clean($data)
    {
        if (!is_string($data)) {
            return $data;
        }
        // Fix &entity\n;
        $data = str_replace(['&amp;', '&lt;', '&gt;'], ['&amp;amp;', '&amp;lt;', '&amp;gt;'], $data);
        $data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
        $data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
        $data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');

        // Remove any attribute starting with "on" or xmlns
        $data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $data);

        // Remove javascript: and vbscript: protocols
        $data = preg_replace(
            '#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu',
            '$1=$2nojavascript...',
            $data
        );
        $data = preg_replace(
            '#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu',
            '$1=$2novbscript...',
            $data
        );
        $data = preg_replace(
            '#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u',
            '$1=$2nomozbinding...',
            $data
        );

        // Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
        $data = preg_replace(
            '#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i',
            '$1>',
            $data
        );
        $data = preg_replace(
            '#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i',
            '$1>',
            $data
        );
        $data = preg_replace(
            '#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu',
            '$1>',
            $data
        );

        // Remove namespaced elements (we do not need them)
        $data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);

        do {
            // Remove really unwanted tags
            $old_data = $data;
            $data = preg_replace(
                '#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i',
                '',
                $data
            );
        } while ($old_data !== $data);

        // we are done...
        return $data;
    }
}
