<?php
class TSG_CallCenter_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * @var array $_orderIds
     */
    private $_orderIds = array();

    /**
     * @var array $queueData
     */
    private $_queueData = array();

    /**
     * Generate data of relations users with orders and clear queue
     *
     * @param $collectionQueue
     * @return array
     * @throws Exception
     */
    public function generateDataByQueue(TSG_CallCenter_Model_Resource_Queue_Collection $collectionQueue) : array
    {
        $this->_queueData = array();
        foreach ($collectionQueue as $itemQueue){
            $this->_orderIds = array();
            $userData = Mage::getModel('admin/user')->load($itemQueue->getUserId())->getData();
            $productsCriteria = Mage::helper('callcenter')->generateProductsCriteria($userData['products_type']);
            /* @var Mage_Sales_Model_Order $modelOrder */
            $modelOrder = Mage::getModel('sales/order');
            $ordersCollection = $modelOrder->getCollection();
            $ordersCollection->addFieldToFilter('initiator_id', array('null' => true));
            //$ordersCollection->addFieldToFilter('entity_id', array('eq' => 195));
            switch ($userData['orders_type']){
                case 1:
                    $ordersCollection->addFieldToFilter('created_at', Mage::helper('callcenter')->timeRangeArray(20,8));
                    break;
                case 2:
                    $ordersCollection->addFieldToFilter('created_at', Mage::helper('callcenter')->timeRangeArray(8,20));
                    break;
                default:
                    // do nothing
                    break;
            }
            $ordersCollection2 = clone $ordersCollection;
            $matchedEmails = $this->checkCollection($ordersCollection, $productsCriteria);
            if (!empty($matchedEmails)) {
                $ordersCollection2->addFieldToFilter('customer_email', array('in' => $matchedEmails))
                    ->addFieldToFilter('entity_id', array('nin' => $this->_orderIds));
                $this->checkCollection($ordersCollection2, $productsCriteria);
            }
            if (!empty($this->_orderIds)) {
                $this->_queueData[$itemQueue->getUserId()] = $this->_orderIds;
                /* @var TSG_CallCenter_Model_Queue $callcenterQueue */
                $callcenterQueue = Mage::getModel('callcenter/queue');
                $callcenterQueue->setId($itemQueue->getId())->delete();
            }
        }
        return $this->_queueData;
    }

    /**
     * Generate array of filters by hours range
     *
     * @param $from
     * @param $to
     * @return array
     */
    public function timeRangeArray(int $from, int $to) : array
    {
        $arr = array();
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
            $arr[] = array('like' => '% '.$like.':%');
        }
        return $arr;
    }

    /**
     * Generate criteria for collection by products
     *
     * @param $productsType
     * @return array
     */
    public function generateProductsCriteria(string $productsType) : array
    {
        $criteria = array();
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

    /**
     * Collection processing and push matched order id to list of ids
     *
     * @param $ordersCollection
     * @param $productsCriteria
     * @return array
     */
    public function checkCollection($ordersCollection, array $productsCriteria)
    {
        $matchedEmails = array();
        foreach ($ordersCollection as $order) {
            $orderMatch = false;
            foreach ($order->getAllItems() as $orderItem) {
                $customProductType = Mage::getModel('catalog/product')->load($orderItem->getProductId())->getAttributeText('custom_product_type');
                //$customProductType = Mage::getResourceModel('catalog/product')->getAttributeRawValue($orderItem->getProductId(), 'custom_product_type', $order->getStoreId());
                if (true === $productsCriteria[$customProductType] && count($productsCriteria) === 1) {
                    $orderMatch = true;
                    break;
                }else{
                    if (false === $productsCriteria[$customProductType]) {
                        continue 2; // if find one 'false' go to check next order
                    }
                    if (true === $productsCriteria[$customProductType]) {
                        $orderMatch = true;
                    }
                }
            }
            if ($orderMatch) {
                $this->_orderIds[] = $order->getId();
                $matchedEmails[] = $order->getCustomerEmail();
            }
        }
        return $matchedEmails;
    }
}