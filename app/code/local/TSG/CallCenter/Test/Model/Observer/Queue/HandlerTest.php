<?php
class TSG_Callcenter_Test_Model_Observer_Queue_HandlerTest extends PHPUnit_Framework_TestCase
{
    /** @var TSG_CallCenter_Model_Observer_Queue_Handler $handler */
    private $handler;

    /** @var TSG_CallCenter_Model_Adapter_Queue_Collection $modelQueue */
    private $modelQueue;

    /** @var TSG_CallCenter_Model_Adapter_Order_Collection $modelOrder */
    private $modelOrder;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        define('MAGENTO_ROOT', getcwd());
        $mageFilename = MAGENTO_ROOT . '/app/Mage.php';
        require MAGENTO_ROOT . '/app/bootstrap.php';
        require_once $mageFilename;
        Mage::init();
    }

    public function setUp()
    {
        $this->handler = Mage::getModel('callcenter/observer_queue_handler');
        $this->modelQueue = Mage::getModel('callcenter/adapter_queue_collection');
        $this->modelOrder = Mage::getModel('callcenter/adapter_order_collection');
        parent::setUp();
    }

    public function tearDown()
    {
        $this->handler = null;
        $this->modelQueue = null;
        $this->modelOrder = null;
        parent::tearDown();
    }

    /**
     * Test that method generateDataByQueue work as expected.
     *
     * @covers TSG_CallCenter_Model_Observer_Queue_Handler::generateDataByQueue
     */
    public function testGenerateDataByQueue()
    {
        $collectionQueue = $this->getCollectionQueueData();
        $collectionOrder = $this->getCollectionOrderData();
        $queueData = $this->handler->generateDataByQueue($collectionQueue, $collectionOrder);
        $this->assertEquals($this->getResultQueueData(), $queueData);
    }

    private function getCollectionQueueData(): TSG_CallCenter_Model_Adapter_Queue_Collection
    {
        $item = new Varien_Object();
        $item->setQueueId(1);
        $item->setUserId(9);
        $item->setProductsType(1);
        $item->setOrdersType(0);

        $collection = $this->modelQueue;
        $collection->addItem($item);
        return $collection;
    }

    private function getCollectionOrderData(): TSG_CallCenter_Model_Adapter_Order_Collection
    {
        $collection = $this->modelOrder;

        $item1 = new Varien_Object();
        $item1->setId(100);
        $item1->setCustomerEmail('test@example.com');
        $item1->setCreatedAt('2013-04-04 03:34:48');

        $orderItems1 = [];
        $resultOrderItem = new Varien_Object();
        $resultOrderItem->setCustomProductType('КБТ');
        $orderItems1[] = $resultOrderItem;

        $item1->setOrderedItems($orderItems1);

        $collection->addItem($item1);

        $item2 = new Varien_Object();
        $item2->setId(200);
        $item2->setCustomerEmail('test2@example.com');
        $item2->setCreatedAt('2013-04-04 03:34:48');

        $orderItems2 = [];
        $resultOrderItem = new Varien_Object();
        $resultOrderItem->setCustomProductType('КБТ');
        $orderItems2[] = $resultOrderItem;
        $resultOrderItem = new Varien_Object();
        $resultOrderItem->setCustomProductType('МБТ');
        $orderItems2[] = $resultOrderItem;

        $item2->setOrderedItems($orderItems2);

        $collection->addItem($item2);

        return $collection;
    }

    private function getResultQueueData()
    {
        return array(
            9 => array(100, 200)
        );
    }
}