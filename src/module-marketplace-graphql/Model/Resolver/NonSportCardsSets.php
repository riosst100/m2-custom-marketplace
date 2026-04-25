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

class NonSportCardsSets implements ResolverInterface
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
     * @var \TCGCollective\ProductAttributesLink\Model\NonSportCardsSetsFactory
     */
    protected $nonSportCardsSetsFactory;

    /**
     * @param DataObjectProcessor $dataProcessor
     * @param CountryInformationAcquirerInterface $countryInformationAcquirer
     * @param MappingHelper $mappingHelper
     * @param \TCGCollective\ProductAttributesLink\Model\NonSportCardsSetsFactory $nonSportCardsSetsFactory
     */
    public function __construct(
        DataObjectProcessor $dataProcessor,
        CountryInformationAcquirerInterface $countryInformationAcquirer,
        MappingHelper $mappingHelper,
        \TCGCollective\ProductAttributesLink\Model\NonSportCardsSetsFactory $nonSportCardsSetsFactory,
        \TCGCollective\ProductAttributesLink\Model\SportCardsMappingFactory $sportCardsMappingFactory
    ) {
        $this->dataProcessor = $dataProcessor;
        $this->countryInformationAcquirer = $countryInformationAcquirer;
        $this->_mappingHelper = $mappingHelper;
        $this->nonSportCardsSetsFactory = $nonSportCardsSetsFactory;
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
        $setType = isset($args['set_type']) ? $args['set_type'] : null;
        $shopby = isset($args['shopby']) ? $args['shopby'] : null;
        $categoryUrlKey = isset($args['categoryUrlKey']) ? $args['categoryUrlKey'] : null;

        $filters = $args['filters'];

        $result = [];

        if ($categoryUrlKey) {
            $categoryName = $this->_mappingHelper->getCategoryNameByUrlKey($categoryUrlKey);
            if ($categoryName) {
                $code = '';
                if ($categoryUrlKey == "comics") {
                    $code = 'comics';
                }

                if ($categoryUrlKey == "movies") {
                    $code = 'movies';
                }

                if ($categoryUrlKey == "music") {
                    $code = 'music';
                }

                if ($categoryUrlKey == "tv") {
                    $code = 'tv';
                }

                if ($categoryUrlKey == "games") {
                    $code = 'games';
                }

                $releaseAttrCode = 'nsc_'.$code.'_release';

                // die($categoryName)
                $attributeOptionId = $this->_mappingHelper->getAttributeOptionId('nsc_categories', $categoryName);
                if ($attributeOptionId) {
                    $setsByAlphabet = [];
                    
                    $collection = $this->nonSportCardsSetsFactory->create()
                    ->getCollection()
                    ->addFieldToFilter('nsc_categories', $attributeOptionId);

                    if (isset($filters[$releaseAttrCode])) {
                        $release = $filters[$releaseAttrCode]['eq'];
                        if ($release) {
                            $collection->addFieldToFilter($releaseAttrCode, $release);
                        }
                    }

                    if ($setType && str_contains($setType, 'Inserts')) { 
                        $collection->addFieldToFilter('set_type', 'Inserts');
                    }

                    // die($collection->getSelect()->__toString());

                    // nsc_movies_inserts

                    // $releaseAttrCode = 'sc_'.strtolower($categoryName).'_release';
                    
                    $collection->getSelect()->order('release_year DESC');
                    // die($collection->getSelect()->__toString());
                    if ($collection->getSize()) {
                        foreach($collection as $model) {
                            $attributeId = $model->getData($shopby);
                            $attributeName = $this->_mappingHelper->getOptionLabelById($shopby, $attributeId);
                            if ($attributeName) {
                                $group = $this->_mappingHelper->getOptionLabelById('release_year', $model->getReleaseYear());
                                $setsByAlphabet[$group][] = [
                                    'set_name' => $attributeName,
                                    'option_id' => $attributeId,
                                    'release_year' => $group
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

                        usort($result, fn($a, $b) => $b['group'] <=> $a['group']);
                    }
                }
            }
        }

        return $result;
    }
}
