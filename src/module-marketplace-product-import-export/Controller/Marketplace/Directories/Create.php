<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace TCGCollective\MarketplaceProductImportExport\Controller\Marketplace\Directories;

use Exception;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\MediaGalleryApi\Api\CreateDirectoriesByPathsInterface;
use Psr\Log\LoggerInterface;
use Lof\MarketPlace\Helper\Data;
use Magento\Framework\Filesystem;

/**
 * Controller to create the folders
 */
class Create extends Action implements HttpPostActionInterface
{
    private const HTTP_OK = 200;
    private const HTTP_INTERNAL_ERROR = 500;
    private const HTTP_BAD_REQUEST = 400;

    /**
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magento_MediaGalleryUiApi::create_folder';

    /**
     * @var CreateDirectoriesByPathsInterface
     */
    private $createDirectoriesByPaths;

    /**
     * @var LoggerInterface
     */
    private $logger;

    private $data;

    private $filesystem;

    /**
     * @param Context $context
     * @param CreateDirectoriesByPathsInterface $createDirectoriesByPaths
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        CreateDirectoriesByPathsInterface $createDirectoriesByPaths,
        LoggerInterface $logger,
        Data $data,
        Filesystem $filesystem
    ) {
        parent::__construct($context);

        $this->createDirectoriesByPaths = $createDirectoriesByPaths;
        $this->logger = $logger;
        $this->data = $data;
        $this->filesystem = $filesystem;
    }

    /**
     * Create folder by provided path.
     */
    public function execute()
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/directories.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        // $logger->info('Create Folder'); 

        /** @var Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $paths = $this->getRequest()->getParam('paths');
        // $logger->info('Path: '. json_encode($paths));

        if (!$paths) {
            $responseContent = [
                'success' => false,
                'message' => __('Folder paths parameter is required.'),
            ];
            $resultJson->setHttpResponseCode(self::HTTP_BAD_REQUEST);
            $resultJson->setData($responseContent);

            return $resultJson;
        }

        try {
            // $logger->info('Real path: '.json_encode($paths));
            // Get seller ID from helper
            $sellerId = $this->data->getSellerId();

            if ($sellerId) {
                /** @var \Magento\Framework\Filesystem $filesystem */
                $mediaDirectory = $this->filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);

                $sellerBasePath = 'seller/' . $sellerId;

                // If directory seller/{id} does not exist, create it
                if (!$mediaDirectory->isExist($sellerBasePath)) {
                    $mediaDirectory->create($sellerBasePath);
                }

                // Normalize $paths (in case it’s an array from JS)
                if (is_array($paths)) {
                    $paths = array_map(function ($path) use ($sellerBasePath) {
                        $normalized = str_replace('\\', '/', $path);
                        if (strpos($normalized, $sellerBasePath) !== 0) {
                            // prepend seller/{id}/
                            $normalized = rtrim($sellerBasePath, '/') . '/' . ltrim($normalized, '/');
                        }
                        return $normalized;
                    }, $paths);
                } else {
                    $normalized = str_replace('\\', '/', $paths);
                    if (strpos($normalized, $sellerBasePath) !== 0) {
                        $normalized = rtrim($sellerBasePath, '/') . '/' . ltrim($normalized, '/');
                    }
                    $paths = [$normalized];
                }
            } else {
                // if seller id is missing, prevent accidental folder creation
                throw new LocalizedException(__('Seller ID not found.'));
            }
            // $logger->info('Final path: '.json_encode($paths));

            $this->createDirectoriesByPaths->execute($paths);

            $responseCode = self::HTTP_OK;
            $responseContent = [
                'success' => true,
                'message' => __('You have successfully created the folder.'),
            ];
        } catch (LocalizedException $exception) {
            $logger->info('Catch 1: '. $exception->getMessage());
            $responseCode = self::HTTP_BAD_REQUEST;
            $responseContent = [
                'success' => false,
                'message' => $exception->getMessage(),
            ];
        } catch (Exception $exception) {
            $logger->info('Catch 2: '. $exception->getMessage());
            $this->logger->critical($exception);
            $responseCode = self::HTTP_INTERNAL_ERROR;
            $responseContent = [
                'success' => false,
                'message' => __('An error occurred on attempt to create folder.'),
            ];
        }

        $resultJson->setHttpResponseCode($responseCode);
        $resultJson->setData($responseContent);

        return $resultJson;
    }
}
