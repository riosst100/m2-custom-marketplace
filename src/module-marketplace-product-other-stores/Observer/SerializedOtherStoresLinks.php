<?php

namespace TCGCollective\MarketplaceProductOtherStores\Observer;
 
use Magento\Framework\Event\Observer;
use Magento\Catalog\Model\Product;
use Magento\Framework\Event\ObserverInterface;
 
class SerializedOtherStoresLinks implements ObserverInterface
{
    const ATTR_LINK_TO_OTHER_STORES_CODE = 'link_to_other_stores';
 
    /**
     * @var  \Magento\Framework\App\RequestInterface
     */
    protected $request;
 
    /**
     * Constructor
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request
    )
    {
        $this->request = $request;
    }
 
    public function execute(Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        $this->processOtherStoresLinksData($product);
    }

    /**
     * Process stock item data
     *
     * Synchronize stock data from different sources (stock_data, quantity_and_stock_status, StockItem) and set it to
     * stock_data key
     *
     * @param Product $product
     * @return void
     */
    private function processOtherStoresLinksData(Product $product)
    {
        // $post = $this->request->getPost();
        // $post = $post['product'];

        // $otherStoresLinks = isset($post[self::ATTR_LINK_TO_OTHER_STORES_CODE]) ? $post[self::ATTR_LINK_TO_OTHER_STORES_CODE] : '';
        // if ($otherStoresLinks) {
        //     $requiredParams = ['stores','product_link'];

        //     $otherStoresLinks = $this->removeEmptyArray($otherStoresLinks, $requiredParams);
        //     $this->setOtherStoresLinksDataToProduct($product, $otherStoresLinks);
        // }
    }

    /**
     * Set stock data to product
     *
     * First of all we take stock_data data, replace it from quantity_and_stock_status data (if was changed) and finally
     * replace it with data from Stock Item object (only if Stock Item was changed)
     *
     * @param Product $product
     * @param array $quantityAndStockStatus
     * @return void
     */
    private function setOtherStoresLinksDataToProduct(Product $product, array $otherStoresLinks)
    {
        $product->setData('link_to_other_stores', json_encode($otherStoresLinks));
    }
 
    /**
    * Function to remove empty array from the multi dimensional array
    *
    * @return Array
    */
    private function removeEmptyArray($attractionData, $requiredParams){
 
        $requiredParams = array_combine($requiredParams, $requiredParams);
        $reqCount = count($requiredParams);
 
        foreach ($attractionData as $key => $values) {
            $values = array_filter($values);
            $inersectCount = count(array_intersect_key($values, $requiredParams));
            if ($reqCount != $inersectCount) {
                unset($attractionData[$key]);
            }  
        }
        return $attractionData;
    }
}