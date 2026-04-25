<?php

namespace TCGCollective\ProductAttributesLink\Ui\DataProvider;

use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory;
use TCGCollective\ProductAttributesLink\Helper\SetsReleaseHelper;

class PlatformDatabaseGridDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * Product link collection
     *
     * @var \TCGCollective\ProductAttributesLink\Model\ResourceModel\CardGamesMapping\CollectionFactory
     */
    protected $collection;

    /**
     * @var \Magento\Ui\DataProvider\AddFieldToCollectionInterface[]
     */
    protected $addFieldStrategies;

    /**
     * @var \Magento\Ui\DataProvider\AddFilterToCollectionInterface[]
     */
    protected $addFilterStrategies;

    /**
     * @var MappingHelper
     */
    protected $setsReleaseHelper;
    protected $request;
    protected $redirect;
    protected $filterBuilder;

    /**
     * SellerDataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param MappingHelper $setsReleaseHelper
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute
     * @param array $addFieldStrategies
     * @param array $addFilterStrategies
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        SetsReleaseHelper $setsReleaseHelper,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Store\App\Response\Redirect $redirect,
        array $addFieldStrategies = [],
        array $addFilterStrategies = [],
        array $meta = [],
        array $data = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $meta,
            $data
        );
        $this->collection = $collectionFactory->create();
        $this->addFieldStrategies = $addFieldStrategies;
        $this->addFilterStrategies = $addFilterStrategies;
        $this->setsReleaseHelper = $setsReleaseHelper;
        $this->request = $request;
        $this->redirect = $redirect;
        $this->filterBuilder = $filterBuilder;

        $this->prepareUpdateUrl();
    }

    /**
     * @return void
     */
    protected function prepareUpdateUrl()
    {
        if (!isset($this->data['config']['filter_url_params'])) {
            return;
        }

        foreach ($this->data['config']['filter_url_params'] as $paramName => $paramValue) {
            if ('*' == $paramValue) {
                $paramValue = $this->request->getParam($paramName);
            }
            if ($paramValue) {
                $this->data['config']['update_url'] = sprintf(
                    '%s%s/%s/',
                    $this->data['config']['update_url'],
                    $paramName,
                    $paramValue
                );
                $this->addFilter(
                    $this->filterBuilder->setField($paramName)->setValue($paramValue)->setConditionType('eq')->create()
                );
            }
        }
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        $refererUrl = $this->redirect->getRefererUrl();

        // dd($refererUrl);
        if (str_contains($refererUrl, 'catalog/product/new') || str_contains($refererUrl, 'catalog/product/edit')) {
            $data = $this->setsReleaseHelper->getProductCreationSetsReleaseDataProvider();
            return [
                'totalRecords' => $data['totalRecords'],
                'items' => $data['items'],
            ];
        }

        // dd('ok');
        $data = [];

        $isAjax = $this->request->getParam('isAjax');
        if ($isAjax) {
            $data = $this->setsReleaseHelper->getPlatformDatabaseDataProvider();
        }

        return [
            'totalRecords' => $data ? $data['totalRecords'] : 0,
            'items' => $data ? $data['items'] : [],
        ];
    }

    /**
     * Add field to select
     *
     * @param string|array $field
     * @param string|null $alias
     * @return void
     */
    public function addField($field, $alias = null)
    {
        if (isset($this->addFieldStrategies[$field])) {
            $this->addFieldStrategies[$field]->addField($this->getCollection(), $field, $alias);
        } else {
            parent::addField($field, $alias);
        }
    }

    /**
     * @param \Magento\Framework\Api\Filter $filter
     */
    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        return $this;
    }
}
