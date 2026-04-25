<?php

namespace TCGCollective\ProductAttributesLink\Controller\Adminhtml\Import;

use Magento\Backend\App\Action;

class GetAttributesOptions extends \TCGCollective\ProductAttributesLink\Controller\Adminhtml\Index
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

    protected $actionToySets;
    protected $dollsAttributesFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \TCGCollective\ProductAttributesLink\Helper\Data $helper,
        \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory,
        \TCGCollective\ProductAttributesLink\Model\DollsAttributesFactory $dollsAttributesFactory
    ) {
        parent::__construct($context, $resultPageFactory, $registry);
        $this->helper = $helper;
        $this->jsonResultFactory = $jsonResultFactory;
        $this->dollsAttributesFactory = $dollsAttributesFactory;
    }

    public function execute()
    {
        $data = [
            'data' => []
        ];

        $result = [];

        $entity = $this->getRequest()->getParam('entity') ?: null;
        if ($entity) {
            if ($entity == "dolls") {
                $result = [
                    [
                        'label' => 'doll_brand',
                        'value' => 'doll_brand'
                    ],
                    [
                        'label' => 'doll_toy_line',
                        'value' => 'doll_toy_line'
                    ],
                    [
                        'label' => 'doll_scale',
                        'value' => 'doll_scale'
                    ],
                    [
                        'label' => 'doll_type',
                        'value' => 'doll_type'
                    ],
                    [
                        'label' => 'doll_product_type',
                        'value' => 'doll_product_type'
                    ],
                    [
                        'label' => 'doll_feature',
                        'value' => 'doll_feature'
                    ]
                ];
            }
        }

        $data['data'] = $result;

        $result = $this->jsonResultFactory->create();
        $result->setData($data);

        return $result;
    }
}