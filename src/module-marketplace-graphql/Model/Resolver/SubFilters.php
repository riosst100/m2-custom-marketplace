<?php
declare(strict_types=1);

namespace TCGCollective\MarketplaceGraphQl\Model\Resolver;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Psr\Log\LoggerInterface;
use TCGCollective\ProductAttributesLink\Model\ResourceModel\CardGamesSets\CollectionFactory;

class SubFilters implements ResolverInterface
{
    /**
     * @var CollectionFactory
     */
    private CollectionFactory $collectionFactory;

    private $sportCardsSetsCollectionFactory;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;
    
    public function __construct(
        CollectionFactory $collectionFactory,
        \TCGCollective\ProductAttributesLink\Model\ResourceModel\SportCardsSets\CollectionFactory $sportCardsSetsCollectionFactory,
        LoggerInterface $logger = null
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->sportCardsSetsCollectionFactory = $sportCardsSetsCollectionFactory;
        $this->logger = $logger ?? ObjectManager::getInstance()->get(LoggerInterface::class);
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
        $data = [];

        // CARD GAMES
        $collection = $this->collectionFactory->create();
        $collection->getSelect()->group('card_release');
        if ($collection->getSize()) {
            $options = [];
            foreach($collection as $releaseModel) {
                $release = $releaseModel->getData('card_release');
                $options[] = $this->getChildSets($release);
            }

            $data[] = [
                'attribute_code' => 'card_release',
                'options' => $options
            ];
        }

        // SPORT CARDS
        // $releaseAttrCode = 'sc_release';
        // $collection = $this->sportCardsSetsCollectionFactory->create();
        // $collection->getSelect()->group($releaseAttrCode);
        // if ($collection->getSize()) {
        //     $options = [];
        //     foreach($collection as $releaseModel) {
        //         $release = $releaseModel->getData($releaseAttrCode);
        //         $options[] = $this->getSportCardsChildSets($release, $releaseAttrCode);
        //     }

        //     $data[] = [
        //         'attribute_code' => $releaseAttrCode,
        //         'options' => $options
        //     ];
        // }

        return $data;
    }

    public function getSportCardsChildSets($release, $releaseAttrCode) 
    {
        $sub_option_ids = [];

        if ($release) {
            $collection = $this->sportCardsSetsCollectionFactory->create();
            $collection->addFieldToFilter($releaseAttrCode, $release);
            if ($collection->getSize()) {
                foreach($collection as $releaseModel) {
                    $sub_option_ids[] = $releaseModel->getData('sc_set_name');
                }
            }
        }

        return [
            'option_label' => $release,
            'sub_option_labels' => $sub_option_ids
        ];
    }

    public function getChildSets($release) 
    {
        $sub_option_ids = [];

        if ($release) {
            $collection = $this->collectionFactory->create();
            $collection->addFieldToFilter('card_release', $release);
            if ($collection->getSize()) {
                foreach($collection as $releaseModel) {
                    $sub_option_ids[] = $releaseModel->getData('card_set');
                }
            }
        }

        return [
            'option_label' => $release,
            'sub_option_labels' => $sub_option_ids
        ];
    }
}
