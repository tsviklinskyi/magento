<?php
class TSG_CallCenter_Model_Queue extends Mage_Core_Model_Abstract
{
    public const ORDERS_TYPE_NIGHT = '1';
    public const ORDERS_TYPE_DAY = '2';
    public const ORDERS_TYPE_NOT_SPECIFIED = '0';

    public const ORDERS_TYPE_DAY_TIME_FROM = '8:10';
    public const ORDERS_TYPE_DAY_TIME_TO = '20:55';

    public const PRODUCTS_TYPE_LARGE_DEVICES = '1';
    public const PRODUCTS_TYPE_SMALL_DEVICES = '2';
    public const PRODUCTS_TYPE_GADGETS = '3';
    public const PRODUCTS_TYPE_NOT_SPECIFIED = '0';

    private const ORDER_TYPES = array(
        self::ORDERS_TYPE_NIGHT => 'Ночные - (с 20.00 до 08.00)',
        self::ORDERS_TYPE_DAY => 'Дневные - (с 08.00 до 20.00)',
        self::ORDERS_TYPE_NOT_SPECIFIED => 'Не указан'
    );

    private const PRODUCT_TYPES = array(
        self::PRODUCTS_TYPE_LARGE_DEVICES => 'КБТ',
        self::PRODUCTS_TYPE_SMALL_DEVICES => 'МБТ',
        self::PRODUCTS_TYPE_GADGETS => 'Гаджеты',
        self::PRODUCTS_TYPE_NOT_SPECIFIED => 'Не указан'
    );

    protected function _construct()
    {
        $this->_init('callcenter/queue');
    }

    /**
     * @return array
     */
    public function getProductTypes()
    {
        return self::PRODUCT_TYPES;
    }

    /**
     * @return array
     */
    public function getOrderTypes()
    {
        return self::ORDER_TYPES;
    }

    /**
     * Check is user in queue
     *
     * @return bool
     */
    public function isUserInQueue(): bool
    {
        $result = false;
        $limit = 1;
        /* @var Mage_Admin_Model_User $adminUser */
        $adminUser = Mage::getSingleton('admin/session')->getUser();
        $collection = $this->getCollection()
            ->addFieldToFilter('user_id', $adminUser->getId());
        if (count($collection->getAllIds($limit)) > 0) {
            $result = true;
        }
        return $result;
    }

    /**
     * Check count orders in database by current user filtered by order statuses
     *
     * @return bool
     */
    public function userHaveOrders(): bool
    {
        $result = false;
        $statuses = array('pending');
        $limit = 1;
        $orders = Mage::getModel('sales/order')->getCollection()
            ->addFieldToFilter('status', array('in' => $statuses))
            ->addFieldToFilter('initiator_id', Mage::getSingleton('admin/session')->getUser()->getId());
        if (count($orders->getAllIds($limit)) > 0) {
            $result = true;
        }
        return $result;
    }

    /**
     * Saving initiator to orders list by order ids
     *
     * @param $initiatorId
     * @param $orderIds
     */
    public function saveInitiatorToOrders(int $initiatorId, array $orderIds): void
    {
        if (empty($orderIds)) {
            return;
        }
        /* @var Mage_Sales_Model_Order $modelOrder */
        $modelOrder = Mage::getModel('sales/order');
        $ordersCollection = $modelOrder->getCollection();
        $ordersCollection->addFieldToFilter('entity_id', array('in' => $orderIds));
        foreach ($ordersCollection as $order) {
            $order->setInitiatorId($initiatorId)->save();
        }
    }
}