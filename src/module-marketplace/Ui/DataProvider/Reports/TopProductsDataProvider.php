<?php

namespace TCGCollective\MarketPlace\Ui\DataProvider\Reports;

use Lof\MarketPlace\Helper\Data;
use Lof\MarketPlace\Model\ResourceModel\SellerProduct\CollectionFactory;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider;

class TopProductsDataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \Lofmp\FavoriteSeller\Model\ResourceModel\Subscription\Collection
     */
    protected $collection;

    /**
     * @var Data
     */
    protected $helper;
    protected $request;
    protected $websiteRepository;
    protected $redirect;
    protected $_localeDate;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        ReportingInterface $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        FilterBuilder $filterBuilder,
        CollectionFactory $collectionFactory,
        \Magento\Store\Model\WebsiteRepository $websiteRepository,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        Data $helper,
        array $meta = [],
        array $data = []
    ) {
        $this->request = $request;
        $this->redirect = $redirect;
        $this->helper = $helper;
        $this->_localeDate = $localeDate;
        $this->websiteRepository = $websiteRepository;
        $this->collection = $collectionFactory->create();
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $meta,
            $data
        );

        $this->collection->addFieldToFilter('seller_id', $this->helper->getSellerId());

        $filters = $this->request->getParam('filters');
        if (!isset($filters['website_id'])) {
            $websiteId = '';

            $websiteCode = $this->request->getParam('country');
            if (!$websiteCode) {
                $refererUrl = $this->redirect->getRefererUrl();
                if ($refererUrl) {
                    if (preg_match('/country\/([a-z]+)/', $refererUrl, $matches)) {
                        $websiteCode = $matches[1];
                    }
                }
            }
            
            if ($websiteCode) {
                $website = $this->websiteRepository->get($websiteCode);
                if ($website) {
                    $websiteId = $website->getId();
                }
            }

            // dd($storeIds);
            if ($websiteId) {
                $this->collection->addFieldToFilter('main_table.website_id', ['eq' => $websiteId]);
            }
        }

        $this->collection->getSelect()->joinLeft(
            ['customer' => $this->collection->getTable('customer_entity')],
            'main_table.customer_id = customer.entity_id',
            [
                'firstname' => 'customer.firstname', 
                'lastname' => 'customer.lastname',
                'email' => 'customer.email'
            ]
        )->group(
            'main_table.id'
        );

        $this->collection->addFilterToMap('website_id', 'main_table.website_id');
        // $this->collection->addFilterToMap('email', 'customer.email');
    }


    /**
     * @return array
     */
    public function getData()
    {
        // dd($this->getCollection()->toArray());
        if (!$this->getCollection()->isLoaded()) {
            $this->getCollection()->load();
        }
        return $this->getCollection()->toArray();
    }

    /**
     * @return AbstractCollection
     */
    public function getCollection()
    {
        return $this->collection;
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
        // if (isset($this->addFieldStrategies[$field])) {
        //     $this->addFieldStrategies[$field]->addField($this->getCollection(), $field, $alias);
        // } else {
            // parent::addField($field, $alias);
        // }
    }

    /**
     * @param \Magento\Framework\Api\Filter $filter
     */
    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {
        if ($filter->getField() == "creation_time") {
            $filters = $this->request->getParam('filters');
            if (isset($filters[$filter->getField()]['from'])) {
                $from = $filters[$filter->getField()]['from'];
                if ($from) {
                    $from = $this->_localeDate->convertConfigTimeToUtc($from, 'Y-m-d 00:00:00');
                    $this->getCollection()->addFieldToFilter('creation_time', ['gteq' => $from]);
                }
            }

            if (isset($filters[$filter->getField()]['to'])) {
                $to = $filters[$filter->getField()]['to'];
                if ($to) {
                    $to = $this->_localeDate->convertConfigTimeToUtc($to, 'Y-m-d 00:00:00');
                    $this->getCollection()->addFieldToFilter('creation_time', ['lteq' => $to]);
                }
            }

            return $this;
        } else {
            // $this->getCollection()->addFieldToFilter($filter->getField(), [
            //     'like' => $filter->getValue()
            // ]);
            // // dd($this->getCollection()->getSelect()->__toString());
            // return $this;
            if (isset($this->addFilterStrategies[$filter->getField()])) {
                $this->addFilterStrategies[$filter->getField()]
                    ->addFilter(
                        $this->getCollection(),
                        $filter->getField(),
                        [$filter->getConditionType() => $filter->getValue()]
                    );
            } else {
                parent::addFilter($filter);
            }
        }
    }
}
