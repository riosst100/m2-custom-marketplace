<?php

namespace TCGCollective\MarketPlace\Ui\DataProvider;

use \Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory;
use TCGCollective\ProductAttributesLink\Helper\Data as MappingHelper;

use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Customer\Ui\Component\Listing\AttributeRepository;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\Reporting;

class SidebarFiltersAttributes extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * Product link collection
     *
     * @var \TCGCollective\ProductAttributesLink\Model\ResourceModel\CardGamesSets\CollectionFactory
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
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    protected $filterBuilder;

    /**
     * SellerDataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
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
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
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
        $this->request = $request;
        $this->filterBuilder = $filterBuilder;

        $this->collection->addFieldToFilter('frontend_input', ['eq' => 'select']);
        $this->collection->addFieldToFilter('entity_type_id', ['eq' => 4]);
        // Join ke tabel catalog_eav_attribute
        $this->collection->getSelect()->join(
            ['catalog_attr' => $this->collection->getTable('catalog_eav_attribute')],
            'main_table.attribute_id = catalog_attr.attribute_id',
            ['position','is_master_attribute'] // ambil kolom tambahan kalau mau
        );

        // Filter hanya atribut dengan is_filterable = 1
        $this->collection->addFieldToFilter('catalog_attr.is_filterable', ['in' => [1, 2]]);

        $this->collection->setOrder('attribute_code', 'ASC');
    }
    
    // namespace Magento\Customer\Ui\Component;

    // use Magento\Customer\Api\Data\AttributeMetadataInterface;
    // use Magento\Customer\Ui\Component\Listing\AttributeRepository;
    // use Magento\Framework\Api\FilterBuilder;
    // use Magento\Framework\Api\Search\SearchCriteriaBuilder;
    // use Magento\Framework\App\RequestInterface;
    // use Magento\Framework\View\Element\UiComponent\DataProvider\Reporting;

    // class DataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
    // {
    //     /**
    //      * @var AttributeRepository
    //      */
    //     private $attributeRepository;

    //     /**
    //      * @param string $name
    //      * @param string $primaryFieldName
    //      * @param string $requestFieldName
    //      * @param Reporting $reporting
    //      * @param SearchCriteriaBuilder $searchCriteriaBuilder
    //      * @param RequestInterface $request
    //      * @param FilterBuilder $filterBuilder
    //      * @param AttributeRepository $attributeRepository
    //      * @param array $meta
    //      * @param array $data
    //      * @SuppressWarnings(PHPMD.ExcessiveParameterList)
    //      */
    //     public function __construct(
    //         $name,
    //         $primaryFieldName,
    //         $requestFieldName,
    //         Reporting $reporting,
    //         SearchCriteriaBuilder $searchCriteriaBuilder,
    //         RequestInterface $request,
    //         FilterBuilder $filterBuilder,
    //         AttributeRepository $attributeRepository,
    //         array $meta = [],
    //         array $data = []
    //     ) {
    //         $this->attributeRepository = $attributeRepository;
    //         parent::__construct(
    //             $name,
    //             $primaryFieldName,
    //             $requestFieldName,
    //             $reporting,
    //             $searchCriteriaBuilder,
    //             $request,
    //             $filterBuilder,
    //             $meta,
    //             $data
    //         );
    //     }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $data = parent::getData();
        // dd($data);
        // foreach ($this->attributeRepository->getList() as $attributeCode => $attributeData) {
        //     foreach ($data['items'] as &$item) {
        //         if (isset($item[$attributeCode]) && !empty($attributeData[AttributeMetadataInterface::OPTIONS])) {
                    // $item[$attributeCode] = explode(',', $item[$attributeCode]);
        //         }
        //     }
        // }
        // $result = $this->getCollection()->toArray();
        return $data;
    }
    
    // public function getData()
    // {
    //     if (!$this->getCollection()->isLoaded()) {
    //         $this->getCollection()->load();
    //     }

    //     $result = $this->getCollection()->toArray();

    //     $items = $result['items'] ?? [];

    //     // dd($items);

    //     // usort($items, fn($a, $b) => $a['attribute_code'] <=> $b['attribute_code']);

    //     return [
    //         'totalRecords' => $result['totalRecords'],
    //         'items' => array_values($items),
    //     ];
    // }

    // /**
    //  * Add field to select
    //  *
    //  * @param string|array $field
    //  * @param string|null $alias
    //  * @return void
    //  */
    // public function addField($field, $alias = null)
    // {
    //     if (isset($this->addFieldStrategies[$field])) {
    //         $this->addFieldStrategies[$field]->addField($this->getCollection(), $field, $alias);
    //     } else {
    //         parent::addField($field, $alias);
    //     }
    // }

    // /**
    //  * @param \Magento\Framework\Api\Filter $filter
    //  */
    // public function addFilter(\Magento\Framework\Api\Filter $filter)
    // {
    //     if (isset($this->addFilterStrategies[$filter->getField()])) {
    //         $this->addFilterStrategies[$filter->getField()]
    //             ->addFilter(
    //                 $this->getCollection(),
    //                 $filter->getField(),
    //                 [$filter->getConditionType() => $filter->getValue()]
    //             );
    //     } else {
    //         parent::addFilter($filter);
    //     }
    // }
}
