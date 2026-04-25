<?php

declare(strict_types=1);

namespace TCGCollective\MarketplaceGraphQl\Model\Resolver\Category;

use Magento\CatalogGraphQl\DataProvider\Product\SearchCriteriaBuilder;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\CatalogGraphQl\Model\Resolver\Products\Query\ProductQueryInterface;

class CustomSubCategory implements ResolverInterface
{
    /**
     * @var \TCGCollective\ProductAttributesLink\Helper\Data
     */
    private $mappingHelper;

    /**
     * @param \TCGCollective\ProductAttributesLink\Helper\Data $mappingHelper
     */
    public function __construct(
        \TCGCollective\ProductAttributesLink\Helper\Data $mappingHelper
    ) {
        $this->mappingHelper = $mappingHelper;
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
        // die(print_r($args,true));
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        $result = [];

        /* @var Category $category */
        $category = $value['model'];
        if ($category) {

            $parentCategory = $category->getParentCategory();

            // die('<pre>'.print_r(get_class_methods($parentCategory),true));

            // if ($category->getName() == "Brands") {
            //     $children = $category->getChildrenCategories();
            //     if ($children) {
            //         // die('<pre>'.print_r($children,true));
            //         $limit = 8;
            //         $x = 1;
            //         foreach($children as $subCategory) {
            //             if ($x++ < $limit) {
            //                 $result[] = [
            //                     'name' => $subCategory->getName(),
            //                     'path' => $subCategory->getRequestPath()
            //                 ];
            //             }
            //         }

            //         $result[] = [
            //             'name' => 'Akk = Games',
            //             'path' => $parentCategory->getUrlPath().'.html?show_subcategory=1'
            //         ];
            //     }
            // }

            if ($parentCategory->getName() == "Collectible Card Games") {
                $children = $parentCategory->getChildrenCategories();
                if ($children) {
                    // die('<pre>'.print_r($children,true));
                    $limit = 8;
                    $x = 1;
                    foreach($children as $subCategory) {
                        if ($x++ < $limit) {
                            $result[] = [
                                'name' => $subCategory->getName(),
                                'path' => $subCategory->getRequestPath()
                            ];
                        }
                    }

                    $result[] = [
                        'name' => 'More Games',
                        'path' => $parentCategory->getUrlPath().'.html?show_subcategory=1'
                    ];
                }
            }

            if ($parentCategory->getName() == "Sport Cards") {
                $children = $parentCategory->getChildrenCategories();
                if ($children) {
                    // die('<pre>'.print_r($children,true));
                    $limit = 8;
                    $x = 1;
                    foreach($children as $subCategory) {
                        if ($x++ < $limit) {
                            // if ($subCategory->getId() != $category->getId()) {
                                $result[] = [
                                    'name' => $subCategory->getName(),
                                    'path' => $subCategory->getRequestPath()
                                ];
                            // }
                        }
                    }

                    $result[] = [
                        'name' => 'More Cards',
                        'path' => $parentCategory->getUrlPath().'.html?show_subcategory=1'
                    ];
                }
            }

            if ($parentCategory->getName() == "Non Sports Cards") {
                $children = $parentCategory->getChildrenCategories();
                if ($children) {
                    // die('<pre>'.print_r($children,true));
                    $limit = 8;
                    $x = 1;
                    foreach($children as $subCategory) {
                        if ($x++ < $limit) {
                            // if ($subCategory->getId() != $category->getId()) {
                                $result[] = [
                                    'name' => $subCategory->getName(),
                                    'path' => $subCategory->getRequestPath()
                                ];
                            // }
                        }
                    }

                    if (count($children) > $limit) {
                        $result[] = [
                            'name' => 'More Cards',
                            'path' => $parentCategory->getUrlPath().'.html?show_subcategory=1'
                        ];
                    }
                }
            }

            // ACTION TOYS
            // die($parentCategory->getUrlPath());
            if ($parentCategory->getUrlPath() == "action-figures/categories") {
                $children = $parentCategory->getChildrenCategories();
                if ($children) {
                    // die('<pre>'.print_r($children,true));
                    $limit = 9;
                    $x = 1;
                    foreach($children as $subCategory) {
                        if ($x++ < $limit) {
                            // if ($subCategory->getId() != $category->getId()) {
                                $result[] = [
                                    'name' => $subCategory->getName(),
                                    'path' => $subCategory->getRequestPath()
                                ];
                            // }
                        }
                    }

                    if (count($children) > $limit) {
                        $result[] = [
                            'name' => 'More Categories',
                            'path' => $parentCategory->getUrlPath().'.html?show_subcategory=1'
                        ];
                    }
                }
            }

            if ($parentCategory->getUrlPath() == "fashion-dolls/fashion-dolls") {
                $children = $parentCategory->getChildrenCategories();
                if ($children) {
                    // die('<pre>'.print_r($children,true));
                    // $limit = 9;
                    // $x = 1;
                    foreach($children as $subCategory) {
                        // if ($x++ < $limit) {
                            // if ($subCategory->getId() != $category->getId()) {
                                $result[] = [
                                    'name' => $subCategory->getName(),
                                    'path' => $subCategory->getRequestPath()
                                ];
                            // }
                        // }
                    }

                    // if (count($children) > $limit) {
                    //     $result[] = [
                    //         'name' => 'More Dolls',
                    //         'path' => $parentCategory->getUrlPath().'.html?show_subcategory=1'
                    //     ];
                    // }
                }
            }

            if ($parentCategory->getName() == "Anime Figures") {
                $children = $parentCategory->getChildrenCategories();
                if ($children) {
                    // die('<pre>'.print_r($children,true));
                    $limit = 9;
                    $x = 1;
                    foreach($children as $subCategory) {
                        if ($x++ < $limit) {
                            // if ($subCategory->getId() != $category->getId()) {
                                $result[] = [
                                    'name' => $subCategory->getName(),
                                    'path' => $subCategory->getRequestPath()
                                ];
                            // }
                        }
                    }

                    if (count($children) > $limit) {
                        $result[] = [
                            'name' => 'More Figures',
                            'path' => $parentCategory->getUrlPath().'.html?show_subcategory=1'
                        ];
                    }
                }
            }

            if ($parentCategory->getName() == "Layout") {
                $children = $parentCategory->getChildrenCategories();
                if ($children) {
                    foreach($children as $subCategory) {
                        $result[] = [
                            'name' => $subCategory->getName(),
                            'path' => $subCategory->getRequestPath()
                        ];
                    }
                }

                $result[] = [
                    'name' => 'All',
                    'path' => $parentCategory->getUrlPath().'.html'
                ];
            }

            if ($parentCategory->getName() == "Tools & Supplies") {
                $children = $parentCategory->getChildrenCategories();
                if ($children) {
                    foreach($children as $subCategory) {
                        $result[] = [
                            'name' => $subCategory->getName(),
                            'path' => $subCategory->getRequestPath()
                        ];
                    }
                }

                $result[] = [
                    'name' => 'All',
                    'path' => $parentCategory->getUrlPath().'.html'
                ];
            }

            if ($category->getParentCategory()->getName() == "Miniature Games") {
                $result[] = [
                    'name' => 'Sets',
                    'path' => $category->getUrlPath().'.html?shopby=sets'
                ];

                $result[] = [
                    'name' => 'Singles',
                    'path' => $category->getUrlPath().'.html?shopby=singles'
                ];
            }

            // LEGO
            if ($parentCategory->getName() == "Lego") {
                $children = $parentCategory->getChildrenCategories();
                if ($children) {
                    // die('<pre>'.print_r($children,true));
                    $limit = 9;
                    $x = 1;
                    foreach($children as $subCategory) {
                        if ($x++ < $limit) {
                            // if ($subCategory->getId() != $category->getId()) {
                                $result[] = [
                                    'name' => $subCategory->getName(),
                                    'path' => $subCategory->getRequestPath()
                                ];
                            // }
                        }
                    }

                    if (count($children) > $limit) {
                        $result[] = [
                            'name' => 'More Lego',
                            'path' => $parentCategory->getUrlPath().'.html?show_subcategory=1'
                        ];
                    }
                }
            }
        }

        return $result;
    }
}
