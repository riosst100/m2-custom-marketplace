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

class MiniatureGameSets implements ResolverInterface
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
     * @var \TCGCollective\ProductAttributesLink\Model\MiniatureGameSetsFactory
     */
    protected $miniatureGameSetsFactory;

    /**
     * @param DataObjectProcessor $dataProcessor
     * @param CountryInformationAcquirerInterface $countryInformationAcquirer
     * @param MappingHelper $mappingHelper
     * @param \TCGCollective\ProductAttributesLink\Model\MiniatureGameSetsFactory $miniatureGameSetsFactory
     */
    public function __construct(
        DataObjectProcessor $dataProcessor,
        CountryInformationAcquirerInterface $countryInformationAcquirer,
        MappingHelper $mappingHelper,
        \TCGCollective\ProductAttributesLink\Model\MiniatureGameSetsFactory $miniatureGameSetsFactory
    ) {
        $this->dataProcessor = $dataProcessor;
        $this->countryInformationAcquirer = $countryInformationAcquirer;
        $this->_mappingHelper = $mappingHelper;
        $this->miniatureGameSetsFactory = $miniatureGameSetsFactory;
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

        $filters = $args['filters'];

        $shopby = isset($args['shopby']) ? $args['shopby'] : null;

        $result = [];

        $setsByAlphabet = [];
        $collection = $this->miniatureGameSetsFactory->create()
        ->getCollection();

        $collection->getSelect()->order('release_year DESC');
        if ($shopby == "singles") {
            // if ($scBrandsOptionId) {
                $collection->addFieldToFilter('bricks_minifigures_name', ['neq' => null]);
                $collection->getSelect()->group('bricks_set_name');
            // }
        }
        
        // $collection->getSelect()->group('card_set');
        if ($collection->getSize()) {
            foreach($collection as $model) {
                // $group = $this->_mappingHelper->getOptionLabelById('release_year', $model->getLegoReleaseYear());
                // if ($legoTheme && $legoTheme == "Collectable Minifigures") {
                //     $attributeId = $model->getLegoSingles();
                //     $attributeName = $this->_mappingHelper->getOptionLabelById('bricks_minifigures_name', $attributeId);
                // } else {
                    
                // }
                $attributeId = $model->getData('bricks_set_name');
                $attributeName = $this->_mappingHelper->getOptionLabelById('bricks_set_name', $attributeId);
                if ($attributeName) {
                    $group = $attributeName ? substr($attributeName, 0, 1) : '';
                    if (!ctype_alpha($group)) {
                        $group = '#';
                    }

                    // die($model->getData('release_year'));

                    $releaseYear = $this->_mappingHelper->getOptionLabelById('release_year', $model->getData('release_year'));

                    // die($releaseYear);

                    if ($activeTab && $activeTab == "year") {
                        $group = $releaseYear;
                    }
                    
                    $setsByAlphabet[$group][] = [
                        'set_name' => $attributeName,
                        'option_id' => $attributeId,
                        'release_year' => $releaseYear
                    ];
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
