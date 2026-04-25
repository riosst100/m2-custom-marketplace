<?php

namespace TCGCollective\ProductAttributesLink\Controller\Adminhtml\Import;

use Magento\Backend\App\Action;

class GetReleaseData extends \TCGCollective\ProductAttributesLink\Controller\Adminhtml\Index
{
    protected $jsonResultFactory;


    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \TCGCollective\ProductAttributesLink\Helper\Data
     */
    protected $helper;
    
    protected $cardGameSets;
    protected $legoSets;
    protected $sportCardsSets;
    protected $nonSportCardsSets;
    protected $miniatureGameSets;
    protected $livingCardGameSets;

    protected $actionToySets;
    protected $animeSets;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \TCGCollective\ProductAttributesLink\Helper\Data $helper,
        \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory,
        \TCGCollective\ProductAttributesLink\Model\CardGamesSetsFactory $cardGameSets,
        \TCGCollective\ProductAttributesLink\Model\LegoSetsFactory $legoSets,
        \TCGCollective\ProductAttributesLink\Model\LivingCardGameSetsFactory $livingCardGameSets,
        \TCGCollective\ProductAttributesLink\Model\SportCardsSetsFactory $sportCardsSets,
        \TCGCollective\ProductAttributesLink\Model\NonSportCardsSetsFactory $nonSportCardsSets,
        \TCGCollective\ProductAttributesLink\Model\MiniatureGameSetsFactory $miniatureGameSets,
        \TCGCollective\ProductAttributesLink\Model\ActionToySetsFactory $actionToySets,
        \TCGCollective\ProductAttributesLink\Model\AnimeSetsFactory $animeSets
    ) {
        parent::__construct($context, $resultPageFactory, $registry);
        $this->helper = $helper;
        $this->jsonResultFactory = $jsonResultFactory;
        $this->cardGameSets = $cardGameSets;
        $this->livingCardGameSets = $livingCardGameSets;
        $this->legoSets = $legoSets;
        $this->sportCardsSets = $sportCardsSets;
        $this->nonSportCardsSets = $nonSportCardsSets;
        $this->miniatureGameSets = $miniatureGameSets;
        $this->actionToySets = $actionToySets;
        $this->animeSets = $animeSets;
    }

    public function execute()
    {
        $data = [
            'data' => []
        ];

        $result = [];

        $entity = $this->getRequest()->getParam('entity') ?: null;
        $releaseYear = $this->getRequest()->getParam('release_year') ?: null;
        $categories = $this->getRequest()->getParam('categories') ?: null;
        if ($entity) {
            if ($entity == "sport_card") {
                $options = [];
                $attribute = [
                    'code' => 'release',
                    'label' => 'Release'
                ];
                $collection = $this->sportCardsSets->create()
                ->getCollection()
                ->addFieldToFilter('sc_categories', $categories)
                ->addFieldToFilter('release_year', $releaseYear);
                $collection->getSelect()->group("sc_release")->order('sc_release', 'ASC');
                if ($collection->getSize()) {
                    foreach($collection as $model) {
                        $options[] = [
                            'label' => $model->getData('sc_release'),
                            'value' => $model->getData('sc_release')
                        ];
                    }
                }

                $result[] = [
                    'attribute' => $attribute,
                    'options' => $options
                ];
            }

            if ($entity == "action_toys") {
                $brand = $this->getRequest()->getParam('toy_brand') ?? null;
                $options = [];
                $attribute = [
                    'code' => 'toy_line',
                    'label' => 'Toy Line'
                ];
                $collection = $this->actionToySets->create()
                ->getCollection()
                ->addFieldToFilter('sub_categories', $categories)
                ->addFieldToFilter('toy_brand', $brand);
                $collection->getSelect()->group('toy_line')->order('toy_line', 'ASC');
                if ($collection->getSize()) {
                    foreach($collection as $model) {
                        $options[] = [
                            'label' => $model->getData('toy_line'),
                            'value' => $model->getData('toy_line')
                        ];
                    }
                }

                $result[] = [
                    'attribute' => $attribute,
                    'options' => $options
                ];
            }
        }

        $data['data'] = $result;

        $result = $this->jsonResultFactory->create();
        $result->setData($data);

        return $result;
    }
}