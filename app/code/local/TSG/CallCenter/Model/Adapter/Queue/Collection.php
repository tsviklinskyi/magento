<?php
class TSG_CallCenter_Model_Adapter_Queue_Collection extends Varien_Data_Collection
{
    /**
     * @param TSG_CallCenter_Model_Resource_Queue_Collection $queueCollection
     */
    public function adaptCollection(TSG_CallCenter_Model_Resource_Queue_Collection $queueCollection): void
    {
        $items = [];
        foreach ($queueCollection as $queueItem) {
            $item = $this->adaptQueue($queueItem);
            $items[] = $item;
        }
        $this->_items = $items;
    }

    /**
     * Adapt queue
     *
     * @param TSG_CallCenter_Model_Queue $queue
     * @return Varien_Object
     */
    protected function adaptQueue(TSG_CallCenter_Model_Queue $queue): Varien_Object
    {
        $result = new Varien_Object();

        $result->setQueueId($queue->getId());
        $result->setUserId($queue->getUserId());
        $result->setProductsType($queue->getProductsType());
        $result->setOrdersType($queue->getOrdersType());

        return $result;
    }
}