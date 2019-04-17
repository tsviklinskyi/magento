<?php
class TSG_CallCenter_Adminhtml_CallCenter_InitiatorController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Save item to waiting queue
     */
    public function addToQueueAction()
    {
        /* @var TSG_CallCenter_Model_Queue $callcenterQueue */
        $callcenterQueue = Mage::getModel('callcenter/queue');
        $callcenterQueue->setUserId(Mage::getSingleton('admin/session')->getUser()->getId())->save();
        $this->_redirectReferer();
    }

    /**
     * Mass clear initiator of orders list
     */
    public function clearInitiatorAction()
    {
        $orderIds = $this->getRequest()->getParam('order_ids');
        if (empty($orderIds) && $orderId = $this->getRequest()->getParam('order_id')) {
            $orderIds = array($orderId);
        }
        if (!empty($orderIds)) {
            /* @var Mage_Sales_Model_Order $modelOrder */
            $modelOrder = Mage::getModel('sales/order');
            $orders = $modelOrder->getCollection()
                ->addFieldToFilter('entity_id', array('in' => $orderIds));
            $orders->setDataToAll('initiator_id', null)->save();
        }
        $this->_redirectReferer();
    }
}