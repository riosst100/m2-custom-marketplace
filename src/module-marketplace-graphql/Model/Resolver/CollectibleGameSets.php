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

class CollectibleGameSets implements ResolverInterface
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
     * @var \TCGCollective\ProductAttributesLink\Model\CardGamesSetsFactory
     */
    protected $cardGamesSetsFactory;

    /**
     * @param DataObjectProcessor $dataProcessor
     * @param CountryInformationAcquirerInterface $countryInformationAcquirer
     * @param MappingHelper $mappingHelper
     * @param \TCGCollective\ProductAttributesLink\Model\CardGamesSetsFactory $cardGamesSetsFactory
     */
    public function __construct(
        DataObjectProcessor $dataProcessor,
        CountryInformationAcquirerInterface $countryInformationAcquirer,
        MappingHelper $mappingHelper,
        \TCGCollective\ProductAttributesLink\Model\CardGamesSetsFactory $cardGamesSetsFactory
    ) {
        $this->dataProcessor = $dataProcessor;
        $this->countryInformationAcquirer = $countryInformationAcquirer;
        $this->_mappingHelper = $mappingHelper;
        $this->cardGamesSetsFactory = $cardGamesSetsFactory;
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
        $productType = isset($args['productType']) ? $args['productType'] : null;
        $categoryUrlKey = isset($args['categoryUrlKey']) ? $args['categoryUrlKey'] : null;

        $result = [];

        if ($categoryUrlKey) {
            $categoryName = $this->_mappingHelper->getCategoryNameByUrlKey($categoryUrlKey);
            if ($categoryName) {
                $cardGameId = $this->_mappingHelper->getAttributeOptionId('card_game', $categoryName);
                if ($cardGameId) {

                    $cardSetAttributesOptions = [];
                    
                    $attributePrefix = null;
                    if ($attributePrefix) {
                        $cardSetAttributesOptionsRaw = $this->_mappingHelper->getAttributeOptions($attributePrefix.'card_set');
                        if ($cardSetAttributesOptionsRaw) {
                            foreach($cardSetAttributesOptionsRaw as $optionRaw) {
                                $cardSetAttributesOptions[$optionRaw->getLabel()] = $optionRaw->getValue();
                            }
                        }
                    }

                    // die('<pre>'.print_r(get_class_methods($cardSetAttributesOptions),true));

                    if ($productType == "expansion_sets") {
                        $setsByReleases = [];
                        $collection = $this->cardGamesSetsFactory->create()
                        ->getCollection()
                        ->setOrder('release_date', 'DESC');
                        if ($collection->getSize()) {
                            foreach($collection as $model) {
                                $setsByReleases[$model->getReleaseType()][] = [
                                    'set_name' => $model->getCardSet(),
                                    'option_id' => isset($cardSetAttributesOptions[$model->getCardSet()]) ? $cardSetAttributesOptions[$model->getCardSet()] : 0,
                                    'set_abbreviation' => $model->getCardSetAbbreviation(),
                                    'release_number' => $model->getReleaseNumber(),
                                    'release_date' => $model->getReleaseDate(),
                                    'release_year' => explode("/", $model->getReleaseDate())[0]
                                ];
                            }
                        }

                        if ($setsByReleases) {
                            foreach($setsByReleases as $releaseType => $sets) {
                                $result[] = [
                                    'release_type' => $releaseType,
                                    'sets' => $sets
                                ];
                            }
                        }
                    } else {
                        $setsByAlphabet = [];
                        $collection = $this->cardGamesSetsFactory->create()
                        ->getCollection()
                        ->addFieldToFilter('card_game', $cardGameId)
                        ->setOrder('release_date', 'DESC');
                        $collection->getSelect()->group('card_set');
                        if ($collection->getSize()) {
                            foreach($collection as $model) {
                                // $cardSetId = $model->getCardSet();
                                // $setName = $this->_mappingHelper->getOptionLabelById('card_set', $cardSetId);

                                $setName = $model->getCardSet();
                                $firstChar = substr($setName, 0, 1);
                                if (!ctype_alpha($firstChar)) {
                                    $firstChar = '#';
                                }
                                $setsByAlphabet[$firstChar][] = [
                                    'set_name' => $setName,
                                    'option_id' => isset($cardSetAttributesOptions[$setName]) ? $cardSetAttributesOptions[$setName] : 0,
                                    'set_abbreviation' => $model->getCardSetAbbreviation(),
                                    'release_number' => $model->getReleaseNumber(),
                                    'release_year' => $model->getData('release_year'),
                                    'release_date' => $model->getReleaseDate()
                                ];
                            }
                        }

                        if ($setsByAlphabet) {
                            foreach($setsByAlphabet as $releaseType => $sets) {
                                usort($sets, fn($a, $b) => $a['set_name'] <=> $b['set_name']);
                                $result[] = [
                                    'release_type' => $releaseType,
                                    'sets' => $sets
                                ];
                            }

                            usort($result, fn($a, $b) => $a['release_type'] <=> $b['release_type']);
                        }
                    }
                }
            }
        }

        return $result;
    }
}
