<?php
namespace TCGCollective\AgeVerification\Model\Resolver;

use Magento\Framework\GraphQl\Query\ResolverInterface;

class Verify implements ResolverInterface
{
    protected $helper;

    public function __construct(
        \Lof\AgeVerification\Helper\Data $helper
    ){
        $this->helper = $helper;
    }

    public function resolve($field, $context, $info, array $value = null, array $args = null)
    {
        $dob = $args['dob'];

        // Save to cookie/session
        $this->helper->setVerified($dob);

        return true;
    }
}
