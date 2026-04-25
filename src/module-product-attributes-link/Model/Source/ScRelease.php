<?php
namespace TCGCollective\ProductAttributesLink\Model\Source;

use Magento\Framework\Option\ArrayInterface;
use TCGCollective\ProductAttributesLink\Model\ResourceModel\SportCardsSets\CollectionFactory;
use Psr\Log\LoggerInterface as Logger;

class ScRelease implements ArrayInterface
{
    protected $request;


    /**
     * @var array
     */
    private $options;

    /**
     * @var Logger
     */
    private $logger;
    
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param Logger $logger
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Logger $logger,
        CollectionFactory $collectionFactory
    ) {
        $this->logger = $logger;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    $this->request = $objectManager->get(\Magento\Framework\App\Request\Http::class);

        if (!$this->options) {
            $this->options = [];

            $mainEntityAttrCode = "sc_categories";
            $releaseAttrCode = "sc_release";
            $mainEntityAttrVal = $this->request->getParam($mainEntityAttrCode);
            if ($mainEntityAttrVal) {
                $collection = $this->collectionFactory->create();
                $collection->addFieldToFilter($mainEntityAttrCode, $mainEntityAttrVal);
                $collection->getSelect()->group($releaseAttrCode);
                if ($collection->getSize()) {
                    foreach ($collection as $model) {
                        $this->options[] = [
                            'label' => $model->getData($releaseAttrCode),
                            'value' => $model->getData($releaseAttrCode)
                        ];
                    }
                }
            }
        }

        return $this->options;
    }
}