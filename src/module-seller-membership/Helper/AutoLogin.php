<?php
namespace TCGCollective\SellerMembership\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Store\Model\StoreManagerInterface;

class AutoLogin extends AbstractHelper
{
    protected $encryptor;
    protected $customerFactory;
    protected $storeManager;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        EncryptorInterface $encryptor,
        CustomerFactory $customerFactory,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->encryptor = $encryptor;
        $this->customerFactory = $customerFactory;
        $this->storeManager = $storeManager;
    }

    public function generateToken($customerId)
    {
        $data = json_encode([
            'customer_id' => $customerId,
            'timestamp' => time()
        ]);
        return base64_encode($this->encryptor->encrypt($data));
    }

    public function validateToken($token)
    {
        try {
            $decoded = $this->encryptor->decrypt(base64_decode($token));
            $data = json_decode($decoded, true);

            if (!isset($data['customer_id']) || !isset($data['timestamp'])) {
                return false;
            }

            // token expires in 5 minutes
            if (time() - $data['timestamp'] > 300) {
                return false;
            }

            return $data['customer_id'];
        } catch (\Exception $e) {
            return false;
        }
    }
}
