<?php
class TSG_CallCenter_Model_Observer_Sales_Order_View_Postdispatch
{
    /**
     * Save initiator to order on order view page if allowed by role
     */
    public function saveInitiatorToOrder()
    {
        $orderId = Mage::app()->getRequest()->getParam('order_id');
        $initiatorId = Mage::getSingleton('admin/session')->getUser()->getId();
        /* @var TSG_CallCenter_Model_Queue $callcenterQueue */
        $callcenterQueue = Mage::getModel('callcenter/queue');
        if ($callcenterQueue->isAllowedByRole()) {
            $callcenterQueue->saveInitiatorToOrders($initiatorId, array($orderId));
        }
    }
}