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

class ShopByVehicles implements ResolverInterface
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
     * @var \TCGCollective\ProductAttributesLink\Model\VehiclesMakeFactory
     */
    protected $vehiclesMakeFactory;

    /**
     * @var \TCGCollective\ProductAttributesLink\Model\VehiclesBrandsFactory
     */
    protected $vehiclesBrandsFactory;

    /**
     * @param DataObjectProcessor $dataProcessor
     * @param CountryInformationAcquirerInterface $countryInformationAcquirer
     * @param MappingHelper $mappingHelper
     * @param \TCGCollective\ProductAttributesLink\Model\VehiclesMakeFactory $vehiclesMakeFactory
     * @param \TCGCollective\ProductAttributesLink\Model\VehiclesBrandsFactory $vehiclesBrandsFactory
     */
    public function __construct(
        DataObjectProcessor $dataProcessor,
        CountryInformationAcquirerInterface $countryInformationAcquirer,
        MappingHelper $mappingHelper,
        \TCGCollective\ProductAttributesLink\Model\VehiclesMakeFactory $vehiclesMakeFactory,
        \TCGCollective\ProductAttributesLink\Model\VehiclesBrandsFactory $vehiclesBrandsFactory
    ) {
        $this->dataProcessor = $dataProcessor;
        $this->countryInformationAcquirer = $countryInformationAcquirer;
        $this->_mappingHelper = $mappingHelper;
        $this->vehiclesMakeFactory = $vehiclesMakeFactory;
        $this->vehiclesBrandsFactory = $vehiclesBrandsFactory;
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

        if ($shopby == "vehicles_make") {
            $collection = $this->vehiclesMakeFactory->create()
            ->getCollection();

            $collection->getSelect()->order('vehicles_make ASC');
            if ($collection->getSize()) {
                foreach($collection as $model) {
                    $attributeId = $model->getVehiclesMake();
                    $attributeName = $this->_mappingHelper->getOptionLabelById('vehicles_make', $attributeId);
                    if ($attributeName) {
                        $group = $attributeName ? substr($attributeName, 0, 1) : '';
                        if (!ctype_alpha($group)) {
                            $group = '#';
                        }

                        $multipleOptions = [];
                        
                        $options = $model->getData('vehicles_diecast') ? explode(",", $model->getData('vehicles_diecast')) : null;
                        if ($options) {
                            foreach($options as $option) {
                                $multipleOptions[] = '<'.$this->_mappingHelper->getOptionLabelById('vehicles_diecast', $option).'>';
                            }
                        }

                        if ($group) {
                            $setsByAlphabet[$group][] = [
                                'set_name' => $attributeName,
                                'vehicles_diecast' => $multipleOptions ? implode(",", $multipleOptions) : '',
                                'option_id' => $attributeId
                            ];
                        }
                    }
                }
            }
        }

        if ($shopby == "vehicles_brands") {
            $collection = $this->vehiclesBrandsFactory->create()
            ->getCollection();

            $collection->getSelect()->order('vehicles_brands ASC');
            if ($collection->getSize()) {
                foreach($collection as $model) {
                    $attributeId = $model->getVehiclesBrands();
                    $attributeName = $this->_mappingHelper->getOptionLabelById('vehicles_brands', $attributeId);
                    if ($attributeName) {
                        $group = $attributeName ? substr($attributeName, 0, 1) : '';
                        if (!ctype_alpha($group)) {
                            $group = '#';
                        }

                        $multipleOptions = [];
                        
                        $options = $model->getData('vehicles_diecast') ? explode(",", $model->getData('vehicles_diecast')) : null;
                        if ($options) {
                            foreach($options as $option) {
                                $multipleOptions[] = '<'.$this->_mappingHelper->getOptionLabelById('vehicles_diecast', $option).'>';
                            }
                        }


                        if ($group) {
                            $setsByAlphabet[$group][] = [
                                'set_name' => $attributeName,
                                'vehicles_diecast' => $multipleOptions ? implode(",", $multipleOptions) : '',
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
