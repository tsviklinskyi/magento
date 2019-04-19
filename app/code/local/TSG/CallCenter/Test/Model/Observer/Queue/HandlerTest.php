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
     * @param $itemsQueue
     * @param $itemsOrder
     * @param $resultData
     *
     * @dataProvider provider
     * @throws Exception
     */
    public function testGenerateDataByQueue($itemsQueue, $itemsOrder, $resultData)
    {
        $collectionQueue = $this->getCollectionQueueData($itemsQueue);
        $collectionOrder = $this->getCollectionOrderData($itemsOrder);
        $queueData = $this->handler->generateDataByQueue($collectionQueue, $collectionOrder);
        $this->assertEquals($resultData, $queueData);
    }

    public function provider()
    {
        return [
            'one user one matched order' => [
                [
                    [
                        'queue_id' => 1,
                        'user_id' => 9,
                        'products_type' => 1,
                        'orders_type' => 0
                    ]
                ],
                [
                    [
                        'id' => 100,
                        'customer_email' => 'test@example.com',
                        'created_at' => '2013-04-04 03:34:48',
                        'ordered_items' => [
                            ['custom_product_type' => 'КБТ']
                        ]
                    ]
                ],
                [9 => [100]]
            ],
            'one user two matched orders' => [
                [
                    [
                        'queue_id' => 2,
                        'user_id' => 8,
                        'products_type' => 1,
                        'orders_type' => 0
                    ]
                ],
                [
                    [
                        'id' => 100,
                        'customer_email' => 'test@example.com',
                        'created_at' => '2013-04-04 03:34:48',
                        'ordered_items' => [
                            ['custom_product_type' => 'КБТ']
                        ]
                    ],
                    [
                        'id' => 200,
                        'customer_email' => 'test2@example.com',
                        'created_at' => '2013-04-04 03:34:48',
                        'ordered_items' => [
                            ['custom_product_type' => 'КБТ'],
                            ['custom_product_type' => 'МБТ']
                        ]
                    ]
                ],
                [8 => [100, 200]]
            ],
            'one user one not matched order' => [
                [
                    [
                        'queue_id' => 3,
                        'user_id' => 10,
                        'products_type' => 2,
                        'orders_type' => 0
                    ]
                ],
                [
                    [
                        'id' => 100,
                        'customer_email' => 'test@example.com',
                        'created_at' => '2013-04-04 03:34:48',
                        'ordered_items' => [
                            ['custom_product_type' => 'КБТ']
                        ]
                    ]
                ],
                []
            ],
        ];
    }

    /**
     * @param $itemsQueue
     * @return TSG_CallCenter_Model_Adapter_Queue_Collection
     * @throws Exception
     */
    private function getCollectionQueueData($itemsQueue): TSG_CallCenter_Model_Adapter_Queue_Collection
    {
        $collection = $this->modelQueue;

        foreach ($itemsQueue as $itemQueue) {
            $item = new Varien_Object();
            $item->setQueueId($itemQueue['queue_id']);
            $item->setUserId($itemQueue['user_id']);
            $item->setProductsType($itemQueue['products_type']);
            $item->setOrdersType($itemQueue['orders_type']);
            $collection->addItem($item);
        }

        return $collection;
    }

    /**
     * @param $itemsOrder
     * @return TSG_CallCenter_Model_Adapter_Order_Collection
     * @throws Exception
     */
    private function getCollectionOrderData($itemsOrder): TSG_CallCenter_Model_Adapter_Order_Collection
    {
        $collection = $this->modelOrder;

        foreach ($itemsOrder as $itemOrder) {
            $item = new Varien_Object();
            $item->setId($itemOrder['id']);
            $item->setCustomerEmail($itemOrder['customer_email']);
            $item->setCreatedAt($itemOrder['created_at']);

            $orderItems = [];
            foreach ($itemOrder['ordered_items'] as $orderedItem) {
                $orderItem = new Varien_Object();
                $orderItem->setCustomProductType($orderedItem['custom_product_type']);
                $orderItems[] = $orderItem;
                $item->setOrderedItems($orderItems);
            }
            $collection->addItem($item);
        }

        return $collection;
    }
}