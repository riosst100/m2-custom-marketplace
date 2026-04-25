<?php

namespace TCGCollective\ProductAttributesLink\Controller\Adminhtml\CardGamesSets;

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
use TCGCollective\ProductAttributesLink\Model\ResourceModel\CardGamesSets\CollectionFactory;

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

    public function convertToFileName($str) 
    {
        // Convert the string to lowercase
        $string = strtolower($str);

        // Replace spaces and special characters with underscores
        $string = preg_replace('/[^a-z0-9]+/', '_', $string);

        // Trim any leading or trailing underscores
        $string = trim($string, '_');

        return $string;
    }

    /**
     * @return Redirect|ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $mainAttrCode = 'card_game';

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->mappingHelper = $objectManager->create(\TCGCollective\ProductAttributesLink\Helper\Data::class);

        $name = '_sets_export_'.date('Y-m-d') .' '.date('H-i-s');;
        $file = 'export/sets/'. $name . '.csv';
        $allowedFields = [
            'images',
            'card_release',
            'card_set',
            'card_game',
            'card_set_jp',
            'card_set_abbreviation',
            'print_version',
            'release_type',
            'release_date',
            'release_year',
            'number_of_cards',
            'card_manufacturer',
            'language_version',
            'language',
            'card_series',
            'card_set_symbol',
            // 'card_set_jp_en',
            // 'secret_lair_drop',
            // 'secret_lair_card_set',
            // 'insert_card_set_abbreviation',
            'bar_code',
            'bar_code_number'         
        ];

        $refererUrl = $this->_redirect->getRefererUrl();

        $mainField = 'card_game';
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
                $gameLabel = '';
                foreach ($collection as $key => $model) {
                    $data = $model->getData();
                    if ($data) {
                        if (isset($data['card_game']) && $data['card_game']) {
                            $gameLabel = $this->mappingHelper->getOptionLabelById('card_game', $data['card_game']);
                            $data['card_game'] = $gameLabel;
                        }
                        $params[] = $data;
                        $headers = array_keys($data);
                    }
                }

                

                if ($gameLabel) {
                    $name = $this->convertToFileName($gameLabel) . $name;
                }

                $name = 'card_game_' . $name;

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
