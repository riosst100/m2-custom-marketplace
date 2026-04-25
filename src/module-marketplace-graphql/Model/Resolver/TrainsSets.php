<?php
declare(strict_types=1);

namespace TCGCollective\MarketplaceGraphQl\Model\Resolver;

use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Directory\Api\CountryInformationAcquirerInterface;
use Magento\Directory\Api\Data\CountryInformationInterface;
use TCGCollective\ProductAttributesLink\Helper\Data as MappingHelper;

class TrainsSets implements ResolverInterface
{
    /**
     * @var DataObjectProcessor
     */
    private $dataProcessor;

    /**
     * @var CountryInformationAcquirerInterface
     */
    private $countryInformationAcquirer;

    /**
     * @var MappingHelper
     */
    protected $_mappingHelper;

    /**
     * @var \TCGCollective\ProductAttributesLink\Model\TrainsRoadnameFactory
     */
    protected $trainsRoadnameFactory;

    /**
     * @var \TCGCollective\ProductAttributesLink\Model\TrainsBrandsFactory
     */
    protected $trainsBrandsFactory;

    /**
     * @param DataObjectProcessor $dataProcessor
     * @param CountryInformationAcquirerInterface $countryInformationAcquirer
     * @param MappingHelper $mappingHelper
     * @param \TCGCollective\ProductAttributesLink\Model\TrainsRoadnameFactory $trainsRoadnameFactory
     * @param \TCGCollective\ProductAttributesLink\Model\TrainsBrandsFactory $trainsBrandsFactory
     */
    public function __construct(
        DataObjectProcessor $dataProcessor,
        CountryInformationAcquirerInterface $countryInformationAcquirer,
        MappingHelper $mappingHelper,
        \TCGCollective\ProductAttributesLink\Model\TrainsRoadnameFactory $trainsRoadnameFactory,
        \TCGCollective\ProductAttributesLink\Model\TrainsBrandsFactory $trainsBrandsFactory
    ) {
        $this->dataProcessor = $dataProcessor;
        $this->countryInformationAcquirer = $countryInformationAcquirer;
        $this->_mappingHelper = $mappingHelper;
        $this->trainsRoadnameFactory = $trainsRoadnameFactory;
        $this->trainsBrandsFactory = $trainsBrandsFactory;
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
        $categoryUrlKey = isset($args['categoryUrlKey']) ? $args['categoryUrlKey'] : null;
        $activeTab = isset($args['activeTab']) ? $args['activeTab'] : null;
        $activeFilter = isset($args['activeFilter']) ? $args['activeFilter'] : null;

        $filters = $args['filters'];

        $shopby = isset($args['shopby']) ? $args['shopby'] : null;

        $result = [];

        $setsByAlphabet = [];

        if ($shopby == "trains_roadname") {
            $collection = $this->trainsRoadnameFactory->create()
            ->getCollection();

            $collection->getSelect()->order('trains_roadname ASC');
            if ($collection->getSize()) {
                foreach($collection as $model) {
                    $attributeId = $model->getTrainsRoadname();
                    $attributeName = $this->_mappingHelper->getOptionLabelById('trains_roadname', $attributeId);
                    if ($attributeName) {
                        $group = $attributeName ? substr($attributeName, 0, 1) : '';
                        if (!ctype_alpha($group)) {
                            $group = '#';
                        }

                        if ($group) {
                            $setsByAlphabet[$group][] = [
                                'set_name' => $attributeName,
                                'country' => $model->getCountry(),
                                'option_id' => $attributeId
                            ];
                        }
                    }
                }
            }
        }

        if ($shopby == "trains_brands") {
            $collection = $this->trainsBrandsFactory->create()
            ->getCollection();

            $collection->getSelect()->order('trains_brands ASC');
            if ($collection->getSize()) {
                foreach($collection as $model) {
                    $attributeId = $model->getTrainsBrands();
                    $attributeName = $this->_mappingHelper->getOptionLabelById('trains_brands', $attributeId);
                    if ($attributeName) {
                        $group = $attributeName ? substr($attributeName, 0, 1) : '';
                        if (!ctype_alpha($group)) {
                            $group = '#';
                        }

                        $gaugeOptions = [];
                        
                        $gauges = $model->getTrainsGauge() ? explode(",", $model->getTrainsGauge()) : null;
                        if ($gauges) {
                            foreach($gauges as $gauge) {
                                $gaugeOptions[] = '<'.$this->_mappingHelper->getOptionLabelById('railway_gauge', $gauge).'>';
                            }
                        }


                        if ($group) {
                            $setsByAlphabet[$group][] = [
                                'set_name' => $attributeName,
                                'railway_gauge' => $gaugeOptions ? implode(",", $gaugeOptions) : '',
                                'option_id' => $attributeId
                            ];
                        }
                    }
                }
            }
        }

        if ($setsByAlphabet) {
            foreach($setsByAlphabet as $group => $sets) {
                usort($sets, fn($a, $b) => $a['set_name'] <=> $b['set_name']);
                $result[] = [
                    'group' => $group,
                    'sets' => $sets
                ];
            }

            usort($result, fn($a, $b) => $a['group'] <=> $b['group']);
        }

        return $result;
    }
}
