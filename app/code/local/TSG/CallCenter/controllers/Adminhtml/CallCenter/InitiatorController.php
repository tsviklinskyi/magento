<?php
class TSG_CallCenter_Adminhtml_CallCenter_InitiatorController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Save item to waiting queue
     */
    public function setInitiatorAction()
    {
        $model = Mage::getModel('callcenter/queue');
        $model->setUserId(Mage::getSingleton('admin/session')->getUser()->getId())->save();
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
            $modelOrder = Mage::getModel('sales/order');
            $modelOrderGrid = Mage::getModel('sales/order_grid');
            $orders = $modelOrder->getCollection()
                ->addFieldToFilter('entity_id', array('in' => $orderIds));
            $orderGridItems = $modelOrderGrid->getCollection()
                ->addFieldToFilter('entity_id', array('in' => $orderIds));
            $orders->setDataToAll('initiator_id', null)->save();
            $orderGridItems->setDataToAll('initiator_id', null)->save();
        }
        $this->_redirectReferer();
    }
}