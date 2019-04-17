<?php
class TSG_CallCenter_Model_Queue extends Mage_Core_Model_Abstract
{
    protected $_allowedRoleNames = array(
        1 => array('CallCenterSpecialist'),
        2 => array('CallCenterCoordinator')
    );

    protected $_orderTypes = array(
        '1' => 'Ночные - (с 20.00 до 08.00)',
        '2' => 'Дневные - (с 08.00 до 20.00)',
        '0' => 'Не указан'
    );

    protected $_productTypes = array(
        '1' => 'КБТ',
        '2' => 'МБТ',
        '3' => 'Гаджеты',
        '0' => 'Не указан'
    );

    protected function _construct()
    {
        $this->_init('callcenter/queue');
    }

    /**
     * @return array
     */
    public function getAllowedRoleNames()
    {
        return $this->_allowedRoleNames;
    }

    /**
     * @return array
     */
    public function getProductTypes()
    {
        return $this->_productTypes;
    }

    /**
     * @return array
     */
    public function getOrderTypes()
    {
        return $this->_orderTypes;
    }

    /**
     * Check if user is in list of allowed roles
     * @param int $roleType
     * @return bool
     */
    public function isAllowedByRole($roleType = 1)
    {
        $allowed = false;
        if (in_array(Mage::getSingleton('admin/session')->getUser()->getRole()->getRoleName(), $this->_allowedRoleNames[$roleType])) {
            $allowed = true;
        }
        return $allowed;
    }

    /**
     * Get count rows in queue by current user
     * @return mixed
     */
    public function getCountByUser()
    {
        return $this->getCollection()->addFieldToFilter('user_id', Mage::getSingleton('admin/session')->getUser()->getId())->count();
    }

    /**
     * Get count orders in database by current user and filter by order statuses
     * @param array $statuses
     * @return mixed
     */
    public function getCountOrdersByUser(array $statuses = array('pending'))
    {
        $orders = Mage::getModel('sales/order')->getCollection()
            ->addFieldToFilter('status', array('in' => $statuses))
            ->addFieldToFilter('initiator_id', Mage::getSingleton('admin/session')->getUser()->getId());
        return $orders->count();
    }

    /**
     * @param $initiatorId
     * @param $orderIds
     * @param $queueIdToClear
     * @throws Exception
     */
    public function saveInitiatorToOrders($initiatorId, array $orderIds, $queueIdToClear = null, $checkAllowByCurrentUser = false)
    {
        if (empty($orderIds) || ($checkAllowByCurrentUser && !Mage::getModel('callcenter/queue')->isAllowedByRole())) {
            return;
        }
        $modelOrder = Mage::getModel('sales/order');
        $ordersCollection = $modelOrder->getCollection();
        $ordersCollection->addFieldToFilter('entity_id', array('in' => $orderIds));
        foreach ($ordersCollection as $order) {
            $orderGridItem = Mage::getModel('sales/order_grid')->load($order->getId());
            $order->setInitiatorId($initiatorId);
            $orderGridItem->setInitiatorId($initiatorId);
            if(null === $order->getPrimaryInitiatorId()){
                $order->setPrimaryInitiatorId($initiatorId);
                $orderGridItem->setPrimaryInitiatorId($initiatorId);
            }
            $order->save();
            $orderGridItem->save();
        }
        if (null !== $queueIdToClear) {
            $model = Mage::getModel('callcenter/queue');
            try {
                $model->setId($queueIdToClear)->delete();
            } catch (Exception $e){
                echo $e->getMessage();
            }
        }
    }
}