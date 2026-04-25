<?php

namespace TCGCollective\MarketPlace\Model\ResourceModel;

class HistoryOverwrite extends \Magento\ImportExport\Model\ResourceModel\History
{
    /**
     * Define main table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('import_history', 'history_id');
    }

    /**
     * Retrieve last inserted report id by user id
     *
     * @param string $userId
     * @return int $lastId
     */
    public function getLastInsertedId($userId, $sellerId = null)
    {
        $connection = $this->getConnection();
        $select = $connection
            ->select()
            ->from($this->getMainTable())
            ->order($this->getIdFieldName() . ' DESC')
            ->where('user_id = ?', $userId)
            ->limit(1);

        if ($sellerId) {
            $select->where('seller_id = ?', $sellerId);
        }

        return $connection->fetchOne($select);
    }

    /**
     * Retrieve last inserted report id by user id
     *
     * @param string $sellerId
     * @return int $lastId
     */
    public function getLastInsertedIdBySellerId($sellerId)
    {
        $connection = $this->getConnection();
        $select = $connection
            ->select()
            ->from($this->getMainTable())
            ->order($this->getIdFieldName() . ' DESC')
            ->where('seller_id = ?', $sellerId)
            ->limit(1);

        return $connection->fetchOne($select);
    }
}
