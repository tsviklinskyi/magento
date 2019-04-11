<?php
require_once Mage::getModuleDir('controllers', 'Mage_Adminhtml').DS.'Sales'.DS.'OrderController.php';

class TSG_CallCenter_Adminhtml_Sales_OrderController extends Mage_Adminhtml_Sales_OrderController
{
    public function setInitiatorAction()
    {
        $model = Mage::getModel('callcenter/queue');
        $model->setUserId(Mage::getSingleton('admin/session')->getUser()->getId())->save();
        $this->_redirectReferer();
    }

    public function clearInitiatorAction()
    {
        $orderIds = $this->getRequest()->getParam('order_ids');
        if (empty($orderIds) && $orderId = $this->getRequest()->getParam('order_id')) {
            $orderIds = array($orderId);
        }
        if (!empty($orderIds)) {
            $modelOrder = Mage::getModel('sales/order');
            $orders = $modelOrder->getCollection()
                ->addFieldToFilter('entity_id', array('in' => $orderIds));
            $orders->setDataToAll('initiator_id', null)->save();
        }
        $this->_redirectReferer();
    }

    public function viewAction()
    {
        $this->_title($this->__('Sales'))->_title($this->__('Orders'));

        $order = $this->_initOrder();
        if ($order) {
            if (Mage::getModel('callcenter/queue')->isAllowedByRole()) {
                $userId = Mage::getSingleton('admin/session')->getUser()->getId();
                $order->setInitiatorId($userId);
                if(null === $order->getPrimaryInitiatorId()){
                    $order->setPrimaryInitiatorId($userId);
                }
                $order->save();
            }

            $isActionsNotPermitted = $order->getActionFlag(
                Mage_Sales_Model_Order::ACTION_FLAG_PRODUCTS_PERMISSION_DENIED
            );
            if ($isActionsNotPermitted) {
                $this->_getSession()->addError($this->__('You don\'t have permissions to manage this order because of one or more products are not permitted for your website.'));
            }

            $this->_initAction();

            $this->_title(sprintf("#%s", $order->getRealOrderId()));

            $this->renderLayout();
        }
    }
}