<?php

namespace TCGCollective\MarketPlace\Helper;

class MainCategoriesHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_scopeConfig;
    protected $request;

    protected $myProductsUrlKey = null;
    protected $myProductsTabsUrlKey = null;
    protected $myProductsTabsAttrCode = null;
    protected $myProductsTabs = null;

    // protected $myProductsTabsAttrCode = null;
    // protected $myProductsTabsAttrCode = null;
    // protected $myProductsTabsAttrCode = null;
    // protected $myProductsTabsAttrCode = null;
    // protected $myProductsTabsAttrCode = null;
    // protected $myProductsTabsAttrCode = null;
    // protected $myProductsTabsAttrCode = null;
    // protected $myProductsTabsAttrCode = null;
    // protected $myProductsTabsAttrCode = null;
    // protected $myProductsTabsAttrCode = null;


    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\RequestInterface $request
    ) {
        parent::__construct(
            $context
        );

        $this->_scopeConfig = $context->getScopeConfig();   
        $this->request = $request;
    }

    public function getTabsByLabel($tabs, $labelDicari) 
    {
        $hasil = array_filter($tabs, function($item) use ($labelDicari) {
             // ubah Phrase ke string dulu
            $labelText = (string) $item['label']; // atau $item['label']->getText()
            return $labelText === $labelDicari;
        });
        
        if ($hasil) {
            // Ambil elemen pertama (jika hanya satu yang cocok)
            return reset($hasil);
        }
        
        return null;
    }

    public function getMainCategoriesDataByUrlKey($urlKey) 
    {
        $mainCategoriesData = $this->getMainCategoriesData();
        if ($mainCategoriesData) {
            $hasil = array_filter($mainCategoriesData, function($item) use ($urlKey) {
                // ubah Phrase ke string dulu
                $labelText = $item['myProductsUrlKey']; // atau $item['label']->getText()
                return $labelText === $urlKey;
            });

            if ($hasil) {
                // Ambil elemen pertama (jika hanya satu yang cocok)
                return reset($hasil);
            }
        }

        return null;
    }

    public function getMainCategoriesDataByCategories($categoryName) 
    {
        $mainCategoriesData = $this->getMainCategoriesData();
        if ($mainCategoriesData) {
            $hasil = array_filter($mainCategoriesData, function($item) use ($categoryName) {
                // ubah Phrase ke string dulu
                $labelText = (string)$item['categories'];
                return $labelText === $categoryName;
            });

            if ($hasil) {
                // Ambil elemen pertama (jika hanya satu yang cocok)
                return reset($hasil);
            }
        }

        return null;
    }

    public function getMainAttrCodes() 
    {
        $mainCategoriesData = $this->getMainCategoriesData();
        if ($mainCategoriesData) {
            $mainAttrCodes = array_column($mainCategoriesData, 'mainAttrCode');
            // Hapus duplikat dan kosong
            $mainAttrCodes = array_filter(array_unique($mainAttrCodes), function($v) {
                return !empty(trim($v));
            });

            // Reindex array (optional)
            $mainAttrCodes = array_values($mainAttrCodes);
            return $mainAttrCodes;
        }

        return null;
    }

    public function getMainAttrCodesWithUrlKeyMapping() 
    {
        $mainCategoriesData = $this->getMainCategoriesData();
        if ($mainCategoriesData) {
            // Buat mapping otomatis
            $mapping = [];
            foreach ($mainCategoriesData as $item) {
                if (!empty($item['myProductsUrlKey']) && !empty($item['mainAttrCode'])) {
                    $mapping[$item['myProductsUrlKey']] = $item['mainAttrCode'];
                }
            }

            return $mapping;
        }

        return null;
    }

    public function getTabsByUrlKey($tabs, $urlKey) 
    {
        if ($tabs) {
            $hasil = array_filter($tabs, function($item) use ($urlKey) {
                // ubah Phrase ke string dulu
                $labelText = (string) $item['value']; // atau $item['label']->getText()
                return $labelText === $urlKey;
            });

            if ($hasil) {
                // Ambil elemen pertama (jika hanya satu yang cocok)
                return reset($hasil);
            }
        }

        return null;
    }

    public function getCategoriesByUrlKey($urlKey) {
        $mainCategoriesData = $this->getMainCategoriesData();
        if ($mainCategoriesData) {
            $hasil = array_filter($mainCategoriesData, function($item) use ($urlKey) {
                // ubah Phrase ke string dulu
                // dd($item);
                $myProductsUrlKey = $item['myProductsUrlKey'];
                $platformDatabaseUrlKey = $item['platformDatabaseUrlKey'] ?? null; // atau $item['label']->getText()
                return $urlKey === $myProductsUrlKey || $urlKey === $platformDatabaseUrlKey;
            });

            // dd($hasil);

            if ($hasil) {
                // Ambil elemen pertama (jika hanya satu yang cocok)
                return reset($hasil)['categories'];
            }
        }

        return null;
    }

    public function getMainCategoriesData() 
    {
        return [
            [
                'categories' => 'Action Toys',
                'mainAttrCode' => 'toys_brand',
                'myProductsUrlKey' => 'actiontoys',
                'platformDatabaseUrlKey' => 'actiontoys',
                'myProductsTabsAttrCode' => 'action_toys_categories',
                'myProductsTabs' => [
                    [
                        'label' => 'Action Figures',
                        'value' => 'index',
                        'attribute_codes' => [
                            'set_name',
                            'toys_line',
                            'set_item_number',
                            'toys_franchise',
                            'toys_series',
                            'toys_characters'
                        ]
                    ],
                    [
                        'label' => 'Statues',
                        'value' => 'statues'
                    ],
                    [
                        'label' => 'Busts',
                        'value' => 'busts'
                    ],
                    [
                        'label' => 'Props & Replica',
                        'value' => 'propsreplica'
                    ],
                    [
                        'label' => 'Mini Figures',
                        'value' => 'minifigures'
                    ],
                    [
                        'label' => 'Robot/Mech Figures',
                        'value' => 'robotmechfigures'
                    ],
                    [
                        'label' => 'Blindboxes (Box Set/Case)',
                        'value' => 'blindboxes'
                    ],
                    [
                        'label' => 'Blindboxes (Singles)',
                        'value' => 'blindboxessingles'
                    ],
                    [
                        'label' => 'Designer Toys',
                        'value' => 'designertoys'
                    ]
                ]
            ],
            [
                'categories' => 'Action Toys Model Kits',
                'mainAttrCode' => 'toys_brand',
                'myProductsUrlKey' => 'actiontoysmodelkits',
                'myProductsTabsAttrCode' => 'action_toys_model_kits_categories',
                'myProductsTabs' => [
                    [
                        'label' => 'Character Kits',
                        'value' => 'index'
                    ],
                    [
                        'label' => 'Props & Replica Kits',
                        'value' => 'propsreplicakits'
                    ],
                    [
                        'label' => 'Robot/Mecha Kits',
                        'value' => 'robotmechakits'
                    ],
                    [
                        'label' => 'Monsters Kits',
                        'value' => 'monsterskits'
                    ]
                ]
            ],
            [
                'categories' => 'Anime Toys',
                'mainAttrCode' => 'toys_brand',
                'myProductsUrlKey' => 'animetoys',
                'myProductsTabsAttrCode' => 'anime_toys_categories',
                'myProductsTabs' => [
                    [
                        'label' => __('Anime Figures'),
                        'is_active' => false,
                        'value' => 'index'
                    ],
                    [
                        'label' => __('Robot/Mech Figures'),
                        'is_active' => false,
                        'value' => 'robotmechfigures'
                    ],
                    [
                        'label' => __('Statues'),
                        'is_active' => false,
                        'value' => 'statues'
                    ],
                    [
                        'label' => __('Busts'),
                        'is_active' => false,
                        'value' => 'busts'
                    ],
                    [
                        'label' => __('Props & Replica'),
                        'is_active' => false,
                        'value' => 'propsreplica'
                    ],
                    [
                        'label' => __('Mini Figures'),
                        'is_active' => false,
                        'value' => 'minifigures'
                    ],
                    [
                        'label' => __('Gashapon'),
                        'is_active' => false,
                        'value' => 'gashapon'
                    ],
                    [
                        'label' => __('Candy Toys'),
                        'is_active' => false,
                        'value' => 'candytoys'
                    ],
                    [
                        'label' => __('R18+ Toys'),
                        'is_active' => false,
                        'value' => 'r18toys'
                    ],
                ]
            ],
            [
                'categories' => 'Anime Model Kits',
                'mainAttrCode' => 'toys_brand',
                'myProductsUrlKey' => 'animemodelkits',
                'myProductsTabsAttrCode' => 'anime_model_kits_categories',
                'myProductsTabs' => [
                    [
                        'label' => __('Character Kits'),
                        'is_active' => false,
                        'value' => 'index'
                    ],
                    [
                        'label' => __('Robot/Mecha Kits'),
                        'is_active' => false,
                        'value' => 'robotmechakits'
                    ],
                    [
                        'label' => __('Props & Replica Kits'),
                        'is_active' => false,
                        'value' => 'propsreplicakits'
                    ],
                    [
                        'label' => __('Monsters/Kaiju Kits'),
                        'is_active' => false,
                        'value' => 'monsterskits'
                    ]
                ]
            ],
            [
                'categories' => 'Gundam',
                'mainAttrCode' => 'toys_brand',
                'myProductsUrlKey' => 'gundam',
                'myProductsTabsAttrCode' => 'gundam_categories',
                'myProductsTabs' => [
                    [
                        'label' => __('Gunpla (Model Kits)'),
                        'is_active' => false,
                        'value' => 'index'
                    ],
                    [
                        'label' => __('Gundam Figures'),
                        'is_active' => false,
                        'value' => 'gundamfigures'
                    ],
                    [
                        'label' => __('Accessories'),
                        'is_active' => false,
                        'value' => 'accessories'
                    ]
                ]
            ],
            [
                'categories' => 'Fun Goods',
                'mainAttrCode' => 'toys_brand',
                'myProductsUrlKey' => 'fungoods',
                'myProductsTabsAttrCode' => 'anime_fun_goods_categories',
                'myProductsTabs' => [
                    [
                        'label' => __('Acrylic Stands'),
                        'is_active' => false,
                        'value' => 'index'
                    ],
                    [
                        'label' => __('Badges/Pins'),
                        'is_active' => false,
                        'value' => 'badgespins'
                    ],
                    [
                        'label' => __('Stickers'),
                        'is_active' => false,
                        'value' => 'stickers'
                    ]
                ]
            ],
            [
                'categories' => 'Fashion Dolls',
                'mainAttrCode' => 'toys_brand',
                'myProductsUrlKey' => 'fashiondolls',
                'myProductsTabsAttrCode' => 'dolls_fashion_dolls_categories',
                'myProductsTabs' => [
                    [
                        'label' => __('Complete Dolls'),
                        'is_active' => false,
                        'value' => 'index'
                    ],
                    [
                        'label' => __('Doll Parts'),
                        'is_active' => false,
                        'value' => 'dollparts'
                    ],
                    [
                        'label' => __('Eyes'),
                        'is_active' => false,
                        'value' => 'eyes'
                    ],
                    [
                        'label' => __('Fashion'),
                        'is_active' => false,
                        'value' => 'fashion'
                    ],
                    [
                        'label' => __('Wigs & Hair'),
                        'is_active' => false,
                        'value' => 'wigshair'
                    ]
                ]
            ],
            [
                'categories' => 'Doll Accessories',
                'mainAttrCode' => 'toys_brand',
                'myProductsUrlKey' => 'dollsaccessories',
                'myProductsTabsAttrCode' => 'dolls_accessories_categories',
                'myProductsTabs' => [
                    [
                        'label' => __('Make Up'),
                        'is_active' => false,
                        'value' => 'index'
                    ],
                    [
                        'label' => __('Eyelashes'),
                        'is_active' => false,
                        'value' => 'eyelashes'
                    ],
                    [
                        'label' => __('Jewellery'),
                        'is_active' => false,
                        'value' => 'jewellery'
                    ],
                    [
                        'label' => __('Doll Stands'),
                        'is_active' => false,
                        'value' => 'dollstands'
                    ],
                    [
                        'label' => __('Carry Cases'),
                        'is_active' => false,
                        'value' => 'carrycases'
                    ],
                    [
                        'label' => __('Furniture'),
                        'is_active' => false,
                        'value' => 'furniture'
                    ]
                ]
            ],
            [
                'categories' => 'Collectible Card Games',
                'mainAttrCode' => 'card_game',
                'myProductsUrlKey' => 'cardgame',
                'platformDatabaseUrlKey' => 'cardgames',
                'myProductsTabsAttrCode' => 'card_product_type',
                'preOrdersAttrCode' => 'card_pre_orders',
                'myProductsTabs' => [
                    [
                        'label' => __('Sealed Products'),
                        'is_active' => false,
                        'value' => 'index',
                        'attribute_codes' => [
                            'card_set',
                            'card_series',
                            'card_release',
                            'sealed_products'
                        ]
                    ],
                    [
                        'label' => __('Singles'),
                        'is_active' => false,
                        'value' => 'singles',
                        'attribute_codes' => [
                            'card_set',
                            'card_name'
                        ]
                    ],
                    [
                        'label' => __('Graded'),
                        'is_active' => false,
                        'value' => 'graded',
                        'attribute_codes' => [
                            'card_set',
                            'card_name'
                        ]
                    ],
                    [
                        'label' => __('Lots/Sets'),
                        'is_active' => false,
                        'value' => 'lots'
                    ],
                    [
                        'label' => __('Supplies'),
                        'is_active' => false,
                        'value' => 'supplies'
                    ],
                    [
                        'label' => __('Pre Orders'),
                        'is_active' => false,
                        'value' => 'preorders'
                    ],
                ]
            ],
            [
                'categories' => 'Living Card Games',
                'mainAttrCode' => 'living_card_game',
                'myProductsUrlKey' => 'livingcardgames',
                'platformDatabaseUrlKey' => 'livingcardgame',
                'myProductsTabsAttrCode' => 'living_card_product_type',
                'myProductsTabs' => [
                    [
                        'label' => __('Box Sets'),
                        'is_active' => false,
                        'value' => 'index',
                        'attribute_codes' => [
                            "living_card_release",
                            "living_card_release_type",
                            "living_card_series",
                            "living_card_set",
                            "living_card_set_abreviation",
                            "living_card_set_type",
                            "living_card_sealed_products"
                        ]
                    ],
                    [
                        'label' => __('Singles'),
                        'is_active' => false,
                        'value' => 'singles',
                        'attribute_codes' => [
                            "living_card_name",
                            "living_card_number",
                            "living_card_set",
                            "living_card_set_abreviation",
                            "living_card_set_type"
                        ]
                    ],
                    [
                        'label' => __('Supplies'),
                        'is_active' => false,
                        'value' => 'supplies'
                    ],
                    [
                        'label' => __('Pre Orders'),
                        'is_active' => false,
                        'value' => 'preorders',
                        'attribute_codes' => [
                            "living_card_release",
                            "living_card_release_type",
                            "living_card_series",
                            "living_card_set",
                            "living_card_set_abreviation",
                            "living_card_set_type",
                            "living_card_sealed_products"
                        ]
                    ]
                ]
            ],
            [
                'categories' => 'Miniature Games',
                'mainAttrCode' => 'miniature_game',
                'myProductsUrlKey' => 'miniaturegames',
                'platformDatabaseUrlKey' => 'miniaturegame',
                'myProductsTabsAttrCode' => 'miniature_product_type',
                'myProductsTabs' => [
                    [
                        'label' => __('Sealed Products'),
                        'is_active' => false,
                        'value' => 'index'
                    ],
                    [
                        'label' => __('Singles'),
                        'is_active' => false,
                        'value' => 'singles'
                    ]
                ]
            ],
            [
                'categories' => 'Games Workshop',
                'mainAttrCode' => 'gw_miniature_game',
                'myProductsUrlKey' => 'gamesworkshop',
                'myProductsTabsAttrCode' => 'gw_miniature_product_type',
                'myProductsTabs' => [
                    [
                        'label' => __('Starter Sets'),
                        'is_active' => false,
                        'value' => 'index',
                        'attribute_codes' => [
                            'miniature_set',
                            'miniature_set_item_no',
                            'miniature_faction',
                            'miniature_sub_faction',
                            'miniature_armies',
                            'miniature_battlefield_role'
                        ]
                    ],
                    [
                        'label' => __('Miniature Sets'),
                        'is_active' => false,
                        'value' => 'miniaturesets',
                        'attribute_codes' => [
                            'miniature_set',
                            'miniature_set_item_no',
                            'miniature_faction',
                            'miniature_sub_faction',
                            'miniature_armies',
                            'miniature_battlefield_role'
                        ]
                    ],
                    [
                        'label' => __('Bundles'),
                        'is_active' => false,
                        'value' => 'bundles',
                        'attribute_codes' => [
                            'miniature_set',
                            'miniature_set_item_no',
                            'miniature_faction',
                            'miniature_sub_faction',
                            'miniature_armies',
                            'miniature_battlefield_role'
                        ]
                    ],
                    [
                        'label' => __('Figures'),
                        'is_active' => false,
                        'value' => 'figures',
                        'attribute_codes' => [
                            'miniature_set',
                            'miniature_set_item_no',
                            'miniature_faction',
                            'miniature_sub_faction',
                            'miniature_armies',
                            'miniature_battlefield_role'
                        ]
                    ],
                    [
                        'label' => __('Accessories'),
                        'is_active' => false,
                        'value' => 'accessories'
                    ]
                ]
            ],
            [
                'categories' => 'Sport Cards',
                'mainAttrCode' => 'sc_categories',
                'myProductsUrlKey' => 'sportcards',
                'myProductsTabsAttrCode' => 'sc_product_type',
                'myProductsTabs' => [
                    [
                        'label' => __('Sealed Products'),
                        'is_active' => false,
                        'value' => 'index'
                    ],
                    [
                        'label' => __('Singles'),
                        'is_active' => false,
                        'value' => 'singles'
                    ],
                    [
                        'label' => __('Lots/Sets'),
                        'is_active' => false,
                        'value' => 'lots'
                    ],
                    [
                        'label' => __('Graded'),
                        'is_active' => false,
                        'value' => 'graded'
                    ],
                    [
                        'label' => __('Pre Orders'),
                        'is_active' => false,
                        'value' => 'preorders'
                    ]
                ]
            ],
            [
                'categories' => 'Non Sports Cards',
                'mainAttrCode' => 'nsc_categories',
                'myProductsUrlKey' => 'nonsportcards',
                'myProductsTabsAttrCode' => 'nsc_product_type',
                'myProductsTabs' => [
                    [
                        'label' => __('Sealed Products'),
                        'is_active' => false,
                        'value' => 'index'
                    ],
                    [
                        'label' => __('Singles'),
                        'is_active' => false,
                        'value' => 'singles'
                    ],
                    [
                        'label' => __('Lots/Sets'),
                        'is_active' => false,
                        'value' => 'lots'
                    ],
                    [
                        'label' => __('Graded'),
                        'is_active' => false,
                        'value' => 'graded'
                    ],
                    [
                        'label' => __('Pre Orders'),
                        'is_active' => false,
                        'value' => 'preorders'
                    ]
                ]
            ],
            [
                'categories' => 'Bricks',
                'mainAttrCode' => 'bricks_brand',
                'myProductsUrlKey' => 'bricks',
                'myProductsTabsAttrCode' => 'bricks_categories',
                'myProductsTabs' => [
                    [
                        'label' => __('Box Sets/Packs'),
                        'is_active' => false,
                        'value' => 'index'
                    ],
                    [
                        'label' => __('Minifigures'),
                        'is_active' => false,
                        'value' => 'singles'
                    ],
                    [
                        'label' => __('Parts'),
                        'is_active' => false,
                        'value' => 'parts'
                    ],
                    [
                        'label' => __('Accessories'),
                        'is_active' => false,
                        'value' => 'accessories'
                    ]
                ]
            ],
            [
                'categories' => 'Model Kits Military',
                'mainAttrCode' => 'model_kits_brand',
                'myProductsUrlKey' => 'modelkitsmilitary',
                'myProductsTabsAttrCode' => 'model_kits_military_vehicles_categories',
                'myProductsTabs' => [
                    [
                        'label' => __('Military Aircrafts'),
                        'is_active' => false,
                        'value' => 'index'
                    ],
                    [
                        'label' => __('Military Helicopters'),
                        'is_active' => false,
                        'value' => 'militaryhelicopters'
                    ],
                    [
                        'label' => __('Military Vehicles'),
                        'is_active' => false,
                        'value' => 'militaryvehicles'
                    ],
                    [
                        'label' => __('Military Tanks'),
                        'is_active' => false,
                        'value' => 'militarytanks'
                    ],
                    [
                        'label' => __('Military Artillery'),
                        'is_active' => false,
                        'value' => 'militaryartillery'
                    ],
                    [
                        'label' => __('Military Ships'),
                        'is_active' => false,
                        'value' => 'militaryships'
                    ],
                    [
                        'label' => __('Military Submarines'),
                        'is_active' => false,
                        'value' => 'militarysubmarines'
                    ]
                ]
            ],
            [
                'categories' => 'Model Kits Aircrafts',
                'mainAttrCode' => 'model_kits_brand',
                'myProductsUrlKey' => 'modelkitsaircrafts',
                'myProductsTabsAttrCode' => 'model_kits_aircrafts_categories',
                'myProductsTabs' => [
                    [
                        'label' => __('Aircrafts'),
                        'is_active' => false,
                        'value' => 'index'
                    ],
                    [
                        'label' => __('Helicopters'),
                        'is_active' => false,
                        'value' => 'helicopters'
                    ]
                ]
            ],
            [
                'categories' => 'Model Kits Vehicles',
                'mainAttrCode' => 'model_kits_brand',
                'myProductsUrlKey' => 'modelkitsvehicles',
                'myProductsTabsAttrCode' => 'model_kits_vehicles_categories',
                'myProductsTabs' => [
                    [
                        'label' => __('Road Cars'),
                        'is_active' => false,
                        'value' => 'index'
                    ],
                    [
                        'label' => __('Formula Racing'),
                        'is_active' => false,
                        'value' => 'formularacing'
                    ],
                    [
                        'label' => __('Motorsports'),
                        'is_active' => false,
                        'value' => 'motorsports'
                    ],
                    [
                        'label' => __('Bikes'),
                        'is_active' => false,
                        'value' => 'bikes'
                    ],
                    [
                        'label' => __('Emergency Services'),
                        'is_active' => false,
                        'value' => 'emergencyservices'
                    ],
                    [
                        'label' => __('Heavy Machinery'),
                        'is_active' => false,
                        'value' => 'heavymachinery'
                    ],
                    [
                        'label' => __('Transport Vehicles'),
                        'is_active' => false,
                        'value' => 'transportvehicles'
                    ],
                    [
                        'label' => __('TV/Movies Vehicles'),
                        'is_active' => false,
                        'value' => 'tvmoviesvehicles'
                    ]
                ]
            ],
            [
                'categories' => 'Diecast Accessories',
                'mainAttrCode' => 'diecast_brand',
                'myProductsUrlKey' => 'diecastaccessories',
                'myProductsTabsAttrCode' => 'diecast_accessories_categories',
                'myProductsTabs' => [
                    [
                        'label' => __('Helmets'),
                        'is_active' => false,
                        'value' => 'index'
                    ],
                    [
                        'label' => __('Engines'),
                        'is_active' => false,
                        'value' => 'engines'
                    ],
                    [
                        'label' => __('Figures'),
                        'is_active' => false,
                        'value' => 'figures'
                    ],
                    [
                        'label' => __('Wheels'),
                        'is_active' => false,
                        'value' => 'wheels'
                    ],
                    [
                        'label' => __('Others'),
                        'is_active' => false,
                        'value' => 'others'
                    ]
                ]
            ],
            [
                'categories' => 'Diecast Military',
                'mainAttrCode' => 'diecast_brand',
                'myProductsUrlKey' => 'diecastmilitary',
                'myProductsTabsAttrCode' => 'diecast_military_vehicles_categories',
                'myProductsTabs' => [
                    [
                        'label' => __('Military Aircrafts'),
                        'is_active' => false,
                        'value' => 'index'
                    ],
                    [
                        'label' => __('Military Helicopters'),
                        'is_active' => false,
                        'value' => 'militaryhelicopters'
                    ],
                    [
                        'label' => __('Military Tanks'),
                        'is_active' => false,
                        'value' => 'militarytanks'
                    ],
                    [
                        'label' => __('Military Vehicles'),
                        'is_active' => false,
                        'value' => 'militaryvehicles'
                    ],
                    [
                        'label' => __('Military Artillery'),
                        'is_active' => false,
                        'value' => 'militaryartillery'
                    ],
                    [
                        'label' => __('Military Ships'),
                        'is_active' => false,
                        'value' => 'militaryships'
                    ],
                    [
                        'label' => __('Military Submarines'),
                        'is_active' => false,
                        'value' => 'militarysubmarines'
                    ]
                ]
            ],
            [
                'categories' => 'Diecast Aircrafts',
                'mainAttrCode' => 'diecast_brand',
                'myProductsUrlKey' => 'diecastaircrafts',
                'myProductsTabsAttrCode' => 'diecast_aircrafts_categories',
                'myProductsTabs' => [
                    [
                        'label' => __('Aircrafts'),
                        'is_active' => false,
                        'value' => 'index'
                    ],
                    [
                        'label' => __('Helicopters'),
                        'is_active' => false,
                        'value' => 'helicopters'
                    ]
                ]
            ],
            [
                'categories' => 'Diecast Vehicles',
                'mainAttrCode' => 'diecast_brand',
                'myProductsUrlKey' => 'diecastvehicles',
                'myProductsTabsAttrCode' => 'diecast_vehicles_categories',
                'myProductsTabs' => [
                    [
                        'label' => __('Road Cars'),
                        'is_active' => false,
                        'value' => 'index'
                    ],
                    [
                        'label' => __('Formula Racing'),
                        'is_active' => false,
                        'value' => 'formularacing'
                    ],
                    [
                        'label' => __('Motorsports'),
                        'is_active' => false,
                        'value' => 'motorsports'
                    ],
                    [
                        'label' => __('Bikes'),
                        'is_active' => false,
                        'value' => 'bikes'
                    ],
                    [
                        'label' => __('Emergency Services'),
                        'is_active' => false,
                        'value' => 'emergencyservices'
                    ],
                    [
                        'label' => __('Heavy Machinery'),
                        'is_active' => false,
                        'value' => 'heavymachinery'
                    ],
                    [
                        'label' => __('Transport Vehicles'),
                        'is_active' => false,
                        'value' => 'transportvehicles'
                    ],
                    [
                        'label' => __('TV/Movies Vehicles'),
                        'is_active' => false,
                        'value' => 'tvmoviesvehicles'
                    ]
                ]
            ],
            [
                'categories' => 'Trains',
                'mainAttrCode' => 'railway_brand',
                'myProductsUrlKey' => 'railway',
                'myProductsTabsAttrCode' => 'railway_categories',
                'myProductsTabs' => [
                    [
                        'label' => __('Sets & Packs'),
                        'is_active' => false,
                        'value' => 'index'
                    ],
                    [
                        'label' => __('Locomotives'),
                        'is_active' => false,
                        'value' => 'locomotives'
                    ],
                    [
                        'label' => __('Freight Cars'),
                        'is_active' => false,
                        'value' => 'freightcars'
                    ],
                    [
                        'label' => __('Train Parts'),
                        'is_active' => false,
                        'value' => 'parts'
                    ],
                    [
                        'label' => __('Power & Control'),
                        'is_active' => false,
                        'value' => 'powercontrol'
                    ],
                    [
                        'label' => __('Tracks'),
                        'is_active' => false,
                        'value' => 'tracks'
                    ],
                    [
                        'label' => __('Passenger Cars'),
                        'is_active' => false,
                        'value' => 'passengercars'
                    ]
                ]
            ],
            [
                'categories' => 'Trains Accessories',
                'mainAttrCode' => 'railway_brand',
                'myProductsUrlKey' => 'railwayaccessories',
                'myProductsTabsAttrCode' => 'railway_accessories_categories',
                'myProductsTabs' => [
                    [
                        'label' => __('Buildings & Structures'),
                        'is_active' => false,
                        'value' => 'index'
                    ],
                    [
                        'label' => __('Figures'),
                        'is_active' => false,
                        'value' => 'figures'
                    ],
                    [
                        'label' => __('Lightings'),
                        'is_active' => false,
                        'value' => 'lightings'
                    ],
                    [
                        'label' => __('Sceneries'),
                        'is_active' => false,
                        'value' => 'sceneries'
                    ],
                    [
                        'label' => __('Signals & Signs'),
                        'is_active' => false,
                        'value' => 'signalssigns'
                    ],
                    [
                        'label' => __('Vehicles & Roads'),
                        'is_active' => false,
                        'value' => 'vehiclesroads'
                    ]
                ]
            ],
            [
                'categories' => 'Hobby Supplies',
                'mainAttrCode' => 'hobby_supplies_brand',
                'myProductsUrlKey' => 'hobbysupplies',
                'myProductsTabsAttrCode' => 'hobby_supplies_categories',
                'myProductsTabs' => [
                    [
                        'label' => __('Paints'),
                        'is_active' => false,
                        'value' => 'index'
                    ],
                    [
                        'label' => __('Painting Tools'),
                        'is_active' => false,
                        'value' => 'paintingtools'
                    ],
                    [
                        'label' => __('Tools'),
                        'is_active' => false,
                        'value' => 'tools'
                    ],
                    [
                        'label' => __('Airbrush'),
                        'is_active' => false,
                        'value' => 'airbrush'
                    ],
                    [
                        'label' => __('Finishing Aids'),
                        'is_active' => false,
                        'value' => 'finishingaids'
                    ],
                    [
                        'label' => __('Glues & Adhesives'),
                        'is_active' => false,
                        'value' => 'gluesadhesives'
                    ]
                ]
            ],
            [
                'categories' => 'Cards & Games',
                'mainAttrCode' => 'hobby_supplies_brand',
                'myProductsUrlKey' => 'hobbysuppliescardsgames',
                'myProductsTabsAttrCode' => 'hobby_supplies_cards_games_categories',
                'myProductsTabs' => [
                    [
                        'label' => __('Cards'),
                        'is_active' => false,
                        'value' => 'index'
                    ],
                    [
                        'label' => __('Games'),
                        'is_active' => false,
                        'value' => 'games'
                    ]
                ]
            ],
        ];
    }
}