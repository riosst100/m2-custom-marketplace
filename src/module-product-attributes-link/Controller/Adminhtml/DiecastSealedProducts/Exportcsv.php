<?php

namespace TCGCollective\ProductAttributesLink\Controller\Adminhtml\CardGamesSealedProducts;

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
use TCGCollective\ProductAttributesLink\Model\ResourceModel\CardGamesSealedProducts\CollectionFactory;

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
    protected $mappingHelper;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param LayoutInterface $layout
     * @param Filesystem $filesystem
     * @param FileFactory $fileFactory
     * @param CollectionFactory $collectionFactory
     * @param \TCGCollective\ProductAttributesLink\Helper\Data $mappingHelper
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
        \TCGCollective\ProductAttributesLink\Helper\Data $mappingHelper
    ) {
        parent::__construct($context,$resultPageFactory,$coreRegistry);
        $this->_layout = $layout;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->fileFactory = $fileFactory;
        $this->collectionFactory = $collectionFactory;
        $this->mappingHelper = $mappingHelper;
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
        $name = '_sealed_products_export_'.date('Y-m-d').' '.date('H-i-s');
        $file = 'export/sealed_products/'. $name . '.csv';
        $allowedFields = [
            "images",
            "card_release",
            "card_release_jp",
            "sealed_products",
            "name",
            "supplier_sku",
            "release_type",
            "release_date",
            "release_year",
            "card_print_version",
            "bar_code",
            "bar_code_no",
            "language_version",
            "language"
        ];

        $setsTableName = 'marketplace_card_games_sets';
        $setsFieldToSelet = [
            $mainAttrCode
        ];

        $refererUrl = $this->_redirect->getRefererUrl();

        $cardRelease = null;
        $language = null;
        $cardGame = null;

        if ($refererUrl) {
            if (preg_match('/card_game\/([^\/]+)/', $refererUrl, $matches)) {
                $cardGame = urldecode($matches[1]); // The first capturing group contains the value after 'language/'
            }
        }

        // dd($cardGame);

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        // if ($cardRelease) {
            try {
                $headers = [];

                // init model and delete
                $collection = $this->collectionFactory->create();
                if ($cardRelease) {
                    $collection->addFieldToFilter('main_table.card_release', $cardRelease);
                }
                if ($language) {
                    $collection->addFieldToFilter('main_table.language', $language);
                }

                if ($cardGame) {
                    $collection->addFieldToFilter('main_table.card_game', $cardGame);
                }
                
                $collection->addFieldToSelect($allowedFields);

                $collection->getSelect()->joinLeft(
                    ['sets' => $setsTableName],
                    'main_table.card_release=sets.card_release',
                    $setsFieldToSelet
                )->group('main_table.entity_id');

                // dd($collection->getSelect()->__toString());

                if ($collection->getSize()) {
                
                $params = [];
                $gameLabel = '';
                foreach ($collection as $key => $model) {
                    $data = $model->getData();
                    // dd($data);
                    if (isset($data['card_game']) && $data['card_game']) {
                            $gameLabel = $this->mappingHelper->getOptionLabelById('card_game', $data['card_game']);
                            $data['card_game'] = $gameLabel;
                        }
                    if ($data) {
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
                $rowData = [];
                $rowData[] = $headers;
                foreach ($params as $row) {
                    $imageName = $row['images'] ? basename($row['images']) : '';
                    $row['images'] = $imageName;
                    $rowData[] = $row;
                    $stream->writeCsv($row);
                }
                $stream->unlock();
                $stream->close();

                $fullFilePath =  $this->directory->getAbsolutePath($file);
// dd($fullFilePath);
                // display success message
                $handle = fopen($fullFilePath, 'w');

                // Menambahkan BOM di awal
                fwrite($handle, "\xEF\xBB\xBF");

                // Menulis data produk ke file CSV
                foreach ($rowData as $row) {
                    fputcsv($handle, $row);
                }

                $file = [
                    'type' => 'filename',
                    'value' => $file,
                    'rm' => true  // can delete file after use
                ];

                
                
                return $this->fileFactory->create($name . '.csv', $file, 'var');
            } else {
                $this->messageManager->addError(__('We can\'t find a record to exportcsv.'));
            }
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addError($e->getMessage());
                // go back to edit form
                
            }
        // }

        // display error message
        
        // go to grid
        return $resultRedirect->setPath($refererUrl);
    }
}
