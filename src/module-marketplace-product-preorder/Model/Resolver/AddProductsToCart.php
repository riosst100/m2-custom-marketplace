<?php
declare(strict_types=1);

namespace TCGCollective\MarketplaceProductPreorder\Model\Resolver;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GraphQl\Model\Query\ContextInterface;
use Magento\Quote\Model\Cart\AddProductsToCart as AddProductsToCartService;
use Magento\Quote\Model\Cart\Data\CartItemFactory;
use Magento\Quote\Model\QuoteMutexInterface;
use Magento\QuoteGraphQl\Model\Cart\GetCartForUser;
use Magento\Quote\Model\Cart\Data\Error;
use Magento\QuoteGraphQl\Model\CartItem\PrecursorInterface;

class AddProductsToCart implements ResolverInterface
{
    private $getCartForUser;
    private $addProductsToCartService;
    private $quoteMutex;
    private $cartItemPrecursor;

    public function __construct(
        GetCartForUser $getCartForUser,
        AddProductsToCartService $addProductsToCart,
        QuoteMutexInterface $quoteMutex,
        PrecursorInterface $cartItemPrecursor = null
    ) {
        $this->getCartForUser = $getCartForUser;
        $this->addProductsToCartService = $addProductsToCart;
        $this->quoteMutex = $quoteMutex;
        $this->cartItemPrecursor = $cartItemPrecursor ?: ObjectManager::getInstance()->get(PrecursorInterface::class);
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (empty($args['cartId'])) {
            throw new GraphQlInputException(__('Required parameter "cartId" is missing'));
        }
        if (empty($args['cartItems']) || !is_array($args['cartItems'])) {
            throw new GraphQlInputException(__('Required parameter "cartItems" is missing'));
        }

        return $this->quoteMutex->execute(
            [$args['cartId']],
            \Closure::fromCallable([$this, 'run']),
            [$context, $args]
        );
    }

    private function run($context, ?array $args): array
    {
        $maskedCartId = $args['cartId'];
        $cartItemsData = $args['cartItems'];
        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();

        $this->getCartForUser->execute($maskedCartId, $context->getUserId(), $storeId);

        // Inject preorder data into each cart item
        
        if (!empty($args['cartItems'])) {
            foreach ($cartItemsData as &$item) {
                if(isset($item['preorder']))
                {
                    $item['is_preorder'] = true;
                    $item['pre_order_payment_type'] = $item['preorder']['pre_order_payment_type'] ?? null;
                    $item['pre_order_deposit'] = $item['preorder']['pre_order_deposit'] ?? null;
                    unset ($item['preorder']);
                }                
            }
            
            // foreach ($cartItemsData as &$item) {
            //     if (!empty($args['preorder'])) {
            //         $item['data']['extension_attributes']['is_preorder'] = true;
            //         $item['data']['extension_attributes']['pre_order_payment_type'] =
            //             $args['preorder']['pre_order_payment_type'] ?? null;
            //         $item['data']['extension_attributes']['pre_order_deposit'] =
            //             $args['preorder']['pre_order_deposit'] ?? null;
            //     }
            // }
        }
        
        $cartItemsData = $this->cartItemPrecursor->process($cartItemsData, $context);
        $cartItems = [];

        foreach ($cartItemsData as $cartItemData) {
            $cartItem = (new CartItemFactory())->create($cartItemData);
            // dd(get_class_methods($cartItem));
            $cartItems[] = $cartItem;            
        }
        
        $registry = \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Registry::class);

        $preorderMap = [];

        foreach ($args['cartItems'] as $item) {
            // if(isset($item['preorder']))
            // {
            //     $item['is_preorder'] = true;
            //     $item['pre_order_payment_type'] = $item['preorder']['pre_order_payment_type'] ?? null;
            //     $item['pre_order_deposit'] = $item['preorder']['pre_order_deposit'] ?? null;
            //     unset ($item['preorder']);
            // }
            if (!empty($item['preorder'])) {
                $sku = $item['sku'];
                $preorderMap[$sku] = [
                    'is_preorder' => true,
                    'pre_order_payment_type' => $item['preorder']['pre_order_payment_type'] ?? null,
                    'pre_order_deposit' => $item['preorder']['pre_order_deposit'] ?? null,
                ];
            }
        }

        $registry->register('preorder_data', $preorderMap, true);
        $preorderData = $registry->registry('preorder_data');
        // dd($preorderData);

        $addProductsToCartOutput = $this->addProductsToCartService->execute($maskedCartId, $cartItems);

        // === Collect totals after adding items ===

        // 1. Convert masked cart id → real quote id
        $maskedIdModel = \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Quote\Model\QuoteIdMaskFactory::class)
            ->create()
            ->load($maskedCartId, 'masked_id');

        $quoteId = (int)$maskedIdModel->getQuoteId();

        // 2. Load the quote model
        $cartRepository = \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Quote\Api\CartRepositoryInterface::class);
        $quote = $cartRepository->get($quoteId);

        // 3. Recalculate totals
        $quote->collectTotals();
        $cartRepository->save($quote);

        return [
            'cart' => [
                // 'model' => $addProductsToCartOutput->getCart(),
                'model' => $quote,
            ],
            'user_errors' => array_map(
                function (Error $error) {
                    return [
                        'code' => $error->getCode(),
                        'message' => $error->getMessage(),
                        'path' => [$error->getCartItemPosition()]
                    ];
                },
                array_merge($addProductsToCartOutput->getErrors(), $this->cartItemPrecursor->getErrors())
            )
        ];
    }
}
