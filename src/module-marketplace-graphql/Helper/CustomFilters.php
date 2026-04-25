<?php

namespace TCGCollective\MarketplaceGraphQl\Helper;

use Magento\Framework\App\ResourceConnection;

class CustomFilters extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $productCollectionFactory;
    protected $resource;
    protected $mappingHelper;
    protected $catalogHelper;
    protected $mainAttrCode = null;
    protected $shopby = null;
    protected $subCategoriesAttrCode= null;
    protected $moreLabel = null;
    
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \TCGCollective\ProductAttributesLink\Helper\Data $mappingHelper,
        \TCGCollective\MarketPlace\Helper\Catalog $catalogHelper,
        ResourceConnection $resource
    ) {
        parent::__construct(
            $context
        );
        $this->productCollectionFactory = $productCollectionFactory;
        $this->resource = $resource;
        $this->mappingHelper = $mappingHelper;
        $this->catalogHelper = $catalogHelper;
    }

    public function getCategoriesMainAttrCode($categoryId, $subcategory = null, $category = null) 
    {
        $this->mainAttrCode = null;

        // GAMES TOP FILTERS
        if (str_contains($category, "action-figures")) {

            // Default for Games

            $this->shopby = [
                [
                    'label' => __('Brand'),
                    'value' => 'toys_brand'
                ],
                [
                    'label' => __('Toy Line'),
                    'value' => 'toys_line'
                ],
                [
                    'label' => __('Franchise'),
                    'value' => 'toys_franchise'
                ],
                [
                    'label' => __('Character'),
                    'value' => 'character'
                ],
                [
                    'label' => __('Series'),
                    'value' => 'toys_series'
                ],
                [
                    'label' => __('Scale'),
                    'value' => 'toys_scale'
                ]
            ];
        }

        // GAMES TOP FILTERS
        if (str_contains($category, "anime")) {

            // Default for Games

            $this->shopby = [
                [
                    'label' => __('Brand'),
                    'value' => 'toys_brand'
                ],
                [
                    'label' => __('Toy Line'),
                    'value' => 'toys_line'
                ],
                [
                    'label' => __('Franchise'),
                    'value' => 'toys_franchise'
                ],
                [
                    'label' => __('Character'),
                    'value' => 'character'
                ],
                [
                    'label' => __('Series'),
                    'value' => 'toys_series'
                ],
                [
                    'label' => __('Scale'),
                    'value' => 'toys_scale'
                ]
            ];
        }

        // DOLLS TOP FILTERS
        if (str_contains($category, "fashion-dolls")) {
            // Default attr code

            $this->shopby = [
                [
                    'label' => __('Brand'),
                    'value' => 'dolls_brand'
                ],
                [
                    'label' => __('Toy Line'),
                    'value' => 'dolls_product_line'
                ],
                [
                    'label' => __('Franchise'),
                    'value' => 'franchise'
                ],
                [
                    'label' => __('Character'),
                    'value' => 'character'
                ],
                [
                    'label' => __('Series'),
                    'value' => 'dolls_series'
                ],
                [
                    'label' => __('Scale'),
                    'value' => 'dolls_scale'
                ]
            ];
        }

        // GAMES
        if (str_contains($category, "games")) {

            $shopbyGames = [
                [
                    'label' => 'Sealed Products',
                    'value' => 'release'
                ],
                [
                    'label' => 'Singles',
                    'value' => 'sets'
                ],
                [
                    'label' => 'Series & Expansion',
                    'value' => 'expansion_sets'
                ],
                [
                    'label' => 'Year',
                    'value' => 'release_year'
                ]
            ];

            if (str_contains($category, "collectible")) {
                $shopbyGames = array_merge($shopbyGames, [
                    [
                        'label' => 'Card Name',
                        'value' => 'PREFIX_card_name'
                    ],
                    [
                        'label' => 'Artist',
                        'value' => 'PREFIX_card_artist'
                    ]
                ]);
            }

            if (str_contains($category, "workshop")) {
                $shopbyGames = [
                    [
                        'label' => __('Brand'),
                        'value' => 'miniature_brand'
                    ]
                ];
            }

            $this->shopby = $shopbyGames;
        }

        // CARDS TOP FILTERS
        if (str_contains($category, "cards")) {

            $shopbyGames = [
                [
                    'label' => 'Sealed Products',
                    'value' => 'release'
                ],
                [
                    'label' => 'Singles',
                    'value' => 'sets'
                ],
                [
                    'label' => 'Series & Expansion',
                    'value' => 'expansion_sets'
                ],
                [
                    'label' => 'Year',
                    'value' => 'release_year'
                ]
            ];

            if (str_contains($category, "non-sport")) {
                $shopbyGames = array_merge($shopbyGames, [
                    [
                        'label' => __('Franchise'),
                        'value' => 'toys_franchise'
                    ],
                    [
                        'label' => __('Character'),
                        'value' => 'character'
                    ],
                    [
                        'label' => __('Series'),
                        'value' => 'toys_series'
                    ]
                ]);
            }

            $this->shopby = $shopbyGames;
        }

        // BRICKS TOP FILTERS
        if (str_contains($category, "bricks")) {
            // Default attr code
            $this->moreLabel = 'More Brand';

            $this->shopby = [
                [
                    'label' => 'Brand',
                    'value' => 'bricks_brand'
                ],
                [
                    'label' => 'Sets',
                    'value' => 'bricks_set_name'
                ],
                [
                    'label' => 'Theme',
                    'value' => 'bricks_theme'
                ],
                [
                    'label' => 'Sub-Theme',
                    'value' => 'bricks_subtheme'
                ],
                [
                    'label' => 'Interest',
                    'value' => 'bricks_interest'
                ]
            ];
        }

        // DIECAST TOP FILTERS
        if (str_contains($category, "die-cast")) {
            $this->shopby = [
                [
                    'label' => __('Brand'),
                    'value' => 'diecast_brand'
                ],
                [
                    'label' => __('Toy Line'),
                    'value' => 'PREFIX_diecast_line'
                ],
                [
                    'label' => __('Make'),
                    'value' => 'vehicle_make'
                ],
                [
                    'label' => __('Year'),
                    'value' => 'vehicle_year'
                ],
                [
                    'label' => __('Scale'),
                    'value' => 'diecast_scale'
                ]
            ];
        }

        // MODEL KITS TOP FILTERS
        if (str_contains($category, "model-kits") && !str_contains($category, "action-figures/model-kits")) {
            $this->shopby = [
                [
                    'label' => __('Brand'),
                    'value' => 'model_kits_brand'
                ],
                [
                    'label' => __('Toy Line'),
                    'value' => 'PREFIX_model_kits_line'
                ],
                [
                    'label' => __('Make'),
                    'value' => 'vehicle_make'
                ],
                [
                    'label' => __('Year'),
                    'value' => 'vehicle_year'
                ],
                [
                    'label' => __('Scale'),
                    'value' => 'model_kits_scale'
                ]
            ];
        }

        // TRAINS TOP FILTERS
        if (str_contains($category, "model-railway")) {
            // Default attr code
            // $this->mainAttrCode = 'railway_categories';

            $this->shopby = [
                [
                    'label' => __('Brand'),
                    'value' => 'railway_brand'
                ],
                [
                    'label' => __('Toy Line'),
                    'value' => 'railway_toy_line'
                ],
                [
                    'label' => __('Gauge'),
                    'value' => 'railway_gauge'
                ],
                [
                    'label' => __('Scale'),
                    'value' => 'scale'
                ]
            ];

            // TRAINS | RAILWAY
            if (str_contains($category, "model-railway/trains")) {
                // $this->mainAttrCode = 'railway_categories';
            }

            // TRAINS | ACCESSORIES
            if (str_contains($category, "model-railway/layout")) {
                // $this->mainAttrCode = 'railway_accessories_categories';
            }
        }

        // HOBBY SUPPLIES TOP FILTERS
        if (str_contains($category, "hobby-supplies")) {
            // Default attr code
            // $this->mainAttrCode = 'hobby_supplies_categories';

            $this->shopby = [
                [
                    'label' => __('Brand'),
                    'value' => 'hobby_supplies_brand'
                ]
            ];

            // // HOBBY SUPPLIES | HOBBY SUPPLIES
            // if (str_contains($category, "hobby-supplies/hobby-supplies")) {
            //     $this->mainAttrCode = 'hobby_supplies_categories';
            // }

            // // HOBBY SUPPLIES | CARDS & GAMES
            // if (str_contains($category, "hobby-supplies/cards-games-supplies")) {
            //     $this->mainAttrCode = 'hobby_supplies_cards_games_categories';
            // }
        }

        $subCategoriesConfig = $this->catalogHelper->getSubCategoriesConfig($categoryId);
        if ($subCategoriesConfig) {
            $this->mainAttrCode = $subCategoriesConfig['attribute_code'] ?? null;
            $this->moreLabel = $subCategoriesConfig['show_more_label'] ?? null;
        }

        return [
            'categories' => $this->mainAttrCode,
            'more_label' => $this->moreLabel,
            'sub_categories' => $this->subCategoriesAttrCode,
            'shopby' => $this->shopby
        ];
    }

    public function getProductCount($info, $context, $attrCode, $value) 
    {
        // dd($categoryId);
        $args = [
            "filter" => [
                $attrCode => [
                    "eq" => $value
                ]
            ],
            "currentPage" => 1,
            "pageSize" => 20
        ];

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $searchQuery = $objectManager->create('\Magento\CatalogGraphQl\Model\Resolver\Products\Query\ProductQueryInterface');
        $searchResult = $searchQuery->getResult($args, $info, $context);

        return $searchResult->getTotalCount();
    }

    public function getAttributeOptionsForTopFilters($info, $context, $attrCode, $params, $limit, $moreLabel = null) 
    {
        // Get attribute options
        if ($attrCode && !is_array($attrCode)) {
            $options = [];
            
            if (!$moreLabel) {
                $moreLabel = 'More Categories';
            }
            
            $activeFiter = null;
            $activeFiterLabel = null;
            if ($params && isset($params[$attrCode])) {
                $filter = $params[$attrCode]['filter'] ?? null;
                if ($filter) {
                    $filterArr = explode(',', $filter);

                    $value = array_pop($filterArr);        // ambil elemen terakhir
                    $label = implode(',', $filterArr);  // gabungkan sisanya

                    if ($filterArr) {
                        $options[] = [
                            'label' => $label,
                            'value' => $value,
                            'count' => $this->getProductCount($info, $context, $attrCode, $value)
                        ];

                        $activeFiterLabel = $label;
                        $activeFiter = $value;

                        $limit = $limit-1;
                    }
                }
            }

            $attributeOptionsArr = $this->mappingHelper->getCachedAttributeOptions($attrCode);
            
            if ($attributeOptionsArr) {
                $x = 0;
                foreach ($attributeOptionsArr as $optionArr) {
                    
                    $label = $optionArr['label'] ?? null;
                    $value = $optionArr['value'] ?? null;
                    
                    if (!$value) {
                        continue;
                    }

                    if (
                        $optionArr['label'] == "Game" 
                        || $optionArr['label'] == "Brands" 
                        || $optionArr['label'] == "Games" 
                        || $optionArr['label'] == "Card Game" 
                        || $optionArr['label'] == "Brand"
                    ) {
                        continue;
                    }

                    if ($activeFiter && $value == $activeFiter) {
                        continue;
                    }

                    $x++;

                    if ($x > $limit) {
                        continue;
                    }

                    $label = mb_convert_encoding($label, "UTF-8");
                    $options[] = [
                        'label' => $label,
                        'value' => $value,
                        'count' => $this->getProductCount($info, $context, $attrCode, $value)
                    ];
                }

                if ($options && $x > $limit) {
                    $options[] = [
                        'label' => $moreLabel,
                        'value' => 'shopby_all'
                    ];
                }
            }

            return [
                "label" => $moreLabel,
                "count" => count($options),
                "show_all_btn" => false,
                "attribute_code" => $attrCode,
                "options" => $options
            ];
        }

        return null;
    }

    public function getAttributeData($attributeCode, $storeId, $filters = null, $categoryUrlKey = null) 
    {
        $connection = $this->resource->getConnection();

        $eavAttributeTable = $this->resource->getTableName('eav_attribute');

        $attribute = $connection->fetchRow(
            "SELECT frontend_label, attribute_id, backend_type FROM {$eavAttributeTable} 
             WHERE attribute_code = :code AND entity_type_id = :entity_type",
            [
                'code' => $attributeCode,
                'entity_type' => 4 // catalog_product
            ]
        );

        if (!$attribute) {
            return [];
        }

        $attributeId = (int)$attribute['attribute_id'];
        $backendType = $attribute['backend_type'];

        // Determine correct EAV table
        $allowedTypes = ['int', 'varchar', 'text', 'decimal', 'datetime'];
        if (!in_array($backendType, $allowedTypes)) {
            return [];
        }

        $eavTable = $this->resource->getTableName("catalog_product_entity_{$backendType}");
        $optionValueTable = $this->resource->getTableName('eav_attribute_option_value');

        $publishStatusAttrId = 1304; // atau ambil via EAV config
        $publishStatusTable = $this->resource->getTableName('catalog_product_entity_int');
        $setsTable = $this->resource->getTableName('marketplace_card_games_sets');

        $select = $connection->select()
            ->from(
                ['eav' => $eavTable],
                ['value' => 'eav.value', 'products_count' => new \Zend_Db_Expr('COUNT(*)')]
            )
            ->join(
                ['ps' => $publishStatusTable],
                'eav.entity_id = ps.entity_id AND ps.attribute_id = :publish_status_attribute_id AND ps.value = 1',
                []
            )
            ->joinLeft(
                ['eaov' => $optionValueTable],
                'eav.value = eaov.option_id AND eaov.store_id = :store_id',
                ['label' => 'eaov.value']
            )
            ->where('eav.attribute_id = :attribute_id')
            ->group('eav.value')
            ->order('products_count DESC');

        if ($filters) {
            // dd($attributeCode);
            // $select = $this->filterBySetsRelease($attributeCode, $select, $filters, $categoryUrlKey);

            // $subSelect = $this->getAllowedData($connection, $attributeCode, $filters, $categoryUrlKey);
            // if ($subSelect) {
            // $select->where('eaov.value IN (?)', $subSelect);
                // $select->where('eaov.value IN (' . $allowedData .')');
            // }
        }

        $bind = [
            'attribute_id' => $attributeId,
            'publish_status_attribute_id' => $publishStatusAttrId,
            'store_id' => 0
        ];

        // dd($select->__toString());


        // dd($connection->fetchAll($select, $bind));
        return [
            'frontend_label' => $attribute['frontend_label'] ?? '',
            'options' => $connection->fetchAll($select, $bind)
        ];
        // if ($optionId) {
        //     $store = $context->getExtensionAttributes()->getStore();
        //     $storeId = $store->getId();

        //     $collection = $this->productCollectionFactory->create();
        //     $collection->addFieldToSelect('e.entity_id');
        //     $collection->addStoreFilter($storeId);
        //     $collection->addAttributeToFilter($attributeCode, ['eq' => $optionId]);
        //     // $collection->addAttributeToFilter('publish_status', ['eq' => 1]);

            // $customTable = $collection->getTable('catalog_product_entity_int');
            // $collection->getSelect()->joinLeft(
            //     ['cpei' => $customTable],
            //     'e.entity_id = cpei.entity_id AND cpei.value = 1 AND cpei.attribute_id = 1304',
            //     []
            // );

            // $collection->getSelect()->where('cpei.value=1');

        //     if ($optionId == 33731) {
        //         // dd($collection->getData());
        //     }

        //     $count = $collection->getSize();
            
        //     return $count;
        // }

        // return 0;
    }
    
    public function getAllowedData($connection, $attributeCode, $filters, $categoryUrlKey) 
    {
        $subSelect = null;

        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
        $this->mappingHelper = $objectManager->get(\TCGCollective\ProductAttributesLink\Helper\Data::class);

        $categoryName = $this->mappingHelper->getCategoryNameByUrlKey($categoryUrlKey);
        $customTableMetadata = $this->mappingHelper->getCustomTableMetadata(null, $categoryName);
        if ($customTableMetadata) {
            $mainAttrVal = null;
            $isSingles = false;
            $filterby = null;

            $mainAttrCode = $customTableMetadata['mainAttrCode'] ?? '';
            $mainSetsAttrCode = $customTableMetadata['mainSetsAttrCode'] ?? '';
            $setsTableName = $customTableMetadata['setsTableName'] ?? '';
            $singlesTableName = $customTableMetadata['singlesTableName'] ?? '';
            $mainReleaseAttrCode = $customTableMetadata['mainReleaseAttrCode'] ?? '';

            $singlesCollection = $customTableMetadata['singlesCollection'] ?? '';

            foreach($filters as $attrCode => $filter) {
                $val = $filter['eq'] ?? '';
                if ($val) {
                    if ($attrCode == $mainAttrCode) {
                        $mainAttrVal = $val;
                    }
                }
            }

            // dd($attributeCode);

            if (str_contains($attributeCode, 'card_name')) {
                $filterby = 'card_name';
                $isSingles = true;
            }

            if ($filterby && $mainAttrVal) {
                if ($isSingles) {
                    $subSelect = $connection->select()
                    ->from(
                        ['singles' => $singlesTableName],
                        [$filterby] // kolom yang match dengan eaov.value
                    )
                    ->join(
                        ['sets' => $setsTableName],
                        'singles.set_id = sets.entity_id',
                        []
                    )
                    ->where("sets.{$mainAttrCode} = ?", $mainAttrVal); 
                }

                // dd($subSelect->__toString());
            }
        }

        // dd($subSelect->fetchAll());

        return $subSelect;
    }

    public function filterBySetsRelease($attributeCode, $select, $filters, $categoryUrlKey) 
    {
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
        $this->mappingHelper = $objectManager->get(\TCGCollective\ProductAttributesLink\Helper\Data::class);

        $categoryName = $this->mappingHelper->getCategoryNameByUrlKey($categoryUrlKey);
        $customTableMetadata = $this->mappingHelper->getCustomTableMetadata(null, $categoryName);
        if ($customTableMetadata) {
            $mainAttrCode = $customTableMetadata['mainAttrCode'] ?? '';
            $mainSetsAttrCode = $customTableMetadata['mainSetsAttrCode'] ?? '';
            $mainReleaseAttrCode = $customTableMetadata['mainReleaseAttrCode'] ?? '';
            foreach($filters as $attrCode => $filter) {
                $val = $filter['eq'] ?? '';
                if ($val) {
                    // Filter by sets
                    if (str_contains($attrCode, $mainSetsAttrCode)) {
                        // $select = $this->filterBySets($customTableMetadata, $select, $mainSetsAttrCode, $val);
                    } else {
                        if (str_contains($attrCode, $mainAttrCode)) {
                            $select = $this->filterByGame($attributeCode, $customTableMetadata, $select, $mainAttrCode, $val);
                        }
                    }
                    

                    // Filter by release
                    // if (str_contains($attrCode, $mainReleaseAttrCode)) {
                    //     $select = $this->filterByRelease($customTableMetadata, $select, $mainReleaseAttrCode, $val);
                    // }
                }
            }
        }

        return $select;
    }

    public function filterByGame($attributeCode, $customTableMetadata, $select, $mainAttrCode, $val) 
    {
        $singlesTableName = $customTableMetadata['singlesTableName'] ?? '';
        $setsTableName = $customTableMetadata['setsTableName'] ?? '';

        $attributeCode = preg_replace('/^[^_]+_/', '', $attributeCode);
        

        $select->join(
            ['singles' => $singlesTableName],
            'singles.' . $attributeCode . '=eaov.value',
            []
        );

        $select->join(
            ['sets' => $setsTableName],
            'sets.entity_id=singles.set_id',
            []
        );

        $select->where("sets.$mainAttrCode=$val");

        return $select;
    }

    public function filterBySets($customTableMetadata, $select, $mainSetsAttrCode, $val) 
    {
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
        $this->mappingHelper = $objectManager->get(\TCGCollective\ProductAttributesLink\Helper\Data::class);

        $categoryName = $this->mappingHelper->getCategoryNameByUrlKey($categoryUrlKey);
        $customTableMetadata = $this->mappingHelper->getCustomTableMetadata(null, $categoryName);
        if ($customTableMetadata) {
            $mainSetsAttrCode = $customTableMetadata['mainSetsAttrCode'] ?? '';
            $mainReleaseAttrCode = $customTableMetadata['mainReleaseAttrCode'] ?? '';

            foreach($filters as $attrCode => $filter) {
                if (str_contains($attrCode, $main)) {
                    $val = $filter['eq'] ?? '';
                    if ($val) {
                        $select->where("$attrCode=$val");
                        $select->join(
                ['sets' => $setsTable],
                'sets.dd=ddf.ff',
                []
            );
                    }
                }
            }
        }

        return $select;
    }
}