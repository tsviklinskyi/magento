<?php
class TSG_CallCenter_Model_Observer_Sales_Order
{
    /**
     * Save primary initiator if null before saving order
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function savePrimaryInitiator(Varien_Event_Observer $observer)
    {
        $order = $observer->getOrder();
        if ($order->getPrimaryInitiatorId() === null && $order->getInitiatorId() !== null) {
            $order->setPrimaryInitiatorId($order->getInitiatorId());
        }
        return $this;
    }
}