<?php
namespace TCGCollective\AgeVerification\Model\Resolver;

use Magento\Framework\GraphQl\Query\ResolverInterface;

class Category implements ResolverInterface
{
    protected $ageRepo;

    public function __construct(
        \Lof\AgeVerification\Api\AgeVerificationProductsRepositoryInterface $ageRepo
    ){
        $this->ageRepo = $ageRepo;
    }

    public function resolve($field, $context, $info, array $value = null, array $args = null)
    {
        $data = $this->ageRepo->getCategoryAgeVerification($args['id']);

        return [
            'requiredAge' => (int)$data->getRequiredAge(),
            'enabled' => (bool)$data->getIsActive()
        ];
    }
}
