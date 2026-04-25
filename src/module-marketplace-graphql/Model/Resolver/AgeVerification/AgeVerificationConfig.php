<?php
declare(strict_types=1);

namespace TCGCollective\MarketplaceGraphQl\Model\Resolver\AgeVerification;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class AgeVerificationConfig implements ResolverInterface
{
    protected $catalogHelper;
    
    public function __construct(
        \TCGCollective\MarketPlace\Helper\Catalog $catalogHelper
    ) {
        $this->catalogHelper = $catalogHelper;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $attributeCode = $args['attribute_code'] ?? null;
        $attributeOption = $args['attribute_option'] ?? null;
        $categoryUid = $args['category_uid'] ?? null;

        $path = 'marketplace_age_verification/age_verification/enable_by_attribute_option';
        $enableConfigByAttributeOptionArr = $this->catalogHelper->getArraySerializedConfigValue($path);

        // die('<pre>'.print_r($enableConfigByAttributeOptionArr,true));

        foreach ($enableConfigByAttributeOptionArr as $row) {
            // Get prefix
            if (
                $row['attribute_code'] == $attributeCode 
                && $row['attribute_option'] == $attributeOption
            ) {
                return [
                    'status' => $row['status'],
                    'minimum_age' => 18
                ];
            }
        }

        return null;
    }
}
