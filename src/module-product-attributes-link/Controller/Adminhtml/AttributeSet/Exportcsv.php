<?php

namespace TCGCollective\ProductAttributesLink\Controller\Adminhtml\AttributeSet;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutInterface;
use TCGCollective\ProductAttributesLink\Model\ResourceModel\ProductAttributesLink\CollectionFactory;

class Exportcsv extends \TCGCollective\ProductAttributesLink\Controller\Adminhtml\Index
{
    /**
     * @var LayoutInterface
     */
    protected $_layout;
    /**
     * @var Filesystem\Directory\WriteInterface
     */
    protected $directory;
    /**
     * @var FileFactory
     */
    protected $fileFactory;
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;
    protected $resultPageFactory;
    protected $helper;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param LayoutInterface $layout
     * @param Filesystem $filesystem
     * @param FileFactory $fileFactory
     * @param CollectionFactory $collectionFactory
     * @param \TCGCollective\ProductAttributesLink\Helper\Data $helper
     * @throws FileSystemException
     */
    public function __construct(
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Registry $coreRegistry,
        LayoutInterface $layout,
        Filesystem $filesystem,
        FileFactory $fileFactory,
        CollectionFactory $collectionFactory,
        \TCGCollective\ProductAttributesLink\Helper\Data $helper
    ) {
        parent::__construct($context,$resultPageFactory,$coreRegistry);
        $this->_layout = $layout;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->fileFactory = $fileFactory;
        $this->collectionFactory = $collectionFactory;
        $this->helper = $helper;
    }


    /**
     * @return Redirect|ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $name = 'attribute_set_mapping_export_'.date('Y-m-d');
        $file = 'export/attribute_set_mapping/'. $name . '.csv';
        $allowedFields = [
            'category_id',
            'group_name',
            'attribute_code',
            'attribute_option_id',
            'target_attribute_set_id',
            'priority'
        ];

        $refererUrl = $this->_redirect->getRefererUrl();

        // $mainField = 'card_game';
        // $mainFieldValue = null;

        // if ($refererUrl) {
        //     if (preg_match('/'.$mainField.'\/(\d+)/', $refererUrl, $matches)) {
        //         $mainFieldValue = $matches[1]; // The first capturing group contains the set_id
        //     }
        // }

        // dd($mainFieldValue);

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        // if ($mainFieldValue) {
            try {
                $headers = [];

                // init model and delete
                $collection = $this->collectionFactory->create();
                $collection->addFieldToFilter('mapping_type', 'attribute_set');
                $collection->addFieldToSelect($allowedFields);

                $categoryEntityTable = $collection->getTable('catalog_category_entity_varchar');
                $collection->getSelect()->joinLeft(
                    ['cce' => $categoryEntityTable],
                    'main_table.category_id = cce.entity_id',
                    ['categories' => new \Zend_Db_Expr('COALESCE(cce.value, "")')]
                );

                $categoryEntityTable = $collection->getTable('eav_attribute_set');
                $collection->getSelect()->joinLeft(
                    ['eas' => $categoryEntityTable],
                    'main_table.target_attribute_set_id = eas.attribute_set_id',
                    ['attribute_set_name' => new \Zend_Db_Expr('COALESCE(eas.attribute_set_name, "")')]
                );

                // Ensure NULL category_id values are explicitly included
                $collection->getSelect()->orWhere('main_table.category_id IS NULL');

                $collection->getSelect()->group('main_table.entity_id');
                $collection->getSelect()->order('main_table.entity_id ASC');
                
                $rawParams = [];
                foreach ($collection as $key => $model) {
                    $data = $model->getData();
                    if ($data) {
                        $data['group'] = $data['group_name'] ?? '';
                        $data['category'] = $data['categories'] ?? '';
                        $data['priority'] = $data['priority'] != 0 ? $data['priority'] : null;
                        // $data['attribute_code'] = $data['target_attribute_code'] ?? '';
                        $attrOption = $this->helper->getOptionLabelById($data['attribute_code'], $data['attribute_option_id']);
                        $data['attribute_option'] = $attrOption ?? null;
                        // dd($row);
                        $data['target_attribute_set'] = $data['attribute_set_name'] ?? '';

                        unset($data['group_name']);
                        unset($data['categories']);
                        unset($data['category_id']);
                        unset($data['entity_id']);
                        unset($data['attribute_option_id']);
                        unset($data['target_attribute_set_id']);
                        unset($data['attribute_set_name']);

                        $rawParams[] = $data;
                        
                    }
                }

                $headerOrder = ['group','category','attribute_code','attribute_option','priority','target_attribute_set'];

                $params = [];
                foreach ($rawParams as $row) {
                    $reorderedRow = [];
                    foreach ($headerOrder as $header) {
                        // if (isset($row[$header])) {
                            $reorderedRow[$header] = $row[$header];
                        // }
                    }
                    $params[] = $reorderedRow;
                }

                $headers = array_keys($params[0]);

                $this->directory->create('export');
                $stream = $this->directory->openFile($file, 'w+');
                $stream->lock();
                $stream->writeCsv($headers);
                foreach ($params as $row) {
                    $stream->writeCsv($row);
                }
                $stream->unlock();
                $stream->close();
                $file = [
                    'type' => 'filename',
                    'value' => $file,
                    'rm' => true  // can delete file after use
                ];

                // display success message
                
                return $this->fileFactory->create($name . '.csv', $file, 'var');
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addError($e->getMessage());
                // go back to edit form
            }
        // }

        // display error message
        $this->messageManager->addError(__('We can\'t find a record to exportcsv.'));
        // go to grid
        return $resultRedirect->setPath($refererUrl);
    }
}
