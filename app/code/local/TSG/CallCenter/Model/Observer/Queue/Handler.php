<?php
class TSG_CallCenter_Model_Observer_Queue_Handler
{
    /**
     * @var array $queueData
     */
    private $queueData = [];

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

        $queueData = $this->generateDataByQueue($collectionQueue, $ordersCollection);
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
     * @param TSG_CallCenter_Model_Resource_Queue_Collection $collectionQueue
     * @param Mage_Sales_Model_Resource_Order_Collection $ordersCollection
     * @return array
     */
    public function generateDataByQueue(TSG_CallCenter_Model_Resource_Queue_Collection $collectionQueue, Mage_Sales_Model_Resource_Order_Collection $ordersCollection): array
    {
        $this->queueData = [];
        $allMatchedOrderIds = [];
        foreach ($collectionQueue as $itemQueue) {
            $userMatchedIds = [];
            $userMatchedEmails = [];
            foreach ($ordersCollection as $order) {
                $orderMatch = $this->isOrderMatch($order, $itemQueue);
                if ($orderMatch && !in_array($order->getId(), $allMatchedOrderIds)) { // if order is match and not distributed yet
                    $userMatchedIds[] = $order->getId();
                    $allMatchedOrderIds[] = $order->getId();
                    if (!in_array($order->getCustomerEmail(), $userMatchedEmails)) {
                        $userMatchedEmails[] = $order->getCustomerEmail();
                    }
                }
            }
            $matchedOrderIdsByEmails = $this->checkByEmails($itemQueue, $ordersCollection, $userMatchedEmails);
            if (!empty($matchedOrderIdsByEmails)) {
                $userMatchedIds = array_unique(array_merge($userMatchedIds, $matchedOrderIdsByEmails));
            }
            if (!empty($userMatchedIds)) {
                $this->queueData[$itemQueue->getUserId()] = $userMatchedIds;
            }
        }
        return $this->queueData;
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
        $ordersCollection->addFieldToFilter('initiator_id', array('null' => true));
        return $ordersCollection;
    }

    /**
     * Check another orders by founded emails
     *
     * @param TSG_CallCenter_Model_Queue $itemQueue
     * @param Mage_Sales_Model_Resource_Order_Collection $ordersCollection
     * @param array $matchedEmails
     * @return array
     */
    private function checkByEmails(TSG_CallCenter_Model_Queue $itemQueue, Mage_Sales_Model_Resource_Order_Collection $ordersCollection, array $matchedEmails): array
    {
        $matchedOrderIds = [];
        if (empty($matchedEmails)) {
            return $matchedOrderIds;
        }
        foreach ($ordersCollection as $order) {
            if (!in_array($order->getCustomerEmail(), $matchedEmails)) {
                continue;
            }
            $orderMatch = $this->isOrderMatch($order, $itemQueue);
            if ($orderMatch) {
                $matchedOrderIds[] = $order->getId();
            }
        }
        return $matchedOrderIds;
    }

    /**
     * Check order is match by user criteria
     *
     * @param Mage_Sales_Model_Order $order
     * @param TSG_CallCenter_Model_Queue $itemQueue
     * @return bool
     */
    private function isOrderMatch(Mage_Sales_Model_Order $order, TSG_CallCenter_Model_Queue $itemQueue): bool
    {
        $orderMatch = false;
        switch ($itemQueue->getOrdersType()){
            case 1:
                $orderIsMatchByTimeRange = $this->checkOrderIsMatchByTimeRange($order->getCreatedAt(), 20, 8);
                break;
            case 2:
                $orderIsMatchByTimeRange = $this->checkOrderIsMatchByTimeRange($order->getCreatedAt(), 8, 20);
                break;
            default:
                $orderIsMatchByTimeRange = false;
                break;
        }
        if ($orderIsMatchByTimeRange === false) {
            return false;
        }
        $productsCriteria = $this->generateProductsCriteria($itemQueue->getProductsType());
        foreach ($order->getAllItems() as $orderItem) {
            $customProductType = Mage::getModel('catalog/product')->load($orderItem->getProductId())->getAttributeText('custom_product_type');
            //$customProductType = Mage::getResourceModel('catalog/product')->getAttributeRawValue($orderItem->getProductId(), 'custom_product_type', $order->getStoreId());
            if (true === $productsCriteria[$customProductType] && count($productsCriteria) === 1) {
                $orderMatch = true;
                break;
            }else{
                if (false === $productsCriteria[$customProductType]) {
                    continue; // if find one 'false' go to check next order
                }
                if (true === $productsCriteria[$customProductType]) {
                    $orderMatch = true;
                }
            }
        }
        return $orderMatch;
    }

    /**
     * Check if order creation time is match by hours range
     *
     * @param string $orderCreatedAt
     * @param int $from
     * @param int $to
     * @return bool
     */
    private function checkOrderIsMatchByTimeRange(string $orderCreatedAt, int $from, int $to): bool
    {
        $n = $to;
        if ($from > $to){
            $n = $from + 23;
        }
        for($i = $from; $i < $n; $i++){
            if($i === 24){
                $i = 0;
                $n = $to;
            }
            $like = $i;
            if(strlen($like) === 1){
                $like = '0' . $like;
            }
            $check = strpos($orderCreatedAt, ' ' . $like . ':');
            if ($check !== false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Generate criteria for collection by products
     *
     * @param $productsType
     * @return array
     */
    private function generateProductsCriteria(string $productsType): array
    {
        $criteria = [];
        /* @var TSG_CallCenter_Model_Queue $callcenterQueue */
        $callcenterQueue = Mage::getModel('callcenter/queue');
        switch ($productsType){
            case '1':
                $criteria = array(
                    $callcenterQueue->getProductTypes()['1'] => true
                );
                break;
            case '2':
                $criteria = array(
                    $callcenterQueue->getProductTypes()['1'] => false,
                    $callcenterQueue->getProductTypes()['2'] => true
                );
                break;
            case '3':
                $criteria = array(
                    $callcenterQueue->getProductTypes()['1'] => false,
                    $callcenterQueue->getProductTypes()['2'] => false,
                    $callcenterQueue->getProductTypes()['3'] => true
                );
                break;
            case '0':
                $criteria = array(
                    $callcenterQueue->getProductTypes()['1'] => false,
                    $callcenterQueue->getProductTypes()['2'] => false,
                    $callcenterQueue->getProductTypes()['3'] => false,
                    $callcenterQueue->getProductTypes()['0'] => true
                );
                break;
            default:
                // do nothing
                break;
        }
        return $criteria;
    }
}