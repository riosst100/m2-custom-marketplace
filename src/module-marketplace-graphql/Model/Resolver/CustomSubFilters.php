<?php

declare(strict_types=1);

namespace TCGCollective\MarketplaceGraphQl\Model\Resolver;

use Magento\CatalogGraphQl\DataProvider\Product\LayeredNavigation\LayerBuilder;
use Magento\CatalogGraphQl\DataProvider\Product\LayeredNavigation\Builder\Aggregations\Category;
use Magento\Directory\Model\PriceCurrency;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Store\Api\Data\StoreInterface;

/**
 * Layered navigation filters resolver, used for GraphQL request processing.
 */
class CustomSubFilters implements ResolverInterface
{
    protected $categoryCollectionFactory;


    /**
     * @var \TCGCollective\ProductAttributesLink\Model\CardGamesMappingFactory
     */
    protected $cardGamesMappingFactory;

    /**
     * @var \TCGCollective\ProductAttributesLink\Model\SportCardsMappingFactory
     */
    protected $sportCardsMappingFactory;

    /**
     * @var \TCGCollective\ProductAttributesLink\Model\SportCardsSetsFactory
     */
    protected $sportCardsSetsFactory;

    /**
     * @var \TCGCollective\ProductAttributesLink\Helper\Data
     */
    protected $mappingHelper;
    protected $customFiltersHelper;

    protected $params = [];
    protected $limit = 8;
    protected $activeFiter = null;
    protected $activeFiterLabel = null;
    protected $prefix = '';
    protected $mainAttrCode = null;
    protected $subcategory = null;
    protected $subCategoriesAttrCode = null;
    protected $moreLabel = null;
    protected $categoryId = null;

    /**
     * @param \TCGCollective\ProductAttributesLink\Model\CardGamesMappingFactory $cardGamesMappingFactory
     * @param \TCGCollective\ProductAttributesLink\Model\SportCardsMappingFactory $sportCardsMappingFactory
     * @param \TCGCollective\ProductAttributesLink\Model\SportCardsSetsFactory $sportCardsSetsFactory
     * @param \TCGCollective\ProductAttributesLink\Helper\Data $mappingHelper
     */
    public function __construct(
        \TCGCollective\ProductAttributesLink\Model\CardGamesMappingFactory $cardGamesMappingFactory,
        \TCGCollective\ProductAttributesLink\Model\SportCardsMappingFactory $sportCardsMappingFactory,
        \TCGCollective\ProductAttributesLink\Model\SportCardsSetsFactory $sportCardsSetsFactory,
        \TCGCollective\ProductAttributesLink\Helper\Data $mappingHelper,
        \TCGCollective\MarketplaceGraphQl\Helper\CustomFilters $customFiltersHelper
    ) {
        $this->cardGamesMappingFactory = $cardGamesMappingFactory;
        $this->sportCardsMappingFactory = $sportCardsMappingFactory;
        $this->sportCardsSetsFactory = $sportCardsSetsFactory;
        $this->mappingHelper = $mappingHelper;
        $this->customFiltersHelper = $customFiltersHelper;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $result = [];

        $filters = $args['filters'];
        $shopby = isset($args['shopby']) ? $args['shopby'] : null;
        $filterBy = isset($args['filterBy']) ? $args['filterBy'] : null;
        $category = isset($args['category']) ? $args['category'] : null;
        $categoryUid = isset($args['categoryId']) ? $args['categoryId'] : null;
        $this->subcategory = isset($args['subcategory']) ? $args['subcategory'] : null;

        $this->getParams($args);
        
        // TOP FILTERS BY CATEGORY
        if ($category) {
            // TOP FILTERS L1
            // $this->mainAttrCode = $this->getTopFiltersL1AttributeCode($category);
            // if ($this->mainAttrCode) {
            //     $options = $this->getAttributeOptionsForTopFilters($this->mainAttrCode, $args);
            //     if ($options) {
            //         $result[] = $options;
            //     }
            // }

            // TOP FILTERS L2
            $result = $this->getTopFilters($info, $context, $categoryUid, $category, $args);
        }

        // dd($result);

        return [
            'filter_id' => json_encode($args),
            'filters' => $result
        ];
    }

    public function getParams($args) {
        $params = [];

        $search = isset($args['search']) ? $args['search'] : null;
        $str = parse_url($search, PHP_URL_QUERY);
        if ($str) {
            parse_str($str, $params);
        }

        $this->params = $params;
    }

    public function getTopFilters($info, $context, $categoryId, $category, $args) 
    {
        // Default
        $result = [];

        $limit = $this->limit;

        $topFilters = $this->customFiltersHelper->getCategoriesMainAttrCode(base64_decode($categoryId), $this->subcategory, $category);

        // dd($categoryId);

        $this->mainAttrCode = $topFilters['categories'] ?? null;
        $this->moreLabel = $topFilters['more_label'] ?? null;
        $this->subCategoriesAttrCode = $topFilters['sub_categories'] ?? null;

        $topFiltersShopBy = $topFilters['shopby'] ?? null;
        
        $categoriesOptions = [];

        $category = $this->mappingHelper->getCategoryById(base64_decode($categoryId));
        // dd($category);
        if ($category) {
            $firstCategory = $category;
            
            if ($category->getLevel() != 3) {
                $children = $category->getChildrenCategories();
                if ($children) {
                    $firstCategory = $children->getFirstItem();
                    if ($firstCategory) {
                        $this->categoryId = $firstCategory->getId();
                    }
                }
            } else {
                $children = $category->getParentCategory()->getChildrenCategories();
                // $categoriesOptions
            }

            // dd($children);

            if ($children) {
                foreach($children as $childCategory) {
                    $categoriesOptions[] = [
                        'label' => $childCategory->getName(),
                        'value' => $childCategory->getRequestPath(),
                        'count' => $this->getCategoryProductCount($info, $context, $childCategory->getId()),
                        'selected' => 0
                    ];
                }

                if ($categoriesOptions) {
                    foreach ($categoriesOptions as $key => $item) {
                        if ($item['label'] === $firstCategory->getName()) {
                            // ambil item dulu
                            $selected = $item;
                            $item['selected'] = 1;
                            // hapus dari posisi lama
                            unset($categoriesOptions[$key]);
                            break;
                        }
                    }

                    // taruh di awal
                    array_unshift($categoriesOptions, $selected);

                    // reindex jika perlu
                    $categoriesOptions = array_values($categoriesOptions);
                }
            }
        }

        
        $result[] = [
            "label" => 'category',
            "count" => count($categoriesOptions),
            "show_all_btn" => false,
            "attribute_code" => 'category',
            "options" => $categoriesOptions
        ];

        if ($this->mainAttrCode) {
            $result[] = $this->customFiltersHelper->getAttributeOptionsForTopFilters(
                $info, $context,
                $this->mainAttrCode, 
                $this->params, 
                $this->limit,
                $this->moreLabel
            );

            

            $this->categoryId = base64_decode($categoryId);
            
            

            // dd($this->categoryId);

            $this->getActiveFilter();
            $this->getPrefix();
        }

        if ($this->subCategoriesAttrCode) {
            $result[] = $this->customFiltersHelper->getAttributeOptionsForTopFilters(
                $info, $context,
                $this->subCategoriesAttrCode, 
                $this->params, 
                $this->limit
            );
        }

        if ($topFiltersShopBy) {
            // dd($this->prefix);
            $shopBy = $this->getShopByForTopFilters($topFiltersShopBy);
            if ($shopBy) {
                $result[] = $shopBy;
            }
            // dd($result);
        }

        return $result;
    }

    public function getCategoryProductCount($info, $context, $categoryId) 
    {
        // dd($categoryId);
        $args = [
            "filter" => [
                "category_uid" => [
                    "eq" => base64_encode($categoryId)
                ]
            ],
            "currentPage" => 1,
            "pageSize" => 20,
            "category_uid" => base64_encode($categoryId)
        ];

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $searchQuery = $objectManager->create('\Magento\CatalogGraphQl\Model\Resolver\Products\Query\ProductQueryInterface');
        $searchResult = $searchQuery->getResult($args, $info, $context);

        return $searchResult->getTotalCount();
    }

    public function getPrefix() 
    {
        $attributePrefix = $this->mappingHelper->getPrefixByCategoryId($this->categoryId, $this->activeFiterLabel);
        if ($attributePrefix) {
            $this->prefix = $attributePrefix;
        }
        // dd($this->activeFiterLabel);
    }

    public function getActiveFilter() 
    {
        if (isset($this->params[$this->mainAttrCode])) {
            $filter = $this->params[$this->mainAttrCode]['filter'] ?? null;
            if ($filter) {
                $filterArr = explode(',', $filter);
                if ($filterArr) {
                    $options[] = [
                        'label' => $filterArr[0],
                        'value' => $filterArr[1]
                    ];

                    $this->activeFiterLabel = $filterArr[0];
                    $this->activeFiter = $filterArr[1];

                    $this->limit = $this->limit-1;
                }
            }
        }
    }

    public function getShopByForTopFilters($options) 
    {
        if ($this->activeFiter) {
            // dd($this->prefix);
            // dd($options);
            $prefix = $this->prefix;

            $options = array_map(function ($item) use ($prefix) {
                if (isset($item["value"])) {
                    $item["value"] = str_replace("PREFIX_", $prefix, $item["value"]);
                }
                return $item;
            }, $options);

            return [
                "label" => "",
                "count" => count($options),
                "attribute_code" => 'shopby',
                "options" => $options
            ];
        }

        return null;
    }

    

    public function fashionDollsCategory($result) 
    {
        $options = $this->addAttributesData('dolls_product_type');
        if ($options) {
            $result[] = $options;
        }

        return $result;
    }

    public function animeFiguresCategory($result) 
    {
        $options = $this->addAttributesData('anime_figures');
        if ($options) {
            $result[] = $options;
        }

        return $result;
    }

    public function addAttributesData($attributeCode, $filters = null, $allowedOptions = []) 
    {
        $result = [];

        $scBaseballReleaseOptionId = $filters && isset($filters['sc_baseball_release']) ? $filters['sc_baseball_release']['eq'] : null;

        $optionsRaw = $this->mappingHelper->getAttributeOptions($attributeCode);
        if ($optionsRaw) {
            $options = [];
            foreach ($optionsRaw as $option) {
                if ($option->getValue()) {
                    $value = $option->getValue();
                    // if (!$scBaseballReleaseOptionId || $scBaseballReleaseOptionId && $this->filterScOptions('sc_baseball_release', $scBaseballReleaseOptionId, $attributeCode, $value)) {
                        // if ($attributeCode == "sc_set_type") {
                        //     if ($option->getLabel() == "Inserts") {
                        //         $value = 'inserts';
                        //     }

                        //     if ($option->getLabel() == "Parallel") {
                        //         $value = 'parallel';
                        //     }
                        // }
                        if (!$allowedOptions || $allowedOptions && in_array($option->getLabel(), $allowedOptions)) {
                            $options[] = [
                                'label' => $option->getLabel(),
                                'value' => $value
                            ];
                        }
                    // }
                }
            }

            if ($options) {
                $result = [
                    "label" => "",
                    "count" => count($options),
                    "attribute_code" => $attributeCode,
                    "options" => $options
                ];
            }
        }

        return $result;
    }

    public function filterScOptions($filterAttributeCode, $filterOptionId, $attributeCode, $optionId) 
    {
        $collection = $this->sportCardsSetsFactory->create()
        ->getCollection()
        ->addFieldToFilter($filterAttributeCode, $filterOptionId)
        ->addFieldToFilter($attributeCode, $optionId);
        // die($collection->getSelect()->__toString());
        if ($collection->getSize()) {
            return true;
        }

        return false;
    }
}
