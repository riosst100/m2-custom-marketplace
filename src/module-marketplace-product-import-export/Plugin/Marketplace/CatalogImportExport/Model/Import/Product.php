<?php

namespace TCGCollective\MarketplaceProductImportExport\Plugin\Marketplace\CatalogImportExport\Model\Import;

class Product
{
    protected $mappingHelper;
    protected $request;


    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Lof\MarketPlace\Model\SellerProduct
     */
    protected $sellerproduct;

    /**
     * @var \Lof\MarketPlace\Helper\Data
     */
    protected $helper;

    /**
     * Product constructor.
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Lof\MarketPlace\Helper\Data $helper
     * @param \Lof\MarketPlace\Model\SellerProduct $sellerproduct
     */
    public function __construct(
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Lof\MarketPlace\Helper\Data $helper,
        \Lof\MarketPlace\Model\SellerProduct $sellerproduct
    ) {
        $this->_productFactory = $productFactory;
        $this->helper = $helper;
        $this->sellerproduct = $sellerproduct;
    }

    /**
     * @param \Magento\CatalogImportExport\Model\Import\Product $subject
     * @param $rowData
     * @param $rowNum
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
    */
    public function beforeValidateRow(
        \Magento\CatalogImportExport\Model\Import\Product $subject,
        $rowData,
        $rowNum
    ) {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->mappingHelper = $objectManager->create(\TCGCollective\ProductAttributesLink\Helper\Data::class);
        $this->request = $objectManager->get('\Magento\Framework\App\RequestInterface');

        $sellerId = $this->helper->getSellerId();
        // $product = $this->_productFactory->create()->loadByAttribute('sku', $rowData['sku']);
        
        // if ($sellerId && $product && $product->getData() && $product->getId() && $product->getSellerId()) {
        //     if ($sellerId != $product->getSellerId()) {
        //         return [false]; // Menghentikan eksekusi method asli
        //     }
        // }
        $paramData = $this->request->getParams();
        if ($paramData) {
            $unusedParams = [
                "form_key",
                "entity",
                "template_url",
                "behavior",
                "validation_strategy",
                "allowed_error_count",
                "_import_field_separator",
                "_import_multiple_value_separator",
                "import_images_file_dir"
            ];

            $categoryId = null;
            
            foreach($paramData as $field => $data) {
                if (!in_array($field, $unusedParams)) {
                    if ($field == "_attribute_set") {
                        $rowData['_attribute_set'] = $data;
                    } elseif ($field == "categories") {
                        $categoryId = $data;
                        $category = $this->mappingHelper->getCategoryById($data);
                        if ($category) {
                            $categoryPathIds = $category->getPathIds();
                            if ($categoryPathIds) {
                                $categoryPathName = $this->mappingHelper->getCategoryPathLabelByIds($categoryPathIds);
                                if ($categoryPathName) {
                                    $rowData[$field] = $categoryPathName;
                                }
                            }
                        }
                    } else {
                        $label = $data ? $this->mappingHelper->getOptionLabelById($field, $data) : null;
                        if ($label) {
                            $rowData[$field] = $label;
                        }
                    }
                }
            }
        }

        $rowData['product_type'] = 'simple';
        $rowData['visibility'] = 'Catalog, Search';
        $rowData['seller_id'] = $sellerId;
        $rowData['approval'] = 'Approved';

        $customTableMetadata = $this->mappingHelper->getCustomTableMetadata($categoryId);
        $mainAttrCode = $customTableMetadata['mainAttrCode'];

        $mainAttrLabel = $rowData[$mainAttrCode] ?? '';

        $prefix = $this->mappingHelper->getPrefixByCategoryId($categoryId, $mainAttrLabel);

        $mainSetsAttrCode = $prefix . $customTableMetadata['mainSetsAttrCode'];
        $mainSetsNumberAttrCode = $prefix . $customTableMetadata['mainSetsNumberAttrCode'];
        $mainSinglesAttrCode = $prefix . $customTableMetadata['mainSinglesAttrCode'];
        $mainSinglesNumberAttrCode = $prefix . $customTableMetadata['mainSinglesNumberAttrCode'];

        $productIdentifierArr = [];

        // CARD NAME
        $cardName = $rowData[$mainSinglesAttrCode] ?? null;
        if ($cardName) {
            $cardIdentifierArr = [];
            $cardNumber = $rowData[$mainSinglesNumberAttrCode] ?? null;
            if ($cardNumber) {
                $cardIdentifierArr[] = strtoupper($this->convertToSlug($cardNumber));
                $cardName = $cardName . ' ' . '#' . $cardNumber;
            }

            $productIdentifierArr[] = implode("-", $cardIdentifierArr);
        }

        // SETS NAME
        $setsName = $rowData[$mainSetsAttrCode] ?? null;
        if ($setsName) {
            $setsIdentifierArr = [];
            $setAbbrv = $rowData[$mainSetsNumberAttrCode] ?? null;
            if ($setAbbrv) {
                $setsIdentifierArr[] = strtoupper($this->convertToSlug($setAbbrv));
                $setsName = $setsName . ' (' . $setAbbrv . ')';
            }

            $productIdentifierArr[] = implode("-", $setsIdentifierArr);
        }

        // SET PRODUCT NAME

        $productIdentifierArr[] = strtoupper($this->makeid(10));

        $productIdentifier = implode("-", $productIdentifierArr);
        if ($productIdentifier) {
            // $rowData['sku'] = $productIdentifier;
        }
        // unset($productData['_attribute_set']);
        // unset($productData['product_type']);
        // unset($productData['url_key']);
        // unset($productData['news_from_date']);
        // unset($productData['news_to_date']);
        // unset($productData['product_rma']);
        // unset($productData['lof_preorder']);
        // unset($productData['preorder_availability']);
        // unset($productData['preorder_msg']);
        // unset($productData['cod_available']);
        // unset($productData['categories']);
        // unset($productData['weight_type']);
        // unset($productData['link_to_other_stores']);

        // dd($rowData);

        return [$rowData, $rowNum];
    }

    public function makeId($length) {
        $result = '';
        $characters = 'abcdefghijklmnopqrstuvwxyz0123456789';
        $charactersLength = strlen($characters);
        for ($i = 0; $i < $length; $i++) {
            $result .= $characters[rand(0, $charactersLength - 1)];
        }
        return $result;
    }    

    public function convertToSlug($text) {
        return strtolower(
            preg_replace(
                ['/[^a-zA-Z0-9 -]/', '/[ -]+/', '/^-|-$/'],
                ['', '-', ''],
                trim($text)
            )
        );
    }
}
