<?php
namespace TCGCollective\MarketplaceOthersFee\Model\Resolver\Cart;

use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Quote\Model\QuoteRepository;
use Lof\Paymentfee\Helper\Data;

class PaymentFees implements ResolverInterface
{
    protected $quoteRepository;

    public function __construct(
        QuoteRepository $quoteRepository,
        Data $helper
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->helper = $helper;
    }

    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/fee.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        // $logger->info('resolver');

        if (!isset($value['model']) || !$value['model'] instanceof \Magento\Quote\Model\Quote) {
            return null;
        }

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $value['model'];

        $paymentFee = $quote->getData('payment_fee');
        if (!$paymentFee) {
            return [];
        }

        /**
         * Example: payment_fee is stored as JSON:
         * [{"title":"Admin Fee","value":100},{"title":"Processing","value":200}]
         * or as serialized string, or custom delimited value.
         */
        $result = [];
        $paymentFee = $this->helper->getFeeApplied($quote);
        // $logger->info('Payment Fee: '.$paymentFee);

        // 👇 Adjust this parsing logic as per your storage format
        if ($this->isJson($paymentFee)) {
            $result = json_decode($paymentFee, true);
        } else {
            // Example if stored as "Admin Fee:100|Processing:200"
            $parts = explode('|', $paymentFee);
            foreach ($parts as $part) {
                [$title, $value] = explode(':', $part) + [null, 0];
                $result[] = ['title' => $title, 'value' => (float)$value];
            }
        }

        return $result;
    }

    private function isJson($string)
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
}
