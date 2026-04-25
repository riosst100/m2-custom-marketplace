<?php
namespace TCGCollective\MarketplaceProductPreorder\Model\Cart\Processor;

use Magento\QuoteGraphQl\Model\CartItem\DataProvider\Processor\ItemDataProcessorInterface;
use Magento\GraphQl\Model\Query\ContextInterface;

class PreorderItemProcessor implements ItemDataProcessorInterface
{
    public function process(array $cartItemData, ContextInterface $context): array
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/addtocart.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        $logger->info('Processor triggered: ' . json_encode($cartItemData));

        foreach ($cartItemData as &$item) {
            if (!isset($item['preorder'])) {
                continue;
            }

            $preorder = $item['preorder'];

            $item['is_preorder'] = 1;

            if (!empty($preorder['pre_order_payment_type'])) {
                $item['pre_order_payment_type'] = $preorder['pre_order_payment_type'];
            }

            if (!empty($preorder['pre_order_deposit'])) {
                $item['pre_order_deposit'] = $preorder['pre_order_deposit'];
            }
        }

        return $cartItemData;
    }
}
