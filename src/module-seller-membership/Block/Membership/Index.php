<?php

namespace TCGCollective\SellerMembership\Block\Membership;

use Lof\MarketPlace\Model\Commission as CommissionRule;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollection;

class Index extends \Lofmp\SellerMembership\Block\Membership\Index
{
    protected $_logger;
    protected $formKey;
    protected $_cartHelper;
    protected $productUrlHelper;
    protected $membershipHelper;
    protected $productCollectionFactory;
    protected $membershipCountry;


    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var ProductCollection
     */
    protected $_productCollection;

    /**
     * @var
     */
    protected $_itemsCollection;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $_productList;

    /**
     * @var \Lof\MarketPlace\Helper\Data
     */
    protected $marketHelper;

    /**
     * @var \Lofmp\SellerMembership\Model\Membership
     */
    protected $membership;

    /**
     * @var CommissionRule
     */
    protected $commission;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * @var \Lof\MarketPlace\Model\Seller
     */
    protected $seller;

    /**
     * @var \Lof\MarketPlace\Model\Group
     */
    protected $group;

    /**
     * @var \Lofmp\SellerMembership\Helper\Email
     */
    protected $email;

    /**
     * @var \Magento\Framework\Url
     */
    protected $urlHelper;

    protected $membershipAddonFactory;

    /**
     * Index constructor.
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Lof\MarketPlace\Helper\Data $marketHelper
     * @param CommissionRule $commission
     * @param \Lof\MarketPlace\Model\Seller $seller
     * @param \Lof\MarketPlace\Model\Group $group
     * @param \Lofmp\SellerMembership\Model\Membership $membership
     * @param \Lofmp\SellerMembership\Helper\Email $email
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param ProductCollection $productCollectionFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Url $urlHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Lof\MarketPlace\Helper\Data $marketHelper,
        \Lof\MarketPlace\Model\Commission $commission,
        \Lof\MarketPlace\Model\Seller $seller,
        \Lof\MarketPlace\Model\Group $group,
        \Lofmp\SellerMembership\Model\Membership $membership,
        \Lofmp\SellerMembership\Helper\Email $email,
        \Magento\Framework\App\ResourceConnection $resource,
        ProductCollection $productCollectionFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Url $urlHelper,
        \TCGCollective\SellerMembership\Model\MembershipAddonFactory $membershipAddonFactory,
        array $data = []
    ) {
        $this->_storeManager = $context->getStoreManager();
        $this->group = $group;
        $this->seller = $seller;
        $this->commission = $commission;
        $this->membership = $membership;
        $this->_resource = $resource;
        $this->_customerSession = $customerSession;
        $this->_productCollection = $productCollectionFactory;
        $this->marketHelper = $marketHelper;
        $this->_logger = $logger;
        $this->email = $email;
        $this->urlHelper = $urlHelper;
        $this->membershipAddonFactory = $membershipAddonFactory;
        parent::__construct(
            $context,
            $customerSession,
            $marketHelper,
            $commission,
            $seller,
            $group,
            $membership,
            $email,
            $resource,
            $productCollectionFactory,
            $logger,
            $data
        );
    }

    public function getExpiredDaysLeft($expiredDate) {
        $today = new \DateTime(); // hari ini
        $target = new \DateTime($expiredDate); // target date

        $interval = $today->diff($target); // hitung selisih

        if ($interval->days <= 30) {
            return'(' . $interval->days . ' days left' . ')';
        }
    }

    public function formatDateTime($datetime) 
    {
        return $this->marketHelper->formatDateTime($datetime);
    }

    public function hasProductUrl($product)
    {
        if ($product->getVisibleInSiteVisibilities()) {
            return true;
        }
        if ($product->hasUrlDataObject()) {
            if (in_array($product->hasUrlDataObject()->getVisibility(), $product->getVisibleInSiteVisibilities())) {
                return true;
            }
        }

        return false;
    }

    public function getProductUrl($product, $additional = [])
    {
        if ($this->hasProductUrl($product)) {
            if (!isset($additional['_escape'])) {
                $additional['_escape'] = true;
            }
            return $product->getUrlModel()->getUrl($product, $additional);
        }

        return '#';
    }

    public function getFormKey() 
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->formKey = $objectManager->create(\Magento\Framework\Data\Form\FormKey::class);

        return $this->formKey->getFormKey();
    }

    public function getAddToCartUrl($product, $additional = [])
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_cartHelper = $objectManager->create(\Magento\Checkout\Helper\Cart::class);
        
        if (!$product->getTypeInstance()->isPossibleBuyFromList($product)) {
            if (!isset($additional['_escape'])) {
                $additional['_escape'] = true;
            }
            if (!isset($additional['_query'])) {
                $additional['_query'] = [];
            }
            $additional['_query']['options'] = 'cart';

            return $this->getProductUrl($product, $additional);
        }
        return $this->_cartHelper->getAddUrl($product, $additional);
    }

    /**
     * @param Product $product
     * @return array
     */
    public function getAddToCartPostParams(\Magento\Catalog\Model\Product $product)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->productUrlHelper = $objectManager->create(\Magento\Framework\Url\Helper\Data::class);

        $customerId = $this->_customerSession->getId();
        
        // $url = $this->getAddToCartUrl($product);
        // $url = $this->removeCountryCode($this->getAddToCartUrl($product)) . 'customer_id/' . $customerId;
        // die($url);
        $storeId = $this->_storeManager->getStore()->getId();
        
        $url = $this->urlHelper->getUrl('sellermembership/membership/add', [ '_scope' => $storeId]);
        // $url = $this->removeCountryCode($url);
        // $url = $this->_getUrl('checkout/cart/add', []);
        return [
            'action' => $url,
            'data' => [
                'product' => $product->getEntityId(),
                \Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED => $this->productUrlHelper->getEncodedUrl($url),
            ]
        ];
    }

    public function removeCountryCode($url) {
        // Hilangkan segment /xx/ (dua huruf lowercase)
        return preg_replace('#/[a-z]{2}/#', '/', $url, 1);
    }

    /**
     * @param $groupId
     * @return array
     */
    public function getExtraOptionsPlan($groupId)
    {
        return [];
    }

    public function filterMembershipDurationByWebsite($duration) 
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->membershipHelper = $objectManager->create(\Lofmp\SellerMembership\Helper\Data::class);

        return $this->membershipHelper->filterMembershipDurationByWebsite($duration);
    }

    public function getLoadedProductCollection() 
    {
        // $collection = $this->_productCollection->create()
        // ->addFieldToFilter('status', 1);
        // $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        // $this->productCollectionFactory = $objectManager->create(\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory::class);

        $collection = $this->_productCollection->create();
        $collection->addStoreFilter(0);
        $collection->addAttributeToSelect(['seller_group','seller_duration','name','seller_featured_package']);
        // $collection->addAttributeToFilter($attributeCode, ['eq' => $optionId]);
        $collection->addFieldToFilter('type_id', ['eq' => 'seller_membership']);
        $collection->setPageSize(3);
        // if ($collection->getSize()) {
            // dd($collection->getFirstItem());
            return $collection;
        // }

        // return null;
    }

    public function getListingMembershipPlanProducts() 
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        // $this->productCollectionFactory = $objectManager->create(\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory::class);

        // $currentWebsiteId = $this->_storeManager->getStore()->getWebsite()->getId();
        // $collection = $this->_productCollection->create()
        // ->addFieldToFilter('status', 1);
        
        $collection = $this->_productCollection->create();
        $collection->addStoreFilter(0);
        $collection->addAttributeToSelect(['membership_country','seller_group','seller_duration','name','seller_featured_package']);
        // $collection->addAttributeToFilter($attributeCode, ['eq' => $optionId]);
        $collection->addFieldToFilter('type_id', ['eq' => 'seller_membership']);
        $collection->addAttributeToFilter('membership_type', ['eq' => 'listing_country']);
        // $collection->addAttributeToFilter('membership_country', ['neq' => $currentWebsiteId]);
        // $collection->setPageSize(3);
        // if ($collection->getSize()) {
            // dd($collection->getFirstItem());
            return $collection;
        // }

        // return null;
    }

    public function getFrontendUrl($urlPath) 
    {
        $storeId = $this->_storeManager->getStore()->getId();
        
        return $this->urlHelper->getUrl($urlPath, [ '_scope' => $storeId]);
    }

    public function getSellerDefaultWebsite() 
    {
        return $this->_storeManager->getStore()->getWebsite();
    }

    public function getWebsiteCurrency($websiteId) 
    {
        $website = $this->_storeManager->getWebsite($websiteId);
        return $website->getDefaultStore()->getDefaultCurrencyCode();
    }

    public function getAvailableWebsites() 
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->websiteCollectionCache = $objectManager->create(\TCGCollective\MarketPlace\Model\WebsiteCollectionCache::class);

        $options = [];

        $websiteCollection = $this->websiteCollectionCache->getWebsiteCollection();
        if ($websiteCollection) {
            $websites = $websiteCollection['items'] ?? [];
            if ($websites) {
                foreach ($websites as $website) {
                    $websiteCode = $website['code'];
                    $website = $this->_storeManager->getWebsite($website['website_id']);
                    $store = $website->getDefaultStore();
                    // dd(get_class_methods($store));
                    $options[] = [
                        'label' => $website['name'] . ' (Currency: ' . $store->getDefaultCurrencyCode() . ')',
                        'value' => $websiteCode
                    ];
                }
            }
        }

        return $options;
    }

    public function getWebsiteNameById($websiteId) 
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->membershipCountry = $objectManager->create(\TCGCollective\SellerMembership\Model\Config\Source\MembershipCountry::class);

        $options = $this->membershipCountry->getAllOptions();
        if ($options) {
            foreach($options as $option) 
            {
                if ($option['value'] == $websiteId) {
                    return $option['label'];
                }
            }
        }

        return null;
    }

    /**
     * @return \Magento\Framework\DataObject
     */
    public function getListingCountryMembership()
    {
        $seller_id = $this->getSellerId();
        $membershipColl = $this->membershipAddonFactory->create()
        ->getCollection()->addFieldToFilter('seller_id', $seller_id);
        if ($membershipColl->getSize()) {
            return $membershipColl;
        }

        return [];
    }

    /**
     * @return \Magento\Framework\DataObject
     */
    public function getSellerListingWebsiteIds()
    {
        $seller = $this->getSeller();
        if ($seller) {
            $listingWebsiteIds = $seller->getData('listing_website_ids');
            if ($listingWebsiteIds) {
                return explode(",", $listingWebsiteIds);
            }
        }

        return null;
    }

    /**
     * @return \Magento\Framework\DataObject
     */
    public function getMembership()
    {
        $seller_id = $this->getSellerId();
        $membershipColl = $this->membership->getCollection()->addFieldToFilter('seller_id', $seller_id);
        if ($membershipColl->getSize()) {
            return $membershipColl->getFirstItem();
        }

        return null;
    }

    /**
     * @param $groupId
     * @return array
     */
    public function getOptionByGroupId($groupId)
    {
        $option = [];
        $group = $this->getGroupById($groupId)->getData();

        return $group;

        if (is_array($group) && count($group) > 0) {
            // if ($group['can_add_product'] == 1) {
            //     $option[] = __('Can add product');
            // }
            // if ($group['can_cancel_order'] == 1) {
            //     $option[] = __('Can cancel order');
            // }
            // if ($group['can_create_invoice'] == 1) {
            //     $option[] = __('Can create invoice');
            // }
            // if ($group['can_create_shipment'] == 1) {
            //     $option[] = __('Can create shipment');
            // }
            // if ($group['hide_payment_info'] == 1) {
            //     $option[] = __('Hide payment info');
            // }
            // if ($group['hide_customer_email'] == 1) {
            //     $option[] = __('Hide customer email');
            // }
            // if ($group['can_use_shipping'] == 1) {
            //     $option[] = __('Can Use Shipping');
            // }
            // if ($group['can_submit_order_comments'] == 1) {
            //     $option[] = __('Can submit order comments');
            // }
            // if ($group['can_use_message'] == 1) {
            //     $option[] = __('Can use message');
            // }
            // if ($group['can_use_review'] == 1) {
            //     $option[] = __('Can use review');
            // }
            // if ($group['can_use_rating'] == 1) {
            //     $option[] = __('Can use rating');
            // }
            // if ($group['can_use_import_export'] == 1) {
            //     $option[] = __('Can import/export product');
            // }
            // if ($group['can_use_vacation'] == 1) {
            //     $option[] = __('Can use vacation');
            // }
            // if ($group['can_use_report'] == 1) {
            //     $option[] = __('Can use report');
            // }
            // if ($group['can_use_withdrawal'] == 1) {
            //     $option[] = __('Can use withdrawal');
            // }
            // return $option;
        }
    }

    /**
     * @param $groupId
     * @return \Magento\Framework\DataObject
     */
    public function getGroupById($groupId)
    {
        $group = $this->group->getCollection()->addFieldToFilter('group_id', $groupId)->getFirstItem();
        return $group;
    }

    /**
     * @return array
     */
    public function getOption()
    {
        $option = [];
        $group = $this->getGroup()->getData();

        if ($group && is_array($group)) {
            if ($group['can_add_product'] == 1) {
                $option[] = __('Can add product');
            }
            if ($group['can_cancel_order'] == 1) {
                $option[] = __('Can cancel order');
            }
            if ($group['can_create_invoice'] == 1) {
                $option[] = __('Can create invoice');
            }
            if ($group['can_create_shipment'] == 1) {
                $option[] = __('Can create shipment');
            }
            if ($group['hide_payment_info'] == 1) {
                $option[] = __('Hide payment info');
            }
            if ($group['hide_customer_email'] == 1) {
                $option[] = __('Hide customer email');
            }
            if ($group['can_use_shipping'] == 1) {
                $option[] = __('Can Use Shipping');
            }
            if ($group['can_submit_order_comments'] == 1) {
                $option[] = __('Can submit order comments');
            }
            if ($group['can_use_message'] == 1) {
                $option[] = __('Can use message');
            }
            if ($group['can_use_review'] == 1) {
                $option[] = __('Can use review');
            }
            if ($group['can_use_rating'] == 1) {
                $option[] = __('Can use rating');
            }
            if ($group['can_use_import_export'] == 1) {
                $option[] = __('Can import/export product');
            }
            if ($group['can_use_vacation'] == 1) {
                $option[] = __('Can use vacation');
            }
            if ($group['can_use_report'] == 1) {
                $option[] = __('Can use report');
            }
            if ($group['can_use_withdrawal'] == 1) {
                $option[] = __('Can use withdrawal');
            }

            return $option;
        }
    }
}
