<?php

namespace TCGCollective\MarketplaceProductImportExport\Model\ResourceModel\Import;

/**
 * ImportExport import data resource model
 *
 * @api
 * @since 100.0.2
 */
class Data extends \Magento\ImportExport\Model\ResourceModel\Import\Data
{
    protected $helper;
    
    /**
     * Set Flag for Imported Rows
     *
     * @param array $ids
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function markProcessedBunches(array $ids): void
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/import.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        // $logger->info('markProcessedBunches IDs: ');
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->helper = $objectManager->create(\Lof\MarketPlace\Helper\Data::class);
        $this->session = $objectManager->create(\Magento\Framework\Session\SessionManager::class);

        $sellerId = $this->helper->getSellerId();
        if (empty($sellerId)) {
            $sellerId = $this->session->getData('seller_id');
            $this->session->unsetData('seller_id');
        }
        // $logger->info('markProcessedBunches Seller ID: ' . $sellerId);
        $where = $sellerId ? ['seller_id = (?)' => $sellerId] : '';
        
        // $where = $ids ? ['id IN (?)' => $ids] : '';

        // if ($sellerWhere) {
        //     if ($where) {
        //         $where .= ' AND ' . $sellerWhere;
        //     } else {
        //         $where = $sellerWhere;
        //     }
        // }

        $this->getConnection()->update(
            $this->getMainTable(),
            ['is_processed' => '1'],
            $where
        );
    }

    /**
     * Save import rows bunch.
     *
     * @param string $entity
     * @param string $behavior
     * @param array $data
     * @return int
     */
    public function saveBunch($entity, $behavior, array $data)
    {
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/import.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);        
        // $logger->info('saveBunch Data: ' . print_r($data, true));
        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->helper = $objectManager->create(\Lof\MarketPlace\Helper\Data::class);
        $this->session = $objectManager->create(\Magento\Framework\Session\SessionManager::class);

        $dataSellerId= $data;
        $first = reset($dataSellerId); // Get the first element of the array
        if (is_array($first) && isset($first['seller_id'])) {
            $sellerId = $first['seller_id'];
            // $logger->info('saveBunch Seller ID reset: ' . $sellerId);
        } elseif (isset($data[0]['seller_id'])) {
            $sellerId = $data[0]['seller_id'];
        } else {
            $sellerId = $this->helper->getSellerId();
        }
        // $logger->info('saveBunch Seller ID: ' . $sellerId);
        $this->session->setData('seller_id', $sellerId);        
        $encodedData = $this->jsonHelper->jsonEncode($data);

        if (json_last_error()!==JSON_ERROR_NONE && empty($encodedData)) {
            throw new \Magento\Framework\Exception\ValidatorException(
                __('Error in CSV: ' . json_last_error_msg())
            );
        }

        $this->getConnection()->insert(
            $this->getMainTable(),
            ['behavior' => $behavior, 'entity' => $entity, 'data' => $encodedData, 'is_processed' => '0', 'seller_id' => $sellerId]
        );
        
        return $this->getConnection()->lastInsertId($this->getMainTable());
    }

    public function getIteratorForCustomQuery($ids)
    {        
        $writer = new \Zend_Log_Writer_Stream(BP . '/var/log/import.log');
        $logger = new \Zend_Log();
        $logger->addWriter($writer);
        // $logger->info('getIteratorForCustomQuery');
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->helper = $objectManager->create(\Lof\MarketPlace\Helper\Data::class);
        $this->session = $objectManager->create(\Magento\Framework\Session\SessionManager::class);

        $sellerId = $this->helper->getSellerId();
        if (empty($sellerId)) {
            $sellerId = $this->session->getData('seller_id');
        }
        // $logger->info('getIteratorForCustomQuery Seller ID: ' . $sellerId);
        
        $connection = $this->getConnection();

        $select = $connection->select()
        ->from($this->getMainTable(), ['id','updated_at'])->where('is_processed=0 AND seller_id = '.$sellerId)->order('id DESC');
        $rowData = $connection->fetchRow($select);

        $latestDataId = $rowData['id'];
        $latestDataUpdatedAt = $rowData['updated_at'];
        if ($latestDataId) {
            $connection->delete(
                $this->getMainTable(),
                sprintf(
                    'updated_at < DATE_SUB("%s", INTERVAL 10 SECOND) AND id != %d AND is_processed = 0 AND seller_id = %d',
                    $latestDataUpdatedAt,
                    $latestDataId,
                    $sellerId
                )
            );
        }

        $select = $connection->select()
            ->from($this->getMainTable(), ['data'])->order('id DESC');
        if ($ids) {
            $select = $select->where('id IN (?)', $ids);
        }

        if ($sellerId) {
            $select = $select->where('seller_id = (?)', $sellerId);
        }
        
        $stmt = $connection->query($select);

        $stmt->setFetchMode(\Zend_Db::FETCH_NUM);
        if ($stmt instanceof \IteratorAggregate) {
            $iterator = $stmt->getIterator();
        } else {
            // Statement doesn't support iterating, so fetch all records and create iterator ourself
            $rows = $stmt->fetchAll();
            $iterator = new \ArrayIterator($rows);
        }

        return $iterator;
    }

    /**
     * Get next unique bunch of validated rows.
     *
     * @param array|null $ids
     * @return array|null
     */
    public function getNextUniqueBunch($ids = null)
    {
        if (null === $this->_iterator) {
            $this->_iterator = $this->getIteratorForCustomQuery($ids);
            // dd(get_class($this->_iterator));
            $this->_iterator->rewind();
        }
        $dataRow = null;
        if ($this->_iterator->valid()) {
            $encodedData = $this->_iterator->current();
            if (!empty($encodedData[0])) {
                $dataRow = $this->jsonHelper->jsonDecode($encodedData[0]);
                $this->_iterator->next();
            }
        }
        if (!$dataRow) {
            $this->_iterator = null;
        }
        return $dataRow;
    }
}
