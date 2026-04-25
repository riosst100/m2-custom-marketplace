<?php

declare(strict_types=1);

namespace TCGCollective\MarketplaceGraphQl\Model\Resolver\Product;

use Magento\CatalogGraphQl\Model\Resolver\Product\ProductFieldsSelector;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\BatchResolverInterface;
use Magento\Framework\GraphQl\Query\Resolver\BatchResponse;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\RelatedProductGraphQl\Model\DataProvider\RelatedProductDataProvider;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product as ProductDataProvider;
use Magento\Framework\Api\SearchCriteriaBuilder;

class CrossSeller implements BatchResolverInterface
{
    /**
     * @var ProductFieldsSelector
     */
    private $productFieldsSelector;

    /**
     * @var RelatedProductDataProvider
     */
    private $relatedProductDataProvider;

    /**
     * @var ProductDataProvider
     */
    private $productDataProvider;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param ProductFieldsSelector $productFieldsSelector
     * @param RelatedProductDataProvider $relatedProductDataProvider
     * @param ProductDataProvider $productDataProvider
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        ProductFieldsSelector $productFieldsSelector,
        RelatedProductDataProvider $relatedProductDataProvider,
        ProductDataProvider $productDataProvider,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->productFieldsSelector = $productFieldsSelector;
        $this->relatedProductDataProvider = $relatedProductDataProvider;
        $this->productDataProvider = $productDataProvider;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @inheritDoc
     */
    public function resolve(ContextInterface $context, Field $field, array $requests): BatchResponse
    {
        /** @var \Magento\Catalog\Api\Data\ProductInterface[] $products */
        $products = [];
        $fields = [];
        /** @var \Magento\Framework\GraphQl\Query\Resolver\BatchRequestItemInterface $request */
        foreach ($requests as $request) {
            //Gathering fields and relations to load.
            if (empty($request->getValue()['model'])) {
                throw new LocalizedException(__('"model" value should be specified'));
            }
            $products[] = $request->getValue()['model'];
            $fields[] = $this->productFieldsSelector->getProductFieldsFromInfo($request->getInfo(), $this->getNode());
        }
        $fields = array_unique(array_merge([], ...$fields));

        //Finding relations.
        $related = $this->findRelations($products, $fields, $this->getLinkType());

        //Matching requests with responses.
        $response = new BatchResponse();
        /** @var \Magento\Framework\GraphQl\Query\Resolver\BatchRequestItemInterface $request */
        foreach ($requests as $request) {
            /** @var \Magento\Catalog\Api\Data\ProductInterface $product */
            $product = $request->getValue()['model'];
            $result = [];
            if (array_key_exists($product->getId(), $related)) {
                $result = array_map(
                    function ($relatedProduct) {
                        $data = $relatedProduct->getData();
                        $data['model'] = $relatedProduct;

                        return $data;
                    },
                    $related[$product->getId()]
                );
            }
            $response->addResponse($request, $result);
        }

        return $response;
    }
}
