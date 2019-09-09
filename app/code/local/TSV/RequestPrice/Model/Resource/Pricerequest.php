<?php

/**
 * Price Request resource model
 *
 * @category    TSV
 * @package     TSV_RequestPrice
 * @author      TSV
 */
class TSV_RequestPrice_Model_Resource_Pricerequest extends Mage_Core_Model_Resource_Db_Abstract
{

    /**
     * constructor
     *
     * @access public
     * @author TSV
     */
    public function _construct()
    {
        $this->_init('tsv_requestprice/pricerequest', 'entity_id');
    }

    /**
     * Get store ids to which specified item is assigned
     *
     * @access public
     * @param int $pricerequestId
     * @return array
     * @author TSV
     */
    public function lookupStoreIds($pricerequestId)
    {
        $adapter = $this->_getReadAdapter();
        $select  = $adapter->select()
            ->from($this->getTable('tsv_requestprice/pricerequest_store'), 'store_id')
            ->where('pricerequest_id = ?', (int)$pricerequestId);
        return $adapter->fetchCol($select);
    }

    /**
     * Perform operations after object load
     *
     * @access public
     * @param Mage_Core_Model_Abstract $object
     * @return TSV_RequestPrice_Model_Resource_Pricerequest
     * @author TSV
     */
    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        if ($object->getId()) {
            $stores = $this->lookupStoreIds($object->getId());
            $object->setData('store_id', $stores);
        }
        return parent::_afterLoad($object);
    }

    /**
     * Retrieve select object for load object data
     *
     * @param string $field
     * @param mixed $value
     * @param TSV_RequestPrice_Model_Pricerequest $object
     * @return Zend_Db_Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object);
        if ($object->getStoreId()) {
            $storeIds = array(Mage_Core_Model_App::ADMIN_STORE_ID, (int)$object->getStoreId());
            $select->join(
                array('requestprice_pricerequest_store' => $this->getTable('tsv_requestprice/pricerequest_store')),
                $this->getMainTable() . '.entity_id = requestprice_pricerequest_store.pricerequest_id',
                array()
            )
            ->where('requestprice_pricerequest_store.store_id IN (?)', $storeIds)
            ->order('requestprice_pricerequest_store.store_id DESC')
            ->limit(1);
        }
        return $select;
    }

    /**
     * Assign price request to store views
     *
     * @access protected
     * @param Mage_Core_Model_Abstract $object
     * @return TSV_RequestPrice_Model_Resource_Pricerequest
     * @author TSV
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        $oldStores = $this->lookupStoreIds($object->getId());
        $newStores = (array)$object->getStores();
        if (empty($newStores)) {
            $newStores = (array)$object->getStoreId();
        }
        $table  = $this->getTable('tsv_requestprice/pricerequest_store');
        $insert = array_diff($newStores, $oldStores);
        $delete = array_diff($oldStores, $newStores);
        if ($delete) {
            $where = array(
                'pricerequest_id = ?' => (int) $object->getId(),
                'store_id IN (?)' => $delete
            );
            $this->_getWriteAdapter()->delete($table, $where);
        }
        if ($insert) {
            $data = array();
            foreach ($insert as $storeId) {
                $data[] = array(
                    'pricerequest_id'  => (int) $object->getId(),
                    'store_id' => (int) $storeId
                );
            }
            $this->_getWriteAdapter()->insertMultiple($table, $data);
        }
        return parent::_afterSave($object);
    }
}
