<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace TCGCollective\MarketplaceGraphQl\Model\Resolver\Category;

use Magento\CatalogGraphQl\Model\Resolver\Category\DataProvider\Breadcrumbs as BreadcrumbsDataProvider;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;

/**
 * Retrieves breadcrumbs
 */
class Breadcrumbs extends \Magento\CatalogGraphQl\Model\Resolver\Category\Breadcrumbs
{
    /**
     * @var BreadcrumbsDataProvider
     */
    private $breadcrumbsDataProvider;

    /**
     * @param BreadcrumbsDataProvider $breadcrumbsDataProvider
     */
    public function __construct(
        BreadcrumbsDataProvider $breadcrumbsDataProvider
    ) {
        parent::__construct($breadcrumbsDataProvider);
        $this->breadcrumbsDataProvider = $breadcrumbsDataProvider;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value['path'])) {
            throw new LocalizedException(__('"path" value should be specified'));
        }

        $urlPath = $value['url_path'] ?? null;

        $breadcrumbsData = $this->breadcrumbsDataProvider->getData($value['path'], $urlPath);
        return count($breadcrumbsData) ? $breadcrumbsData : null;
    }
}
