<?php
class TSG_CallCenter_Model_Observer
{
    /**
     * Update collection before load, join tables and add filters
     * @param $observer
     * @return $this
     */
    public function salesOrderGridCollectionLoadBefore($observer)
    {
        $collection = $observer->getOrderGridCollection();
        $select = $collection->getSelect();
        $select->joinLeft(
            array('sfu' => 'sales_flat_order'),
            'sfu.entity_id = main_table.entity_id',
            array('customer_email', 'initiator_id', 'primary_initiator_id')
        );
        $select->joinLeft(
            array('au' => 'admin_user'),
            'au.user_id = sfu.initiator_id',
            array(
                'initiator_name' => 'CONCAT(au.firstname, " ", au.lastname)'
            )
        );
        $select->joinLeft(
            array('au2' => 'admin_user'),
            'au2.user_id = sfu.primary_initiator_id',
            array(
                'primary_initiator_name' => 'CONCAT(au2.firstname, " ", au2.lastname)'
            )
        );
        $select->group('sfu.entity_id');

        $this->_filterCollectionByRole($collection);
        return $this;
    }

    /**
     * Add user role filter to collection
     * @param $collection
     */
    private function _filterCollectionByRole($collection)
    {
        $modelQueue = Mage::getModel('callcenter/queue');
        if ($modelQueue->isAllowedByRole()) {
            $collection->addAttributeToFilter('sfu.initiator_id', Mage::getSingleton('admin/session')->getUser()->getUserId());
        }elseif ($modelQueue->isAllowedByRole(2)) {
            $collection->addAttributeToFilter('sfu.initiator_id', array('notnull' => true));
        }
    }

    /**
     * Adding new buttons to grid and order view page
     * @param $observer
     * @return $this
     */
    public function addNewButtons($observer)
    {
        $container = $observer->getBlock();
        $modelQueue = Mage::getModel('callcenter/queue');
        if(null !== $container && $container->getType() == 'adminhtml/sales_order' && $modelQueue->isAllowedByRole() && $modelQueue->getCountOrdersByUser() == 0) {
            if (Mage::getModel('callcenter/queue')->getCountByUser()) {
                $data = array(
                    'label'     => 'Waiting order',
                    'class'     => 'disabled reload-page-5',
                );
            }else{
                $data = array(
                    'label'     => 'Get order',
                    'class'     => '',
                    'onclick'   => 'setLocation(\''  . Mage::helper('adminhtml')->getUrl('adminhtml/sales_order/setInitiator') . '\')'
                );
            }
            $container->addButton('get-order', $data);
        }

        if(null !== $container && $container->getType() == 'adminhtml/sales_order_view' && $modelQueue->isAllowedByRole(2)) {
            $order = Mage::registry('current_order');
            $data = array(
                'label'     => 'Clear Initiator',
                'class'     => '',
                'onclick'   => 'setLocation(\''  . Mage::helper('adminhtml')->getUrl('adminhtml/sales_order/clearInitiator', array('order_id' => $order->getId())) . '\')'
            );
            $container->addButton('clear-initiator', $data);
        }

        return $this;
    }

    /**
     * Distribution queue of waiting users, save relations users with orders and clear queue
     */
    public function queueDistribution()
    {
        Mage::log('TSG CallCenter queueDistribution was run at ' . date('Y-m-d H:i:s'), null, 'tsg_callcenter_queue.log', true);
        $modelQueue = Mage::getModel('callcenter/queue');
        $collectionQueue = $modelQueue->getCollection()->setOrder('request_date', 'ASC');
        foreach ($collectionQueue as $itemQueue){
            $userData = Mage::getModel('admin/user')->load($itemQueue->getUserId())->getData();
            $productsCriteria = Mage::helper('callcenter')->generateProductsCriteria($userData['products_type']);
            $modelOrder = Mage::getModel('sales/order');
            $ordersCollection = $modelOrder->getCollection();
            $ordersCollection->addFieldToFilter('initiator_id', array('null' => true));
            //$ordersCollection->addFieldToFilter('entity_id', array('eq' => 195));
            switch ($userData['orders_type']){
                case 1:
                    $ordersCollection->addFieldToFilter('created_at', Mage::helper('callcenter')->getTimeRangeArray(20,8));
                    break;
                case 2:
                    $ordersCollection->addFieldToFilter('created_at', Mage::helper('callcenter')->getTimeRangeArray(8,20));
                    break;
                default:
                    // do nothing
                    break;
            }
            $ordersCollection2 = clone $ordersCollection;
            $matchedEmails = Mage::helper('callcenter')->checkCollectionAndSaveRelations($ordersCollection, $productsCriteria, $itemQueue->getUserId(), $itemQueue->getId());
            if (!empty($matchedEmails)) {
                $ordersCollection2->addFieldToFilter('customer_email', array('in' => $matchedEmails));
                Mage::helper('callcenter')->checkCollectionAndSaveRelations($ordersCollection2, $productsCriteria, $itemQueue->getUserId(), $itemQueue->getId());
            }
        }
        Mage::log('TSG CallCenter queueDistribution finished at ' . date('Y-m-d H:i:s'), null, 'tsg_callcenter_queue.log', true);
    }
}