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

class SportCardsSets implements ResolverInterface
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
     * @var \TCGCollective\ProductAttributesLink\Model\SportCardsSetsFactory
     */
    protected $sportCardsSetsFactory;

    /**
     * @var \TCGCollective\ProductAttributesLink\Model\SportCardsMappingFactory
     */
    protected $sportCardsMappingFactory;

    /**
     * @param DataObjectProcessor $dataProcessor
     * @param CountryInformationAcquirerInterface $countryInformationAcquirer
     * @param MappingHelper $mappingHelper
     * @param \TCGCollective\ProductAttributesLink\Model\SportCardsSetsFactory $sportCardsSetsFactory
     * @param \TCGCollective\ProductAttributesLink\Model\SportCardsMappingFactory $sportCardsMappingFactory
     */
    public function __construct(
        DataObjectProcessor $dataProcessor,
        CountryInformationAcquirerInterface $countryInformationAcquirer,
        MappingHelper $mappingHelper,
        \TCGCollective\ProductAttributesLink\Model\SportCardsSetsFactory $sportCardsSetsFactory,
        \TCGCollective\ProductAttributesLink\Model\SportCardsMappingFactory $sportCardsMappingFactory
    ) {
        $this->dataProcessor = $dataProcessor;
        $this->countryInformationAcquirer = $countryInformationAcquirer;
        $this->_mappingHelper = $mappingHelper;
        $this->sportCardsSetsFactory = $sportCardsSetsFactory;
        $this->sportCardsMappingFactory = $sportCardsMappingFactory;
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

        $scBrandsOptionId = $filters && isset($filters['sc_brands']) ? $filters['sc_brands']['eq'] : null;
        $scBaseballReleaseOptionId = $filters && isset($filters['sc_baseball_release']) ? $filters['sc_baseball_release']['eq'] : null;

        $result = [];

        if ($categoryUrlKey) {
            $categoryName = $this->_mappingHelper->getCategoryNameByUrlKey($categoryUrlKey);
            if ($categoryName) {
                $attributeOptionId = $this->_mappingHelper->getAttributeOptionId('sc_sport', $categoryName);
                if ($attributeOptionId) {
                    $setsByAlphabet = [];
                    if ($shopby == "sc_baseball_players") {
                        $collection = $this->sportCardsMappingFactory->create()
                        ->getCollection()
                        ->addFieldToFilter('sc_sport', $attributeOptionId);

                        if ($scBaseballReleaseOptionId) {
                            $collection->addFieldToFilter('sc_baseball_release', $scBaseballReleaseOptionId);
                        }

                        $collection->getSelect()->group('sc_baseball_players');
                        if ($collection->getSize()) {
                            foreach($collection as $model) {
                                $attributeId = $model->getScBaseballPlayers();
                                $attributeName = $this->_mappingHelper->getOptionLabelById('sc_baseball_players', $attributeId);
                                $group = $attributeName ? substr($attributeName, 0, 1) : '';
                                if (!ctype_alpha($group)) {
                                    $group = '#';
                                }
                                $setsByAlphabet[$group][] = [
                                    'set_name' => $attributeName,
                                    'option_id' => $attributeId,
                                    'sc_league' =>  $this->_mappingHelper->getOptionLabelById('sc_league', $model->getScLeague())
                                ];
                            }
                        }
                    } elseif ($shopby == "sc_baseball_teams") {
                        $collection = $this->sportCardsMappingFactory->create()
                        ->getCollection()
                        ->addFieldToFilter('sc_sport', $attributeOptionId);

                        if ($scBaseballReleaseOptionId) {
                            $collection->addFieldToFilter('sc_baseball_release', $scBaseballReleaseOptionId);
                        }

                        $collection->getSelect()->group('sc_baseball_teams');
                        if ($collection->getSize()) {
                            foreach($collection as $model) {
                                $attributeId = $model->getScBaseballTeams();
                                $attributeName = $this->_mappingHelper->getOptionLabelById('sc_baseball_teams', $attributeId);
                                $group = $attributeName ? substr($attributeName, 0, 1) : '';
                                if (!ctype_alpha($group)) {
                                    $group = '#';
                                }
                                $setsByAlphabet[$group][] = [
                                    'set_name' => $attributeName,
                                    'option_id' => $attributeId,
                                    'sc_league' =>  $this->_mappingHelper->getOptionLabelById('sc_league', $model->getScLeague())
                                ];
                            }
                        }
                    } else {
                        $collection = $this->sportCardsSetsFactory->create()
                        ->getCollection()
                        ->addFieldToFilter('sc_sport', $attributeOptionId);
                        if ($setType) {
                            $collection->addFieldToFilter('set_type', $setType);
                        } else {
                            $collection->addFieldToFilter('set_type', ['null' => true]);
                        }
                        if ($scBrandsOptionId) {
                            $collection->addFieldToFilter('sc_brands', $scBrandsOptionId);
                        }
                        if ($scBaseballReleaseOptionId) {
                            $collection->addFieldToFilter('sc_baseball_release', $scBaseballReleaseOptionId);
                        }
                        $collection->getSelect()->order('release_year DESC');
                        if ($collection->getSize()) {
                            foreach($collection as $model) {
                                $group = $this->_mappingHelper->getOptionLabelById('release_year', $model->getReleaseYear());
                                $attributeId = $model->getScBaseballRelease();
                                $attributeName = $this->_mappingHelper->getOptionLabelById('sc_baseball_release', $attributeId);
                                // $firstChar = substr($setName, 0, 1);
                                // if (ctype_digit($firstChar)) {
                                //     $firstChar = '#';
                                // }
                                $setsByAlphabet[$group][] = [
                                    'set_name' => $attributeName,
                                    'option_id' => $attributeId,
                                    'sc_league' =>  $this->_mappingHelper->getOptionLabelById('sc_league', $model->getScLeague()),
                                    'sc_brands' =>  $this->_mappingHelper->getOptionLabelById('sc_brands', $model->getScBrands()),
                                    'release_year' => $this->_mappingHelper->getOptionLabelById('release_year', $model->getData('release_year'))
                                ];
                            }
                        }
                    }
                    // $collection->getSelect()->group('card_set');
                    

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
