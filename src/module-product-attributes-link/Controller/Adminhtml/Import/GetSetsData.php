<?php

namespace TCGCollective\ProductAttributesLink\Controller\Adminhtml\Import;

use Magento\Backend\App\Action;

class GetSetsData extends \TCGCollective\ProductAttributesLink\Controller\Adminhtml\Index
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
    protected $dollsSets;

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
        \TCGCollective\ProductAttributesLink\Model\AnimeSetsFactory $animeSets,
        \TCGCollective\ProductAttributesLink\Model\DollsSetsFactory $dollsSets
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
        $this->dollsSets = $dollsSets;
    }

    public function execute()
    {
        $data = [
            'data' => []
        ];

        $result = [];

        $entity = $this->getRequest()->getParam('entity') ?: null;
        $release = $this->getRequest()->getParam('release') ?: null;
        $release_year = $this->getRequest()->getParam('release_year') ?: null;
        $setType = $this->getRequest()->getParam('sc_set_type') ?: null;
        if ($entity) {
            $categories = $this->getRequest()->getParam('categories') ?: null;
            if ($entity == "card_game") {
                $game = $this->helper->getOptionLabelById('card_game', $categories);
                if (!str_contains($game, "Secret Lair")) {
                    $options = [];
                    $attribute = [
                        'code' => 'set_id',
                        'label' => 'Sets'
                    ];
                    $sets = $categories ? $this->helper->getCardGameSets($categories) : null;
                    if ($sets) {
                        foreach($sets as $model) {
                            $options[] = [
                                'label' => $model->getCardSet(),
                                'value' => $model->getEntityId()
                            ];
                        }
                    }

                    $result[] = [
                        'attribute' => $attribute,
                        'options' => $options
                    ];
                }
            }
            if ($entity == "non_sport_card") {
                $options = [];
                $attribute = [
                    'code' => 'set_id',
                    'label' => 'Sets'
                ];
                $sets = $categories ? $this->helper->getNonSportCardSets($categories) : null;
                if ($sets) {
                    foreach($sets as $model) {
                        $options[] = [
                            'label' => $model->getNscSetName(),
                            'value' => $model->getEntityId()
                        ];
                    }
                }

                $result[] = [
                    'attribute' => $attribute,
                    'options' => $options
                ];
            }

            // DOLLS
            if ($entity == "dolls") {
                $toyLine = $this->getRequest()->getParam('toys_line') ?? null;
                if (!$toyLine) {
                    $options = [];
                    $attribute = [
                        'code' => 'toys_line',
                        'label' => 'Toy Line'
                    ];
                    $collection = $this->dollsSets->create()
                    ->getCollection()
                    ->addFieldToFilter('toys_brand', $categories);
                    $collection->getSelect()->group('toys_line')->order('toys_line ASC');
                    if ($collection->getSize()) {
                        foreach($collection as $model) {
                            $options[] = [
                                'label' => $model->getData('toys_line'),
                                'value' => $model->getData('toys_line')
                            ];
                        }
                    }

                    $result[] = [
                        'attribute' => $attribute,
                        'options' => $options
                    ];
                }
            }

            // ACTION TOYS
            // if ($entity == "action_toys") {
            //     $toyLine = $this->getRequest()->getParam('toys_line') ?? null;
            //     if ($toyLine) {
            //         $options = [];
            //         $attribute = [
            //             'code' => 'set_id',
            //             'label' => 'Sets'
            //         ];
            //         $collection = $this->actionToySets->create()
            //         ->getCollection()
            //         ->addFieldToFilter('toys_brand', $categories)
            //         ->addFieldToFilter('toys_line', $toyLine);
            //         $collection->getSelect()->group('set_name')->order('set_name ASC');
            //         if ($collection->getSize()) {
            //             foreach($collection as $model) {
            //                 if ($model->getData('set_name')) {
            //                     $options[] = [
            //                         'label' => $model->getData('set_name'),
            //                         'value' => $model->getEntityId()
            //                     ];
            //                 }
            //             }
            //         }

            //         $result[] = [
            //             'attribute' => $attribute,
            //             'options' => $options
            //         ];
            //     }

            //     if (!$toyLine) {
            //         $options = [];
            //         $attribute = [
            //             'code' => 'toys_line',
            //             'label' => 'Toy Line'
            //         ];
            //         $collection = $this->actionToySets->create()
            //         ->getCollection()
            //         ->addFieldToFilter('toys_brand', $categories);
            //         $collection->getSelect()->group('toys_line')->order('toys_line ASC');
            //         if ($collection->getSize()) {
            //             foreach($collection as $model) {
            //                 $options[] = [
            //                     'label' => $model->getData('toys_line'),
            //                     'value' => $model->getData('toys_line')
            //                 ];
            //             }
            //         }

            //         $result[] = [
            //             'attribute' => $attribute,
            //             'options' => $options
            //         ];
            //     }
            // }

            // SPORT CARD
            if ($entity == "sport_card") {
                if ($setType) {
                    $options = [];
                    $attribute = [
                        'code' => 'set_id',
                        'label' => 'Sets'
                    ];
                    $collection = $this->sportCardsSets->create()
                    ->getCollection()
                    ->addFieldToFilter('sc_categories', $categories)
                    ->addFieldToFilter('sc_set_type', $setType);
                    $collection->getSelect()->group('sc_set_name')->order('sc_set_name ASC');
                    if ($collection->getSize()) {
                        foreach($collection as $model) {
                            if ($model->getData('sc_set_name')) {
                                $options[] = [
                                    'label' => $model->getData('sc_set_name'),
                                    'value' => $model->getData('entity_id')
                                ];
                            }
                        }
                    }

                    $result[] = [
                        'attribute' => $attribute,
                        'options' => $options
                    ];
                }

                if (!$setType) {
                    $options = [];
                    $attribute = [
                        'code' => 'sc_set_type',
                        'label' => 'Release Type'
                    ];
                    $collection = $this->sportCardsSets->create()
                    ->getCollection()
                    ->addFieldToFilter('sc_categories', $categories);
                    $collection->getSelect()->group('sc_set_type')->order('sc_set_type DESC');
                    if ($collection->getSize()) {
                        foreach($collection as $model) {
                            $options[] = [
                                'label' => $model->getData('sc_set_type'),
                                'value' => $model->getData('sc_set_type')
                            ];
                        }
                    }

                    $result[] = [
                        'attribute' => $attribute,
                        'options' => $options
                    ];
                }
            }

            if ($entity == "miniature_game") {
                $options = [];
                $attribute = [
                    'code' => 'set_id',
                    'label' => 'Sets'
                ];
                $sets = $categories ? $this->helper->getMiniatureGameSets($categories) : null;
                if ($sets) {
                    foreach($sets as $model) {
                        $options[] = [
                            'label' => $model->getData('miniature_set'),
                            'value' => $model->getEntityId()
                        ];
                    }
                }

                $result[] = [
                    'attribute' => $attribute,
                    'options' => $options
                ];
            }
            if ($entity == "living_card_game") {
                $options = [];
                $attribute = [
                    'code' => 'set_id',
                    'label' => 'Sets'
                ];
                $sets = $categories ? $this->helper->getLivingCardGameSets($categories) : null;
                if ($sets) {
                    foreach($sets as $model) {
                        $options[] = [
                            'label' => $model->getCardSet(),
                            'value' => $model->getEntityId()
                        ];
                    }
                }

                $result[] = [
                    'attribute' => $attribute,
                    'options' => $options
                ];
            }
            if ($entity == "bricks_brand") {
                $options = [];
                $attribute = [
                    'code' => 'set_id',
                    'label' => 'Sets'
                ];
                $sets = $categories ? $this->helper->getLegoSets($categories, false, true) : null;
                if ($sets) {
                    foreach($sets as $model) {
                        $options[] = [
                            'label' => $model->getData('bricks_set_name'),
                            'value' => $model->getEntityId()
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