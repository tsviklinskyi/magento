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

    public function getAllowedRoleNames()
    {
        return $this->_allowedRoleNames;
    }

    public function getProductTypes()
    {
        return $this->_productTypes;
    }

    public function getOrderTypes()
    {
        return $this->_orderTypes;
    }

    public function isAllowedByRole($roleType = 1)
    {
        $allowed = false;
        if (in_array(Mage::getSingleton('admin/session')->getUser()->getRole()->getRoleName(), $this->_allowedRoleNames[$roleType])) {
            $allowed = true;
        }
        return $allowed;
    }

    public function getCountByUser()
    {
        return $this->getCollection()->addFieldToFilter('user_id', Mage::getSingleton('admin/session')->getUser()->getId())->count();
    }

    public function getCountOrdersByUser(array $statuses = array('pending'))
    {
        $orders = Mage::getModel('sales/order')->getCollection()
            ->addFieldToFilter('status', array('in' => $statuses))
            ->addFieldToFilter('initiator_id', Mage::getSingleton('admin/session')->getUser()->getId());
        return $orders->count();
    }
}