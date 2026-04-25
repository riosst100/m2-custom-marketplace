<?php

namespace TCGCollective\ProductAttributesLink\Controller\Adminhtml\LivingCardGameSets;

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
use TCGCollective\ProductAttributesLink\Model\ResourceModel\LivingCardGameSets\CollectionFactory;

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

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param LayoutInterface $layout
     * @param Filesystem $filesystem
     * @param FileFactory $fileFactory
     * @param CollectionFactory $collectionFactory
     * @throws FileSystemException
     */
    public function __construct(
        Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Registry $coreRegistry,
        LayoutInterface $layout,
        Filesystem $filesystem,
        FileFactory $fileFactory,
        CollectionFactory $collectionFactory
    ) {
        parent::__construct($context,$resultPageFactory,$coreRegistry);
        $this->_layout = $layout;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->fileFactory = $fileFactory;
        $this->collectionFactory = $collectionFactory;
    }


    /**
     * @return Redirect|ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $name = 'living_card_game_sets_export_'.date('Y-m-d');
        $file = 'export/sets/'. $name . '.csv';
        $allowedFields = [
            'images',
            'living_card_set',
            'living_card_set_abbreviation',
            'living_card_set_type',
            'living_card_set_series',
            'release_year',
            'release_date',
            'card_manufacturer',
            'language',
            'item_number',
            'living_card_sealed_products',
            'living_card_supplies',
            'age_level',
            'living_card_series',
            'living_card_brand',
            'bar_code',
            'bar_code_number'
        ];

        $refererUrl = $this->_redirect->getRefererUrl();

        $mainField = 'living_card_game';
        $mainFieldValue = null;

        if ($refererUrl) {
            if (preg_match('/'.$mainField.'\/(\d+)/', $refererUrl, $matches)) {
                $mainFieldValue = $matches[1]; // The first capturing group contains the set_id
            }
        }

        // dd($mainFieldValue);

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($mainFieldValue) {
            try {
                $headers = [];

                // init model and delete
                $collection = $this->collectionFactory->create();
                $collection->addFieldToFilter($mainField, $mainFieldValue);
                $collection->addFieldToSelect($allowedFields);
                
                $params = [];
                foreach ($collection as $key => $model) {
                    $data = $model->getData();
                    if ($data) {
                        $params[] = $data;
                        $headers = array_keys($data);
                    }
                }

                $this->directory->create('export');
                $stream = $this->directory->openFile($file, 'w+');
                $stream->lock();
                $stream->writeCsv($headers);
                foreach ($params as $row) {
                    $imageName = $row['images'] ? basename($row['images']) : '';
                    $row['images'] = $imageName;
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
        }

        // display error message
        $this->messageManager->addError(__('We can\'t find a record to exportcsv.'));
        // go to grid
        return $resultRedirect->setPath($refererUrl);
    }
}
