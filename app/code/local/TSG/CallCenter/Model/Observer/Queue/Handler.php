<?php
class TSG_CallCenter_Model_Observer_Queue_Handler
{
    /**
     * Distribution queue of waiting users, save relations users with orders and clear queue
     */
    public function queueDistribution()
    {
        Mage::log('TSG CallCenter queueDistribution was run at ' . date('Y-m-d H:i:s'), null, 'tsg_callcenter_queue.log', true);
        /* @var TSG_CallCenter_Model_Queue $callcenterQueue */
        $callcenterQueue = Mage::getModel('callcenter/queue');

        $collectionQueue = $this->getQueueCollection();
        $ordersCollection = $this->getOrdersCollection();

        /* @var TSG_CallCenter_Model_Adapter_Queue_Collection $adaptedQueueCollection */
        $adaptedQueueCollection = Mage::getModel('callcenter/adapter_queue_collection');
        $adaptedQueueCollection->adaptCollection($collectionQueue);

        /* @var TSG_CallCenter_Model_Adapter_Order_Collection $adaptedOrdersCollection */
        $adaptedOrdersCollection = Mage::getModel('callcenter/adapter_order_collection');
        $adaptedOrdersCollection->adaptCollection($ordersCollection);

        $queueData = $this->generateDataByQueue($adaptedQueueCollection, $adaptedOrdersCollection);
        foreach ($queueData as $initiatorId => $orderIds){
            $callcenterQueue->saveInitiatorToOrders((int)$initiatorId, $orderIds);
        }
        // Clear queue
        foreach ($collectionQueue as $itemQueue) {
            /* @var TSG_CallCenter_Model_Queue $callcenterQueue */
            $callcenterQueue = Mage::getModel('callcenter/queue');
            $callcenterQueue->setId($itemQueue->getId())->delete();
        }
        Mage::log('TSG CallCenter queueDistribution finished at ' . date('Y-m-d H:i:s'), null, 'tsg_callcenter_queue.log', true);
    }

    /**
     * Generate data of relations users with orders
     *
     * @param TSG_CallCenter_Model_Adapter_Queue_Collection $collectionQueue
     * @param TSG_CallCenter_Model_Adapter_Order_Collection $ordersCollection
     * @return array
     */
    public function generateDataByQueue(TSG_CallCenter_Model_Adapter_Queue_Collection $collectionQueue, TSG_CallCenter_Model_Adapter_Order_Collection $ordersCollection): array
    {
        $userMatchedIds = [];

        foreach ($ordersCollection as $order) {
            $customKey = $this->generateCustomKey($order);
            $order->setCustomKey($customKey);
        }

        foreach ($collectionQueue as $itemQueue) {
            $userCustomKey = "product_type_{$itemQueue->getProductsType()}_order_type_{$itemQueue->getOrdersType()}";
            if ($itemQueue->getOrdersType() === TSG_CallCenter_Model_Queue::ORDERS_TYPE_NOT_SPECIFIED) {
                $userCustomKey = "product_type_{$itemQueue->getProductsType()}_order_type_";
                $matched = $ordersCollection->getItemByColumnValueLike('custom_key', $userCustomKey);
            }else{
                $matched = $ordersCollection->getItemByColumnValue('custom_key', $userCustomKey);
            }

            if ($matched !== null) {
                $userMatchedIds[$itemQueue->getUserId()] = [$matched->getId()];

                $ordersCollection->removeItemByKey($matched->getId());

                $matchedByEmail = $ordersCollection->getItemsByColumnValue('customer_email', $matched->getCustomerEmail());
                if (!empty($matchedByEmail)) {
                    foreach ($matchedByEmail as $item) {
                        $userMatchedIds[$itemQueue->getUserId()][] = $item->getId();
                        $ordersCollection->removeItemByKey($item->getId());
                    }
                }
            }
        }
        return $userMatchedIds;
    }

    /**
     * Get queue collection
     *
     * @return TSG_CallCenter_Model_Resource_Queue_Collection
     */
    private function getQueueCollection(): TSG_CallCenter_Model_Resource_Queue_Collection
    {
        /* @var TSG_CallCenter_Model_Queue $callcenterQueue */
        $callcenterQueue = Mage::getModel('callcenter/queue');
        $collectionQueue = $callcenterQueue->getCollection()->setOrder('request_date', 'ASC');
        $select = $collectionQueue->getSelect();
        $select->joinLeft(
            array('au' => 'admin_user'),
            'au.user_id = main_table.user_id',
            array(
                'products_type' => 'au.products_type',
                'orders_type' => 'au.orders_type'
            )
        );
        return $collectionQueue;
    }

    /**
     * Get collection of orders with default empty initiator
     *
     * @return Mage_Sales_Model_Resource_Order_Collection
     */
    private function getOrdersCollection(): Mage_Sales_Model_Resource_Order_Collection
    {
        /* @var Mage_Sales_Model_Order $modelOrder */
        $modelOrder = Mage::getModel('sales/order');
        $ordersCollection = $modelOrder->getCollection();
        $ordersCollection->addFieldToFilter('initiator_id', array('null' => true))
            ->setOrder('created_at', 'ASC');
        return $ordersCollection;
    }

    /**
     * Generating custom key of order
     *
     * @param Varien_Object $order
     * @return string
     */
    private function generateCustomKey(Varien_Object $order)
    {
        /* @var TSG_CallCenter_Model_Queue $callcenterQueue */
        $callcenterQueue = Mage::getModel('callcenter/queue');

        if ($this->checkIsDayOrder($order->getCreatedAt())) {
            $orderType = TSG_CallCenter_Model_Queue::ORDERS_TYPE_DAY;
        }else{
            $orderType = TSG_CallCenter_Model_Queue::ORDERS_TYPE_NIGHT;
        }

        $typeCounts = [];
        foreach ($order->getOrderedItems() as $orderItem) {
            if (!isset($typeCounts[$orderItem->getCustomProductType()])) {
                $typeCounts[$orderItem->getCustomProductType()] = 0;
            }
            $typeCounts[$orderItem->getCustomProductType()]++;
        }

        if ($typeCounts[$callcenterQueue->getProductTypes()[TSG_CallCenter_Model_Queue::PRODUCTS_TYPE_LARGE_DEVICES]] > 0) {
            $productType = TSG_CallCenter_Model_Queue::PRODUCTS_TYPE_LARGE_DEVICES;
        }elseif ($typeCounts[$callcenterQueue->getProductTypes()[TSG_CallCenter_Model_Queue::PRODUCTS_TYPE_SMALL_DEVICES]] > 0) {
            $productType = TSG_CallCenter_Model_Queue::PRODUCTS_TYPE_SMALL_DEVICES;
        }elseif ($typeCounts[$callcenterQueue->getProductTypes()[TSG_CallCenter_Model_Queue::PRODUCTS_TYPE_GADGETS]] > 0) {
            $productType = TSG_CallCenter_Model_Queue::PRODUCTS_TYPE_GADGETS;
        }else {
            $productType = TSG_CallCenter_Model_Queue::PRODUCTS_TYPE_NOT_SPECIFIED;
        }

        return "product_type_{$productType}_order_type_{$orderType}";
    }

    /**
     * Check if order creation time is match by day hours range
     *
     * @param string $orderCreatedAt
     * @return bool
     * @throws Exception
     */
    private function checkIsDayOrder(string $orderCreatedAt): bool
    {
        $timeFrom = explode(':', TSG_CallCenter_Model_Queue::ORDERS_TYPE_DAY_TIME_FROM);
        $timeTo = explode(':', TSG_CallCenter_Model_Queue::ORDERS_TYPE_DAY_TIME_TO);

        $orderDate = new DateTime($orderCreatedAt);

        $datetimeFrom = new DateTime($orderCreatedAt);
        $datetimeTo = new DateTime($orderCreatedAt);

        $datetimeFrom->setTime($timeFrom[0], $timeFrom[1]);
        $datetimeTo->setTime($timeTo[0], $timeTo[1]);

        return $datetimeFrom < $orderDate && $orderDate < $datetimeTo;
    }
}