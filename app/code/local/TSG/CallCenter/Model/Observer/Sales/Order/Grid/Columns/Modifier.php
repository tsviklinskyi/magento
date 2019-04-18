<?php
class TSG_CallCenter_Model_Observer_Sales_Order_Grid_Columns_Modifier
{
    /**
     * Update collection before load, join tables and add filters
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function addInitiatorToCollection(Varien_Event_Observer $observer)
    {
        $collection = $observer->getOrderGridCollection();
        $select = $collection->getSelect();
        $select->joinLeft(
            array('au' => 'admin_user'),
            'au.user_id = main_table.initiator_id',
            array(
                'initiator_name' => 'CONCAT(au.firstname, " ", au.lastname)'
            )
        );
        $select->joinLeft(
            array('au2' => 'admin_user'),
            'au2.user_id = main_table.primary_initiator_id',
            array(
                'primary_initiator_name' => 'CONCAT(au2.firstname, " ", au2.lastname)'
            )
        );
        $select->group('main_table.entity_id');

        $this->_filterCollectionByRole($collection);
        return $this;
    }

    /**
     * Add user role filter to collection
     *
     * @param $collection
     * @return $this
     */
    private function _filterCollectionByRole(Mage_Sales_Model_Resource_Order_Grid_Collection $collection)
    {
        /* @var TSG_CallCenter_Model_Queue $callcenterQueue */
        $callcenterQueue = Mage::getModel('callcenter/queue');
        if ($callcenterQueue->isAllowedByRole()) {
            $collection->addAttributeToFilter('initiator_id', Mage::getSingleton('admin/session')->getUser()->getUserId());
        }elseif ($callcenterQueue->isAllowedByRole(2)) {
            $collection->addAttributeToFilter('initiator_id', array('notnull' => true));
        }
        return $this;
    }
}