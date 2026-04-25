<?php
namespace TCGCollective\MarketplaceGraphQl\Model\Resolver;

use Exception;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\PhpCookieManager;
use Lof\Gdpr\Helper\Data;
use Magento\Framework\DataObject;
use Psr\Log\LoggerInterface;

class DeleteAccount implements ResolverInterface
{
    protected $customerRepository;
    protected $customerSession;
    protected $registry;
    protected $logger;
    protected $helper;
    protected $eventManager;
    private $cookieMetadataFactory;
    private $cookieManager;

    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        Session $customerSession,
        Registry $registry,
        LoggerInterface $logger,
        Data $helper,
        ManagerInterface $eventManager
    ) {
        $this->customerRepository = $customerRepository;
        $this->customerSession    = $customerSession;
        $this->registry           = $registry;
        $this->logger             = $logger;
        $this->helper             = $helper;
        $this->eventManager       = $eventManager;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!method_exists($context, 'getUserId') || !$context->getUserId()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }
        $customerId = (int)$context->getUserId();
        $customer = $this->customerRepository->getById($customerId);
        if (!$customer || !$customer->getId()) {
            throw new GraphQlAuthorizationException(__('You must be logged in to delete your account.'));
        }

        if (!$this->helper->allowDeleteAccount()) {
            throw new LocalizedException(__('Account deletion is not allowed.'));
        }

        $customerId = $customer->getId();
        $checktoken = new DataObject(['flag' => true]);
        $this->eventManager->dispatch('anonymise_account_before_delete', compact('customer', 'checktoken'));

        if (!$checktoken->getFlag()) {
            throw new LocalizedException(__('Account deletion is blocked by a pre-delete event.'));
        }

        try {
            $this->registry->register('isSecureArea', true, true);
            $this->customerRepository->deleteById($customerId);
            $this->eventManager->dispatch('anonymise_account_after_delete', ['customer' => $customer]);

            // delete session cookie
            $cookieManager = $this->getCookieManager();
            if ($cookieManager->getCookie('mage-cache-sessid')) {
                $metadata = $this->getCookieMetadataFactory()->createCookieMetadata();
                $metadata->setPath('/');
                $cookieManager->deleteCookie('mage-cache-sessid', $metadata);
            }

            return [
                'success' => true,
                'message' => __('Your account has been deleted successfully.')
            ];
        } catch (Exception $e) {
            $this->logger->critical($e->getMessage());
            return [
                'success' => false,
                'message' => __('Something went wrong while deleting your account. Please contact the store owner.')
            ];
        }
    }

    private function getCookieManager()
    {
        if (!$this->cookieManager) {
            $this->cookieManager = ObjectManager::getInstance()->get(PhpCookieManager::class);
        }
        return $this->cookieManager;
    }

    private function getCookieMetadataFactory()
    {
        if (!$this->cookieMetadataFactory) {
            $this->cookieMetadataFactory = ObjectManager::getInstance()->get(CookieMetadataFactory::class);
        }
        return $this->cookieMetadataFactory;
    }
}
