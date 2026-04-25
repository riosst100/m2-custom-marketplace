<?php
namespace TCGCollective\ProductAttributesLink\Controller\Marketplace\MappingData;

use Magento\Framework\App\Action\Context;

class SearchOptions extends \Magento\Framework\App\Action\Action
{
    protected $jsonResultFactory;


    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $jsonFactory;

    /**
     * @var \TCGCollective\ProductAttributesLink\Helper\Data
     */
    protected $mappingHelper;

    protected $cardGameSets;
    protected $legoSets;
    protected $legoSingles;
    protected $sportCardsSets;
    protected $sportCardsSetsCard;
    protected $nonSportCardsSets;
    protected $nonSportCardsSetsCard;
    protected $miniatureGameSets;
    protected $miniatureGameSingles;
    protected $livingCardGameSets;
    protected $cardGameSetsCard;

    /**
     * Save constructor.
     * @param Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \TCGCollective\ProductAttributesLink\Helper\Data $mappingHelper
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \TCGCollective\ProductAttributesLink\Helper\Data $mappingHelper,
        \TCGCollective\ProductAttributesLink\Model\CardGamesSetsFactory $cardGameSets,
        \TCGCollective\ProductAttributesLink\Model\CardGamesSetsCardFactory $cardGameSetsCard,
        \TCGCollective\ProductAttributesLink\Model\LegoSetsFactory $legoSets,
        \TCGCollective\ProductAttributesLink\Model\LivingCardGameSetsFactory $livingCardGameSets,
        \TCGCollective\ProductAttributesLink\Model\LegoSinglesFactory $legoSingles,
        \TCGCollective\ProductAttributesLink\Model\SportCardsSetsFactory $sportCardsSets,
        \TCGCollective\ProductAttributesLink\Model\SportCardsSetsCardFactory $sportCardsSetsCard,
        \TCGCollective\ProductAttributesLink\Model\NonSportCardsSetsFactory $nonSportCardsSets,
        \TCGCollective\ProductAttributesLink\Model\NonSportCardsSetsCardFactory $nonSportCardsSetsCard,
        \TCGCollective\ProductAttributesLink\Model\MiniatureGameSetsFactory $miniatureGameSets,
        \TCGCollective\ProductAttributesLink\Model\MiniatureGameSinglesFactory $miniatureGameSingles
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->mappingHelper = $mappingHelper;
        $this->cardGameSets = $cardGameSets;
        $this->cardGameSetsCard = $cardGameSetsCard;
        $this->livingCardGameSets = $livingCardGameSets;
        $this->legoSets = $legoSets;
        $this->legoSingles = $legoSingles;
        $this->sportCardsSets = $sportCardsSets;
        $this->sportCardsSetsCard = $sportCardsSetsCard;
        $this->nonSportCardsSets = $nonSportCardsSets;
        $this->nonSportCardsSetsCard = $nonSportCardsSetsCard;
        $this->miniatureGameSets = $miniatureGameSets;
        $this->miniatureGameSingles = $miniatureGameSingles;
        parent::__construct(
            $context
        );
    }

    public function execute()
    {
        $attributeCode = $this->getRequest()->getParam('attribute');
        $searchKey = $this->getRequest()->getParam('searchKey');
        $selectedOption = $this->getRequest()->getParam('selectedOption');
        $pageNum = (int)$this->getRequest()->getParam('page');
        $limit = (int)$this->getRequest()->getParam('limit');

        $data = [
            'total' => 0,
            'options' => []
        ];

        $result = [];

        $options = $this->mappingHelper->getCachedAttributeOptions($attributeCode);

        if ($searchKey) {
            $resultOptions = array_filter($options, function ($o) use ($searchKey) {
                return stripos($o['label'], $searchKey) !== false; // cari substring tanpa case-sensitive
            });
        } else {
            $resultOptions = $options;
        }

        // 1️⃣ Hapus option yang kosong (label kosong atau value kosong)
        $resultOptions = array_filter($resultOptions, function ($opt) {
            return !empty($opt['label']) && !empty($opt['value']);
        });

        // 2️⃣ Reset index biar rapi (karena array_filter mempertahankan key lama)
        $resultOptions = array_values($resultOptions);

        // 3️⃣ Pagination
        $offset = ($pageNum - 1) * $limit;
        $resultOptions = array_slice($resultOptions, $offset, $limit);

        $data['options'] = $resultOptions;
        $data['total'] = count($options);
        
        $result = $this->jsonFactory->create();
        $result->setData($data);

        return $result;
    }
    
}
