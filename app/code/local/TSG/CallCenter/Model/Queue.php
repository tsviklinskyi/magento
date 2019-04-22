<?php
class TSG_CallCenter_Model_Queue extends Mage_Core_Model_Abstract
{
    public const ORDERS_TYPE_NIGHT = '1';
    public const ORDERS_TYPE_DAY = '2';
    public const ORDERS_TYPE_NOT_SPECIFIED = '0';

    public const PRODUCTS_TYPE_LARGE_DEVICES = '1';
    public const PRODUCTS_TYPE_SMALL_DEVICES = '2';
    public const PRODUCTS_TYPE_GADGETS = '3';
    public const PRODUCTS_TYPE_NOT_SPECIFIED = '0';

    private const SPECIALIST_ROLES_KEY = 1;
    private const COORDINATOR_ROLES_KEY = 2;

    private const ALLOWED_ROLE_NAMES = array(
        self::SPECIALIST_ROLES_KEY => array('CallCenterSpecialist'),
        self::COORDINATOR_ROLES_KEY => array('CallCenterCoordinator')
    );

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
    public function getAllowedRoleNames()
    {
        return self::ALLOWED_ROLE_NAMES;
    }

    /**
     * @return int
     */
    public function getSpecialistRolesKey()
    {
        return self::SPECIALIST_ROLES_KEY;
    }

    /**
     * @return int
     */
    public function getCoordinatorRolesKey()
    {
        return self::COORDINATOR_ROLES_KEY;
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
     * Check if user is in list of allowed roles
     *
     * @param int $roleType
     * @return bool
     */
    public function isAllowedByRole(int $roleType): bool
    {
        $allowed = false;
        /* @var Mage_Admin_Model_User $adminUser */
        $adminUser = Mage::getSingleton('admin/session')->getUser();
        if (in_array($adminUser->getRole()->getRoleName(), self::ALLOWED_ROLE_NAMES[$roleType])) {
            $allowed = true;
        }
        return $allowed;
    }

    /**
     * Get count rows in queue by current user
     *
     * @param int $limit
     * @return array
     */
    public function getCountByUser(int $limit = null): array
    {
        if ($limit === null) {
            $limit = 10;
        }
        /* @var Mage_Admin_Model_User $adminUser */
        $adminUser = Mage::getSingleton('admin/session')->getUser();
        $collection = $this->getCollection()
            ->addFieldToFilter('user_id', $adminUser->getId());
        return $collection->getAllIds($limit);
    }

    /**
     * Get count orders in database by current user filtered by order statuses
     *
     * @param int $limit
     * @return array
     */
    public function getCountOrdersByUser(int $limit = null): array
    {
        $statuses = array('pending');
        if ($limit === null) {
            $limit = 10;
        }
        $orders = Mage::getModel('sales/order')->getCollection()
            ->addFieldToFilter('status', array('in' => $statuses))
            ->addFieldToFilter('initiator_id', Mage::getSingleton('admin/session')->getUser()->getId());
        return $orders->getAllIds($limit);
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