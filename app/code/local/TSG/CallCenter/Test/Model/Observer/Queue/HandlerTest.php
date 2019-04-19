<?php
class TSG_Callcenter_Test_Model_Observer_Queue_HandlerTest extends PHPUnit_Framework_TestCase
{
    /** @var TSG_CallCenter_Model_Observer_Queue_Handler $handler */
    private $handler;

    /** @var TSG_CallCenter_Model_Queue $modelQueue */
    private $modelQueue;

    /** @var Mage_Sales_Model_Order $modelOrder */
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
        $this->modelQueue = Mage::getModel('callcenter/queue');
        $this->modelOrder = Mage::getModel('sales/order');
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

    private function getCollectionQueueData(): TSG_CallCenter_Model_Resource_Queue_Collection
    {
        $collection = $this->modelQueue->getCollection();
        $collection->addItem($this->modelQueue->setQueueId(200)->setUserId(9)->setProductsType(1)->setOrdersType(0));
        return $collection;
    }

    private function getCollectionOrderData(): Mage_Sales_Model_Resource_Order_Collection
    {
        return $this->modelOrder->getCollection();
    }

    private function getResultQueueData()
    {
        return array(
            '9' => array('195', '196')
        );
    }
}