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

class LegoSets implements ResolverInterface
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
     * @var \TCGCollective\ProductAttributesLink\Model\LegoSetsFactory
     */
    protected $legoSetsFactory;

    /**
     * @var \TCGCollective\ProductAttributesLink\Model\LegoSinglesFactory
     */
    protected $legoSinglesFactory;

    /**
     * @param DataObjectProcessor $dataProcessor
     * @param CountryInformationAcquirerInterface $countryInformationAcquirer
     * @param MappingHelper $mappingHelper
     * @param \TCGCollective\ProductAttributesLink\Model\LegoSetsFactory $legoSetsFactory
     * @param \TCGCollective\ProductAttributesLink\Model\LegoSinglesFactory $legoSinglesFactory
     */
    public function __construct(
        DataObjectProcessor $dataProcessor,
        CountryInformationAcquirerInterface $countryInformationAcquirer,
        MappingHelper $mappingHelper,
        \TCGCollective\ProductAttributesLink\Model\LegoSetsFactory $legoSetsFactory,
        \TCGCollective\ProductAttributesLink\Model\LegoSinglesFactory $legoSinglesFactory
    ) {
        $this->dataProcessor = $dataProcessor;
        $this->countryInformationAcquirer = $countryInformationAcquirer;
        $this->_mappingHelper = $mappingHelper;
        $this->legoSetsFactory = $legoSetsFactory;
        $this->legoSinglesFactory = $legoSinglesFactory;
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

        
        if ($shopby == "singles") {
            // if ($scBrandsOptionId) {
                // $collection->addFieldToFilter('bricks_minifigures_name', ['neq' => null]);
                $collection = $this->legoSinglesFactory->create()
                ->getCollection();
                $collection->getSelect()->group('bricks_set_name');
            // }
        } else {
            $collection = $this->legoSetsFactory->create()
        ->getCollection();
            // $collection->addFieldToFilter('bricks_minifigures_name', ['eq' => null]);
        }

        $collection->getSelect()->order('release_year DESC');
        
        // $collection->getSelect()->group('card_set');
        if ($collection->getSize()) {
            foreach($collection as $model) {
                // $group = $this->_mappingHelper->getOptionLabelById('release_year', $model->getLegoReleaseYear());
                // if ($legoTheme && $legoTheme == "Collectable Minifigures") {
                //     $attributeId = $model->getLegoSingles();
                //     $attributeName = $this->_mappingHelper->getOptionLabelById('bricks_minifigures_name', $attributeId);
                // } else {
                    
                // }
                // $attributeId = $model->getData('bricks_set_name');
                // $attributeName = $this->_mappingHelper->getOptionLabelById('bricks_set_name', $attributeId);
                $attributeName = $model->getData('bricks_set_name');
                if ($attributeName) {
                    $group = $attributeName ? substr($attributeName, 0, 1) : '';
                    if (!ctype_alpha($group)) {
                        $group = '#';
                    }

                    // die($model->getData('release_year'));

                    $releaseYear = $model->getData('release_year');

                    // die($releaseYear);

                    

                    if ($activeFilter) {
                        if ($activeFilter == "Theme") {
                            $theme = $model->getData('bricks_theme');
                            $group = $theme;
                        }

                        if ($activeFilter == "Sub-Theme") {
                            $bricks_subtheme = $model->getData('bricks_subtheme');
                            $group = $bricks_subtheme;
                        }

                        if ($activeFilter == "Interest") {
                            $bricks_interest = $model->getData('bricks_interest');
                            $group = $bricks_interest;
                        }

                        if ($activeFilter == "Age Level") {
                            $bricks_age_level = $model->getData('bricks_age_level');
                            $group = $bricks_age_level;
                        }
                    }

                    if ($activeTab && $activeTab == "year") {
                        $group = $releaseYear;
                    }

                    if ($group) {
                        $setsByAlphabet[$group][] = [
                            'set_name' => $attributeName,
                            'option_id' => 0,
                            'release_year' => $releaseYear
                        ];
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
