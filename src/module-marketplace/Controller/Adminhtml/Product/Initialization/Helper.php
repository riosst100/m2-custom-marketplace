<?php

namespace TCGCollective\MarketPlace\Controller\Adminhtml\Product\Initialization;

use Magento\Backend\Helper\Js;
use Magento\Catalog\Api\Data\CategoryLinkInterfaceFactory;
use Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory as CustomOptionFactory;
use Magento\Catalog\Api\Data\ProductLinkInterfaceFactory as ProductLinkFactory;
use Magento\Catalog\Api\Data\ProductLinkTypeInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface\Proxy as ProductRepository;
use Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper\AttributeFilter;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Authorization as ProductAuthorization;
use Magento\Catalog\Model\Product\Filter\DateTime as DateTimeFilter;
use Magento\Catalog\Model\Product\Initialization\Helper\ProductLinks;
use Magento\Catalog\Model\Product\Link\Resolver as LinkResolver;
use Magento\Catalog\Model\Product\LinkTypeProvider;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Stdlib\DateTime\Filter\Date;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Filter\FilterInput;
use Magento\Catalog\Controller\Adminhtml\Product\Initialization\StockDataFilter;
/**
 * Product helper
 *
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @since 100.0.2
 */
class Helper extends \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var StockDataFilter
     */
    protected $stockFilter;

    /**
     * @var Js
     */
    protected $jsHelper;

    /**
     * @var Date
     * @deprecated 101.0.0
     * @see we don't recommend this approach anymore
     */
    protected $dateFilter;

    /**
     * @var CustomOptionFactory
     * @since 101.0.0
     */
    protected $customOptionFactory;

    /**
     * @var ProductLinkFactory
     * @since 101.0.0
     */
    protected $productLinkFactory;

    /**
     * @var ProductRepository
     * @since 101.0.0
     */
    protected $productRepository;

    /**
     * @var ProductLinks
     */
    protected $productLinks;

    /**
     * @var LinkResolver
     */
    private $linkResolver;

    /**
     * @var LinkTypeProvider
     */
    private $linkTypeProvider;

    /**
     * @var AttributeFilter
     */
    private $attributeFilter;

    /**
     * @var ProductAuthorization
     */
    private $productAuthorization;

    /**
     * @var FormatInterface
     */
    private $localeFormat;

    /**
     * @var DateTimeFilter
     */
    private $dateTimeFilter;

    /**
     * @var CategoryLinkInterfaceFactory
     */
    private $categoryLinkFactory;

    /**
     * @var array
     */
    private $productDataKeys = [
        'weight',
        'special_price',
        'cost',
        'country_of_manufacture',
        'description',
        'short_description',
        'meta_description',
        'meta_keyword',
        'meta_title',
        'page_layout',
        'custom_design',
        'gift_wrapping_price'
    ];

    /**
     * Constructor
     *
     * @param RequestInterface $request
     * @param StoreManagerInterface $storeManager
     * @param StockDataFilter $stockFilter
     * @param ProductLinks $productLinks
     * @param Js $jsHelper
     * @param Date $dateFilter
     * @param CustomOptionFactory|null $customOptionFactory
     * @param ProductLinkFactory|null $productLinkFactory
     * @param ProductRepositoryInterface|null $productRepository
     * @param LinkTypeProvider|null $linkTypeProvider
     * @param AttributeFilter|null $attributeFilter
     * @param FormatInterface|null $localeFormat
     * @param ProductAuthorization|null $productAuthorization
     * @param DateTimeFilter|null $dateTimeFilter
     * @param CategoryLinkInterfaceFactory|null $categoryLinkFactory
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        RequestInterface $request,
        StoreManagerInterface $storeManager,
        StockDataFilter $stockFilter,
        ProductLinks $productLinks,
        Js $jsHelper,
        Date $dateFilter,
        CustomOptionFactory $customOptionFactory = null,
        ProductLinkFactory $productLinkFactory = null,
        ProductRepositoryInterface $productRepository = null,
        LinkTypeProvider $linkTypeProvider = null,
        AttributeFilter $attributeFilter = null,
        FormatInterface $localeFormat = null,
        ?ProductAuthorization $productAuthorization = null,
        ?DateTimeFilter $dateTimeFilter = null,
        ?CategoryLinkInterfaceFactory $categoryLinkFactory = null
    ) {
        $this->request = $request;
        $this->storeManager = $storeManager;
        $this->stockFilter = $stockFilter;
        $this->productLinks = $productLinks;
        $this->jsHelper = $jsHelper;
        $this->dateFilter = $dateFilter;

        $objectManager = ObjectManager::getInstance();
        $this->customOptionFactory = $customOptionFactory ?: $objectManager->get(CustomOptionFactory::class);
        $this->productLinkFactory = $productLinkFactory ?: $objectManager->get(ProductLinkFactory::class);
        $this->productRepository = $productRepository ?: $objectManager->get(ProductRepositoryInterface::class);
        $this->linkTypeProvider = $linkTypeProvider ?: $objectManager->get(LinkTypeProvider::class);
        $this->attributeFilter = $attributeFilter ?: $objectManager->get(AttributeFilter::class);
        $this->localeFormat = $localeFormat ?: $objectManager->get(FormatInterface::class);
        $this->productAuthorization = $productAuthorization ?? $objectManager->get(ProductAuthorization::class);
        $this->dateTimeFilter = $dateTimeFilter ?? $objectManager->get(DateTimeFilter::class);
        $this->categoryLinkFactory = $categoryLinkFactory ?? $objectManager->get(CategoryLinkInterfaceFactory::class);

        parent::__construct(
            $request,
            $storeManager,
            $stockFilter,
            $productLinks,
            $jsHelper,
            $dateFilter,
            $customOptionFactory,
            $productLinkFactory,
            $productRepository,
            $linkTypeProvider,
            $attributeFilter,
            $localeFormat,
            $productAuthorization,
            $dateTimeFilter,
            $categoryLinkFactory
        );
    }

    /**
     * Initialize product before saving
     *
     * @param Product $product
     * @return Product
     */
    public function initialize(Product $product)
    {
        $productData = $this->request->getParam('product');
        
        $product = $this->initializeFromData($product, $productData);
        $this->productAuthorization->authorizeSavingOf($product);

        return $product;
    }
}
