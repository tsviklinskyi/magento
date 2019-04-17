<?php
class TSG_CallCenter_Model_Observer_Queue_Handler
{
    /**
     * Distribution queue of waiting users, save relations users with orders and clear queue
     */
    public function queueDistribution()
    {
        Mage::log('TSG CallCenter queueDistribution was run at ' . date('Y-m-d H:i:s'), null, 'tsg_callcenter_queue.log', true);
        $modelQueue = Mage::getModel('callcenter/queue');
        $collectionQueue = $modelQueue->getCollection()->setOrder('request_date', 'ASC');
        Mage::helper('callcenter')->queueDistribution($collectionQueue);
        Mage::log('TSG CallCenter queueDistribution finished at ' . date('Y-m-d H:i:s'), null, 'tsg_callcenter_queue.log', true);
    }
}