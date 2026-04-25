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

class ShopByCard implements ResolverInterface
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
     * @var \TCGCollective\ProductAttributesLink\Model\CardGamesSetsCardFactory
     */
    protected $cardGamesSetsCardFactory;

    /**
     * @var \TCGCollective\ProductAttributesLink\Model\CardGamesSetsFactory
     */
    protected $cardGamesSetsFactory;

    /**
     * @param DataObjectProcessor $dataProcessor
     * @param CountryInformationAcquirerInterface $countryInformationAcquirer
     * @param MappingHelper $mappingHelper
     * @param \TCGCollective\ProductAttributesLink\Model\CardGamesSetsCardFactory $cardGamesSetsCardFactory
     * @param \TCGCollective\ProductAttributesLink\Model\CardGamesSetsFactory $cardGamesSetsFactory
     */
    public function __construct(
        DataObjectProcessor $dataProcessor,
        CountryInformationAcquirerInterface $countryInformationAcquirer,
        MappingHelper $mappingHelper,
        \TCGCollective\ProductAttributesLink\Model\CardGamesSetsCardFactory $cardGamesSetsCardFactory,
        \TCGCollective\ProductAttributesLink\Model\CardGamesSetsFactory $cardGamesSetsFactory
    ) {
        $this->dataProcessor = $dataProcessor;
        $this->countryInformationAcquirer = $countryInformationAcquirer;
        $this->_mappingHelper = $mappingHelper;
        $this->cardGamesSetsCardFactory = $cardGamesSetsCardFactory;
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

        $filterUrl = isset($args['filters']) ? $args['filters'] : [];

        $attributeOptionId = null;
        if ($filterUrl && isset($filterUrl['card_game'])) {
            $attributeOptionId = $filterUrl['card_game']['eq'];
        }

        if ($categoryUrlKey) {
            // $categoryName = $this->_mappingHelper->getCategoryNameByUrlKey($categoryUrlKey);
            // if ($categoryName) {
                // $attributeOptionId = $this->_mappingHelper->getAttributeOptionId('card_game', $categoryName);
                if ($attributeOptionId) {
                    $setsByAlphabet = [];
                    $collection = $this->cardGamesSetsCardFactory->create()
                    ->getCollection()
                    ->setOrder('release_date', 'DESC');
                    $collection->getSelect()->joinLeft(
                        ['sets' => 'marketplace_card_games_sets'],
                        'main_table.set_id=sets.entity_id',
                        []
                    )->where("sets.card_game = " . $attributeOptionId);
                    $collection->getSelect()->group('card_name');
                    if ($collection->getSize()) {
                        foreach($collection as $model) {
                            $cardName = $model->getCardName();
                            $cardName = mb_convert_encoding($cardName, "UTF-8");

                            // $setName = $this->_mappingHelper->getOptionLabelById('card_set', $cardSetId);
                            $firstChar = substr($cardName, 0, 1);
                            if (!ctype_alpha($firstChar)) {
                                $firstChar = '#';
                            }
                            $setsByAlphabet[$firstChar][] = [
                                'set_name' => $cardName,
                                'option_id' => 0,
                                'set_abbreviation' => $model->getCardSetAbbreviation(),
                                'release_number' => $model->getReleaseNumber(),
                                'release_year' => $model->getData('release_year'),
                                'release_date' => $model->getReleaseDate()
                            ];
                        }
                    }

                    if ($setsByAlphabet) {
                        foreach($setsByAlphabet as $firstChar => $sets) {
                            usort($sets, fn($a, $b) => $a['set_name'] <=> $b['set_name']);
                            $result[] = [
                                'release_type' => $firstChar,
                                'sets' => $sets
                            ];
                        }

                        usort($result, fn($a, $b) => $a['release_type'] <=> $b['release_type']);
                    }
                }
            // }
        }

        return $result;
    }
}
