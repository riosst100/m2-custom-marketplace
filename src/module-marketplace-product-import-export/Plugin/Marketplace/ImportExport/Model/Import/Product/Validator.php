<?php

namespace TCGCollective\MarketplaceProductImportExport\Plugin\Marketplace\ImportExport\Model\Import\Product;

use Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface;
use Magento\Framework\Validator\AbstractValidator;
use Magento\Catalog\Model\Product\Attribute\Backend\Sku;

class Validator extends AbstractValidator implements RowValidatorInterface 
{
    /**
     * Filter chain const
     */
    private const FILTER_CHAIN = "php://filter";

    /**
     * @var RowValidatorInterface[]|AbstractValidator[]
     */
    protected $validators = [];

    /**
     * @var \Magento\CatalogImportExport\Model\Import\Product
     */
    protected $context;

    /**
     * @var \Magento\Framework\Stdlib\StringUtils
     */
    protected $string;

    /**
     * @var array
     */
    protected $_uniqueAttributes;

    /**
     * @var array
     */
    protected $_rowData;

    /**
     * @var string|null
     * @since 100.1.0
     */
    protected $invalidAttribute;
    protected $mappingHelper;

    /**
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param \TCGCollective\ProductAttributesLink\Helper\Data $mappingHelper
     * @param RowValidatorInterface[] $validators
     */
    public function __construct(
        \Magento\Framework\Stdlib\StringUtils $string,
        \TCGCollective\ProductAttributesLink\Helper\Data $mappingHelper,
        $validators = []
    ) {
        $this->string = $string;
        $this->validators = $validators;
        $this->mappingHelper = $mappingHelper;
    }

    /**
     * Check if value is valid attribute option
     *
     * @param string $attrCode
     * @param array $possibleOptions
     * @param string $value
     * @return bool
     */
    private function validateOption($attrCode, $possibleOptions, $value)
    {
        if (!isset($possibleOptions[strtolower($value)])) {
            dd('OKE');
            if ($this->mappingHelper->validateImportProductOption($attrCode, $value)) {
                return true;
            }
            
            $this->_addMessages(
                [
                    sprintf(
                        $this->context->retrieveMessageTemplate(
                            RowValidatorInterface::ERROR_INVALID_ATTRIBUTE_OPTION
                        ),
                        $attrCode
                    )
                ]
            );
            return false;
        }
        return true;
    }

    public function aroundIsAttributeValid(\Magento\CatalogImportExport\Model\Import\Product\Validator $subject, \Closure $proceed, $attrCode, array $attrParams, array $rowData)
    {
        $this->_rowData = $rowData;
        if (isset($rowData['product_type']) && !empty($attrParams['apply_to'])
            && !in_array($rowData['product_type'], $attrParams['apply_to'])
        ) {
            return true;
        }

        if ($rowData[$attrCode] === null || trim($rowData[$attrCode]) === '') {
            return true;
        }

        $valid = false;
        if ($attrParams['type'] == 'boolean') {
            $valid = $this->validateOption($attrCode, $attrParams['options'], $rowData[$attrCode]);

            if (!$valid) {
                $this->setInvalidAttribute($attrCode);
            }

            return (bool)$valid;
        }
        
        // // 🔹 kalau attribute type = boolean, jalankan custom validateOption manual
        // if (isset($attrParams['type']) && $attrParams['type'] === 'boolean') {
        //     // karena validateOption private, kamu gak bisa akses langsung.
        //     // ada 2 opsi:
        //     // 1. return custom true/false sesuai kebutuhan
        //     // 2. atau bikin override (ubah ke protected/public) kalau butuh panggilnya
        //     return in_array(strtolower($rowData[$attrCode]), ['0','1','yes','no']) ? true : false;
        //     return (bool)$valid;
        // }

        // 🔹 selain boolean, jalankan method asli
        return $proceed($attrCode, $attrParams, $rowData);
    }

    /**
     * Set invalid attribute
     *
     * @param string|null $attribute
     * @return void
     * @since 100.1.0
     */
    protected function setInvalidAttribute($attribute)
    {
        $this->invalidAttribute = $attribute;
    }

    /**
     * Numeric validation
     *
     * @param mixed $attrCode
     * @param string $type
     * @return bool
     */
    protected function numericValidation($attrCode, $type)
    {
        $val = trim($this->_rowData[$attrCode] ?? '');
        if ($type == 'int') {
            $valid = (string)(int)$val === $val;
        } else {
            $valid = is_numeric($val);
        }
        if (!$valid) {
            $this->_addMessages(
                [
                    sprintf(
                        $this->context->retrieveMessageTemplate(RowValidatorInterface::ERROR_INVALID_ATTRIBUTE_TYPE),
                        $attrCode,
                        $type
                    )
                ]
            );
        }
        return $valid;
    }

    /**
     * Text validation
     *
     * @param mixed $attrCode
     * @param string $type
     * @return bool
     */
    protected function textValidation($attrCode, $type)
    {
        $val = $this->string->cleanString($this->_rowData[$attrCode]);
        if (stripos($val, self::FILTER_CHAIN) !== false) {
            $this->_addMessages([RowValidatorInterface::ERROR_INVALID_ATTRIBUTE_TYPE]);
            return false;
        }
        if ($type == 'text') {
            $valid = $this->string->strlen($val) < Product::DB_MAX_TEXT_LENGTH;
        } elseif ($attrCode == Product::COL_SKU) {
            $valid = $this->string->strlen($val) <= SKU::SKU_MAX_LENGTH;
            if ($this->string->strlen($val) !== $this->string->strlen(trim($val))) {
                $this->_addMessages([RowValidatorInterface::ERROR_SKU_MARGINAL_WHITESPACES]);
                return false;
            }
        } else {
            $valid = $this->string->strlen($val) < Product::DB_MAX_VARCHAR_LENGTH;
        }
        if (!$valid) {
            $this->_addMessages([RowValidatorInterface::ERROR_EXCEEDED_MAX_LENGTH]);
        }
        return $valid;
    }

    /**
     * @inheritdoc
     */
    public function isValid($value)
    {
        $this->_rowData = $value;
        $this->_clearMessages();
        $returnValue = $this->isValidAttributes();
        foreach ($this->validators as $validator) {
            if (!$validator->isValid($value)) {
                $returnValue = false;
                $this->_addMessages($validator->getMessages());
            }
        }
        return $returnValue;
    }

    /**
     * Is valid attributes
     *
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function isValidAttributes()
    {
        $this->_clearMessages();
        $this->setInvalidAttribute(null);
        if (!isset($this->_rowData['product_type'])) {
            return false;
        }
        $entityTypeModel = $this->context->retrieveProductTypeByName($this->_rowData['product_type']);
        if ($entityTypeModel) {
            foreach ($this->_rowData as $attrCode => $attrValue) {
                $attrParams = $entityTypeModel->retrieveAttributeFromCache($attrCode);
                if ($attrCode === Product::COL_CATEGORY && $attrValue) {
                    $this->isCategoriesValid($attrValue);
                } elseif ($attrParams) {
                    $this->isAttributeValid($attrCode, $attrParams, $this->_rowData);
                }
            }
            if ($this->getMessages()) {
                return false;
            }
        }
        return true;
    }

    /**
     * Validate category names
     *
     * @param string $value
     * @return bool
     */
    private function isCategoriesValid(string $value) : bool
    {
        $result = true;
        if ($value) {
            $values = explode($this->context->getMultipleValueSeparator(), $value);
            foreach ($values as $categoryName) {
                if ($result === true) {
                    $result = $this->string->strlen($categoryName) < Product::DB_MAX_VARCHAR_LENGTH;
                }
            }
        }
        if ($result === false) {
            $this->_addMessages([RowValidatorInterface::ERROR_EXCEEDED_MAX_LENGTH]);
            $this->setInvalidAttribute(Product::COL_CATEGORY);
        }
        return $result;
    }

    /**
     * Init
     *
     * @param \Magento\CatalogImportExport\Model\Import\Product $context
     * @return $this
     */
    public function init($context)
    {
        $this->context = $context;
        foreach ($this->validators as $validator) {
            $validator->init($context);
        }

        return $this;
    }
}