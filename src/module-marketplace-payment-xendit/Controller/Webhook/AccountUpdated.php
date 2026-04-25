<?php
namespace TCGCollective\MarketplacePaymentXendit\Controller\Webhook;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;

class AccountUpdated extends \Magento\Framework\App\Action\Action implements HttpPostActionInterface, CsrfAwareActionInterface
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $jsonFactory;
    protected $paymentHelper;

    public function __construct(
        Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \TCGCollective\MarketPlace\Helper\Payments $paymentHelper
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->paymentHelper = $paymentHelper;
        parent::__construct(
            $context
        );
    }
    
	public function execute()
	{
        $result = $this->jsonFactory->create();
        $result->setHttpResponseCode(200);

        // Ambil raw body JSON dari request
        $rawBody = file_get_contents('php://input');
        if ($rawBody) {
            // Decode ke array
            $requestData = json_decode($rawBody, true);
            if ($this->paymentHelper->accountUpdatedXenditWebhook($requestData)) {
                return $result->setData(['success' => true]);
            }
        }

        return $result->setData(['success' => false]);
    }

    // Bypass CSRF validation (wajib untuk webhook eksternal)
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}