<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace TCGCollective\MarketplaceProductImportExport\Controller\Marketplace\Image;

use Exception;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\MediaGalleryUi\Model\UploadImage;
use Psr\Log\LoggerInterface;

/**
 * Controller responsible to upload the media gallery content
 */
class Upload extends Action implements HttpPostActionInterface
{
    private const HTTP_OK = 200;
    private const HTTP_BAD_REQUEST = 400;

    /**
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Magento_MediaGalleryUiApi::upload_assets';

    /**
     * @var UploadImage
     */
    private $uploadImage;

    /**
     * @var LoggerInterface
     */
    private $logger;

    protected $resourceConnection;

    /**
     * @param Context $context
     * @param UploadImage $upload
     * @param LoggerInterface $logger
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     */
    public function __construct(
        Context $context,
        UploadImage $upload,
        LoggerInterface $logger,
        \Magento\Framework\App\ResourceConnection $resourceConnection
    ) {
        parent::__construct($context);
        $this->uploadImage = $upload;
        $this->logger = $logger;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        /** @var Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $targetFolder = $this->getRequest()->getParam('target_folder');
        $type = $this->getRequest()->getParam('type');

        if (!$targetFolder) {
            $responseContent = [
                'success' => false,
                'message' => __('The target_folder parameter is required.'),
            ];
            $resultJson->setHttpResponseCode(self::HTTP_BAD_REQUEST);
            $resultJson->setData($responseContent);
            return $resultJson;
        }

        if ($targetFolder == "/") {
            $responseContent = [
                'success' => false,
                'message' => __("Please select or create a folder first."),
            ];
            $resultJson->setHttpResponseCode(self::HTTP_OK);
            $resultJson->setData($responseContent);
            return $resultJson;
        }

        // $imageName = $_FILES['image']['name'] ?? null;
        // if ($imageName) {
        //     $imageName = str_replace(" ", "_", $imageName);
        //     $fullPathImage = $targetFolder . '/' . $imageName;
        //     dd($fullPathImage);
        //     // Check if the image path already exists in the database
        //     $connection = $this->resourceConnection->getConnection();
        //     $select = $connection->select()
        //         ->from($this->resourceConnection->getTableName('media_gallery_asset'))
        //         ->where('path = ?', $targetFolder);
        //     $existing = $connection->query($select)->fetchAll();
        //     // $existing = $this->resourceConnection->create()->getCollection()
        //     //     ->addFieldToFilter('path', $targetFolder)
        //     //     ->getSize();
        //     if (count($existing) > 0) {
        //         $responseContent = [
        //             'success' => false,
        //             'message' => __("This image wasn't uploaded because it's a duplicate."),
        //         ];
        //         $resultJson->setHttpResponseCode(self::HTTP_OK);
        //         $resultJson->setData($responseContent);
        //         return $resultJson;
        //     }
        // }

        try {
            $this->uploadImage->execute($targetFolder, $type);
            $responseCode = self::HTTP_OK;
            $responseContent = [
                'success' => true,
                'message' => __('The image was uploaded successfully.'),
            ];
        } catch (LocalizedException $exception) {
            $responseCode = self::HTTP_OK;
            $responseContent = [
                'success' => false,
                'message' => $exception->getMessage(),
            ];
        } catch (Exception $exception) {
            $this->logger->critical($exception);
            $responseCode = self::HTTP_OK;
            $responseContent = [
                'success' => false,
                'message' => __('Could not upload image.'),
            ];
        }

        $resultJson->setHttpResponseCode($responseCode);
        $resultJson->setData($responseContent);
        return $resultJson;
    }
}
