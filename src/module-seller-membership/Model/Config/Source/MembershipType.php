<?php

namespace TCGCollective\SellerMembership\Model\Config\Source;

class MembershipType extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    public function getAllOptions()
    {
        $options = [
            [
                'label' => __('Platform Membership'),
                'value' => 'platform'
            ],
            [
                'label' => __('Listing Country Membership'),
                'value' => 'listing_country'
            ]
        ];

        return $options;
    }
}
