<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace TCGCollective\MarketplaceProductImportExport\Ui\Component;

/**
 * Image Uploader component
 */
class ImageUploaderStandAlone extends \Magento\MediaGalleryUi\Ui\Component\ImageUploader
{

    /**
     * @inheritdoc
     */
    public function prepare(): void
    {
        parent::prepare();
        $this->setData(
            'config',
            array_replace_recursive(
                (array) $this->getData('config'),
                [
                    'actionsPath' => 'seller_media_gallery_listing.seller_media_gallery_listing' .
                        '.media_gallery_columns.thumbnail_url',
                    'directoriesPath' => 'seller_media_gallery_listing.seller_media_gallery_listing' .
                        '.media_gallery_directories',
                    'messagesPath' => 'seller_media_gallery_listing.seller_media_gallery_listing.messages'
                ]
            )
        );
    }
}
