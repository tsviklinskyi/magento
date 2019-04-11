<?php
class TSG_CallCenter_Model_Observer
{
    public function salesOrderGridCollectionLoadBefore($observer)
    {
        $collection = $observer->getOrderGridCollection();
        $select = $collection->getSelect();
        $select->joinLeft(
            array('sales_flat_order'),
            'sales_flat_order.entity_id = main_table.entity_id',
            array('customer_email', 'initiator_id', 'primary_initiator_id')
        );
        $select->joinLeft(
            array('admin_user'),
            'admin_user.user_id = sales_flat_order.initiator_id',
            array(
                'initiator_name' => 'CONCAT(admin_user.firstname, " ", admin_user.lastname)',
                'primary_initiator_name' => 'CONCAT(admin_user.firstname, " ", admin_user.lastname)'
            )
        );

        $this->_filterCollectionByRole($collection);
    }

    private function _filterCollectionByRole($collection)
    {
        $modelQueue = Mage::getModel('callcenter/queue');
        if ($modelQueue->isAllowedByRole()) {
            $collection->addAttributeToFilter('sales_flat_order.initiator_id', Mage::getSingleton('admin/session')->getUser()->getUserId());
        }elseif ($modelQueue->isAllowedByRole(2)) {
            $collection->addAttributeToFilter('sales_flat_order.initiator_id', array('notnull' => true));
        }
    }

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

    public function queueDistribution()
    {
        Mage::log('TSG CallCenter queueDistribution was run at ' . date('Y-m-d H:i:s'), null, 'tsg_callcenter_queue.log', true);
        $modelQueue = Mage::getModel('callcenter/queue');
        $collectionQueue = $modelQueue->getCollection()->setOrder('request_date', 'ASC');
        foreach ($collectionQueue as $itemQueue){
            $userData = Mage::getModel('admin/user')->load($itemQueue->getUserId())->getData();
            $flags = $this->getLabels($userData['products_type']);
            $modelOrder = Mage::getModel('sales/order');
            $ordersCollection = $modelOrder->getCollection();
            $ordersCollection->addFieldToFilter('initiator_id', array('null' => true));
            switch ($userData['orders_type']){
                case 1:
                    $ordersCollection->addFieldToFilter('created_at', $this->getTimeRangeArray(20,8));
                    break;
                case 2:
                    $ordersCollection->addFieldToFilter('created_at', $this->getTimeRangeArray(8,20));
                    break;
                default:
                    // do nothing
                    break;
            }
            $ordersCollection2 = clone $ordersCollection;
            $matchedEmails = $this->checkCollectionAndSaveRelations($ordersCollection, $flags, $itemQueue->getUserId());
            if (!empty($matchedEmails)) {
                $ordersCollection2->addFieldToFilter('customer_email', array('in' => $matchedEmails));
                $this->checkCollectionAndSaveRelations($ordersCollection2, $flags, $itemQueue->getUserId());
            }
        }
        Mage::log('TSG CallCenter queueDistribution finished at ' . date('Y-m-d H:i:s'), null, 'tsg_callcenter_queue.log', true);
    }

    protected function getTimeRangeArray($from, $to)
    {
        $arr = array();
        $n = $to;
        if ($from > $to){
            $n = $from + 23;
        }
        for($i = $from; $i < $n; $i++){
            if($i == 24){
                $i = 0;
                $n = $to;
            }
            $like = $i;
            if(strlen($like) == 1){
                $like = '0' . $like;
            }
            $arr[] = array('like' => '% '.$like.':%');
        }
        return $arr;
    }

    protected function getLabels($productsType)
    {
        $flags = array();
        switch ($productsType){
            case '1':
                $flags = array(
                    Mage::getModel('callcenter/queue')->getProductTypes()['1'] => true
                );
                break;
            case '2':
                $flags = array(
                    Mage::getModel('callcenter/queue')->getProductTypes()['1'] => false,
                    Mage::getModel('callcenter/queue')->getProductTypes()['2'] => true
                );
                break;
            case '3':
                $flags = array(
                    Mage::getModel('callcenter/queue')->getProductTypes()['1'] => false,
                    Mage::getModel('callcenter/queue')->getProductTypes()['2'] => false,
                    Mage::getModel('callcenter/queue')->getProductTypes()['3'] => true
                );
                break;
            case '0':
                $flags = array(
                    Mage::getModel('callcenter/queue')->getProductTypes()['1'] => false,
                    Mage::getModel('callcenter/queue')->getProductTypes()['2'] => false,
                    Mage::getModel('callcenter/queue')->getProductTypes()['3'] => false,
                    Mage::getModel('callcenter/queue')->getProductTypes()['0'] => true
                );
                break;
            default:
                // do nothing
                break;
        }
        return $flags;
    }

    protected function checkCollectionAndSaveRelations($ordersCollection, $flags, $initiatorId)
    {
        $matchedEmails = array();
        foreach ($ordersCollection as $order) {
            $orderMatch = false;
            foreach ($order->getAllItems() as $orderItem) {
                $customProductType = Mage::getModel('catalog/product')->load($orderItem->getProductId())->getAttributeText('custom_product_type');
                //$customProductType = Mage::getResourceModel('catalog/product')->getAttributeRawValue($orderItem->getProductId(), 'custom_product_type', $order->getStoreId());
                if (false === $flags[$customProductType]) {
                    continue 2; // if find one 'false' go to check next order
                }
                if (true === $flags[$customProductType]) {
                    $orderMatch = true;
                }
            }
            if ($orderMatch) {
                $matchedEmails[] = $order->getCustomerEmail();
                $order->setInitiatorId($initiatorId);
                if(null === $order->getPrimaryInitiatorId()){
                    $order->setPrimaryInitiatorId($initiatorId);
                }
                //$order->save();
            }
        }
        return $matchedEmails;
    }
}