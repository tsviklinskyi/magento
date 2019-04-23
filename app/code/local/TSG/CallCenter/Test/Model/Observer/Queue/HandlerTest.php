<?php
class TSG_Callcenter_Test_Model_Observer_Queue_HandlerTest extends PHPUnit_Framework_TestCase
{
    /** @var TSG_CallCenter_Model_Observer_Queue_Handler $handler */
    private $handler;
    
    /** @var TSG_CallCenter_Model_Queue $modelQueue */
    private $modelQueue;

    /** @var TSG_CallCenter_Model_Adapter_Queue_Collection $adapterModelQueue */
    private $adapterModelQueue;

    /** @var TSG_CallCenter_Model_Adapter_Order_Collection $adapterModelOrder */
    private $adapterModelOrder;

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
        $this->adapterModelQueue = Mage::getModel('callcenter/adapter_queue_collection');
        $this->adapterModelOrder = Mage::getModel('callcenter/adapter_order_collection');
        parent::setUp();
    }

    public function tearDown()
    {
        $this->handler = null;
        $this->modelQueue = null;
        $this->adapterModelQueue = null;
        $this->adapterModelOrder = null;
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
        $result = [
            'one user one matched order' => [
                [
                    [
                        'queue_id' => 1,
                        'user_id' => 9,
                        'products_type' => TSG_CallCenter_Model_Queue::PRODUCTS_TYPE_LARGE_DEVICES,
                        'orders_type' => TSG_CallCenter_Model_Queue::ORDERS_TYPE_NOT_SPECIFIED
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
                        'products_type' => TSG_CallCenter_Model_Queue::PRODUCTS_TYPE_LARGE_DEVICES,
                        'orders_type' => TSG_CallCenter_Model_Queue::ORDERS_TYPE_NOT_SPECIFIED
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
                        'customer_email' => 'test@example.com',
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
                        'products_type' => TSG_CallCenter_Model_Queue::PRODUCTS_TYPE_SMALL_DEVICES,
                        'orders_type' => TSG_CallCenter_Model_Queue::ORDERS_TYPE_NOT_SPECIFIED
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
            'two user two matched orders' => [
                [
                    [
                        'queue_id' => 1,
                        'user_id' => 8,
                        'products_type' => TSG_CallCenter_Model_Queue::PRODUCTS_TYPE_LARGE_DEVICES,
                        'orders_type' => TSG_CallCenter_Model_Queue::ORDERS_TYPE_DAY
                    ],
                    [
                        'queue_id' => 2,
                        'user_id' => 9,
                        'products_type' => TSG_CallCenter_Model_Queue::PRODUCTS_TYPE_LARGE_DEVICES,
                        'orders_type' => TSG_CallCenter_Model_Queue::ORDERS_TYPE_NIGHT
                    ]
                ],
                [
                    [
                        'id' => 100,
                        'customer_email' => 'test@example.com',
                        'created_at' => '2013-04-04 10:34:48',
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
                [9 => [200], 8 => [100]]
            ],
            'two user one matched order' => [
                [
                    [
                        'queue_id' => 1,
                        'user_id' => 8,
                        'products_type' => TSG_CallCenter_Model_Queue::PRODUCTS_TYPE_GADGETS,
                        'orders_type' => TSG_CallCenter_Model_Queue::ORDERS_TYPE_DAY
                    ],
                    [
                        'queue_id' => 2,
                        'user_id' => 9,
                        'products_type' => TSG_CallCenter_Model_Queue::PRODUCTS_TYPE_LARGE_DEVICES,
                        'orders_type' => TSG_CallCenter_Model_Queue::ORDERS_TYPE_NIGHT
                    ]
                ],
                [
                    [
                        'id' => 100,
                        'customer_email' => 'test@example.com',
                        'created_at' => '2013-04-04 10:34:48',
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
                [9 => [200]]
            ],
            'two user three matched orders' => [
                [
                    [
                        'queue_id' => 1,
                        'user_id' => 8,
                        'products_type' => TSG_CallCenter_Model_Queue::PRODUCTS_TYPE_NOT_SPECIFIED,
                        'orders_type' => TSG_CallCenter_Model_Queue::ORDERS_TYPE_NIGHT
                    ],
                    [
                        'queue_id' => 2,
                        'user_id' => 9,
                        'products_type' => TSG_CallCenter_Model_Queue::PRODUCTS_TYPE_LARGE_DEVICES,
                        'orders_type' => TSG_CallCenter_Model_Queue::ORDERS_TYPE_DAY
                    ]
                ],
                [
                    [
                        'id' => 100,
                        'customer_email' => 'test3@example.com',
                        'created_at' => '2013-04-04 10:34:48',
                        'ordered_items' => [
                            ['custom_product_type' => 'КБТ']
                        ]
                    ],
                    [
                        'id' => 200,
                        'customer_email' => 'test2@example.com',
                        'created_at' => '2013-04-04 03:34:48',
                        'ordered_items' => [
                            ['custom_product_type' => ''],
                            ['custom_product_type' => '']
                        ]
                    ],
                    [
                        'id' => 300,
                        'customer_email' => 'test3@example.com',
                        'created_at' => '2013-04-04 12:34:48',
                        'ordered_items' => [
                            ['custom_product_type' => 'КБТ'],
                            ['custom_product_type' => 'МБТ']
                        ]
                    ]
                ],
                [8 => [200], 9 => [100, 300]]
            ],
            'two user two matched orders and one not matched' => [
                [
                    [
                        'queue_id' => 1,
                        'user_id' => 8,
                        'products_type' => TSG_CallCenter_Model_Queue::PRODUCTS_TYPE_NOT_SPECIFIED,
                        'orders_type' => TSG_CallCenter_Model_Queue::ORDERS_TYPE_NIGHT
                    ],
                    [
                        'queue_id' => 2,
                        'user_id' => 9,
                        'products_type' => TSG_CallCenter_Model_Queue::PRODUCTS_TYPE_LARGE_DEVICES,
                        'orders_type' => TSG_CallCenter_Model_Queue::ORDERS_TYPE_DAY
                    ]
                ],
                [
                    [
                        'id' => 100,
                        'customer_email' => 'test@example.com',
                        'created_at' => '2013-04-04 10:34:48',
                        'ordered_items' => [
                            ['custom_product_type' => 'МБТ']
                        ]
                    ],
                    [
                        'id' => 200,
                        'customer_email' => 'test2@example.com',
                        'created_at' => '2013-04-04 03:34:48',
                        'ordered_items' => [
                            ['custom_product_type' => ''],
                            ['custom_product_type' => '']
                        ]
                    ],
                    [
                        'id' => 300,
                        'customer_email' => 'test3@example.com',
                        'created_at' => '2013-04-04 12:34:48',
                        'ordered_items' => [
                            ['custom_product_type' => 'КБТ'],
                            ['custom_product_type' => 'МБТ']
                        ]
                    ]
                ],
                [8 => [200], 9 => [300]]
            ],
            'two user two matched orders and two not matched' => [
                [
                    [
                        'queue_id' => 1,
                        'user_id' => 8,
                        'products_type' => TSG_CallCenter_Model_Queue::PRODUCTS_TYPE_NOT_SPECIFIED,
                        'orders_type' => TSG_CallCenter_Model_Queue::ORDERS_TYPE_NIGHT
                    ],
                    [
                        'queue_id' => 2,
                        'user_id' => 9,
                        'products_type' => TSG_CallCenter_Model_Queue::PRODUCTS_TYPE_LARGE_DEVICES,
                        'orders_type' => TSG_CallCenter_Model_Queue::ORDERS_TYPE_DAY
                    ]
                ],
                [
                    [
                        'id' => 100,
                        'customer_email' => 'test@example.com',
                        'created_at' => '2013-04-04 10:34:48',
                        'ordered_items' => [
                            ['custom_product_type' => 'МБТ']
                        ]
                    ],
                    [
                        'id' => 200,
                        'customer_email' => 'test2@example.com',
                        'created_at' => '2013-04-04 03:34:48',
                        'ordered_items' => [
                            ['custom_product_type' => ''],
                            ['custom_product_type' => '']
                        ]
                    ],
                    [
                        'id' => 300,
                        'customer_email' => 'test3@example.com',
                        'created_at' => '2013-04-04 12:34:48',
                        'ordered_items' => [
                            ['custom_product_type' => 'КБТ'],
                            ['custom_product_type' => 'МБТ']
                        ]
                    ],
                    [
                        'id' => 400,
                        'customer_email' => 'test@example.com',
                        'created_at' => '2013-04-04 14:34:48',
                        'ordered_items' => [
                            ['custom_product_type' => 'МБТ']
                        ]
                    ]
                ],
                [8 => [200], 9 => [300]]
            ],
            'two user four matched orders' => [
                [
                    [
                        'queue_id' => 1,
                        'user_id' => 8,
                        'products_type' => TSG_CallCenter_Model_Queue::PRODUCTS_TYPE_NOT_SPECIFIED,
                        'orders_type' => TSG_CallCenter_Model_Queue::ORDERS_TYPE_NIGHT
                    ],
                    [
                        'queue_id' => 2,
                        'user_id' => 9,
                        'products_type' => TSG_CallCenter_Model_Queue::PRODUCTS_TYPE_LARGE_DEVICES,
                        'orders_type' => TSG_CallCenter_Model_Queue::ORDERS_TYPE_DAY
                    ]
                ],
                [
                    [
                        'id' => 100,
                        'customer_email' => 'test3@example.com',
                        'created_at' => '2013-04-04 10:34:48',
                        'ordered_items' => [
                            ['custom_product_type' => 'КБТ']
                        ]
                    ],
                    [
                        'id' => 200,
                        'customer_email' => 'test2@example.com',
                        'created_at' => '2013-04-04 03:34:48',
                        'ordered_items' => [
                            ['custom_product_type' => ''],
                            ['custom_product_type' => '']
                        ]
                    ],
                    [
                        'id' => 300,
                        'customer_email' => 'test3@example.com',
                        'created_at' => '2013-04-04 12:34:48',
                        'ordered_items' => [
                            ['custom_product_type' => 'КБТ'],
                            ['custom_product_type' => 'МБТ']
                        ]
                    ],
                    [
                        'id' => 400,
                        'customer_email' => 'test2@example.com',
                        'created_at' => '2013-04-04 06:34:48',
                        'ordered_items' => [
                            ['custom_product_type' => '']
                        ]
                    ]
                ],
                [8 => [200, 400], 9 => [100, 300]]
            ],
            'two user three matched orders and one not matched' => [
                [
                    [
                        'queue_id' => 1,
                        'user_id' => 8,
                        'products_type' => TSG_CallCenter_Model_Queue::PRODUCTS_TYPE_NOT_SPECIFIED,
                        'orders_type' => TSG_CallCenter_Model_Queue::ORDERS_TYPE_NIGHT
                    ],
                    [
                        'queue_id' => 2,
                        'user_id' => 9,
                        'products_type' => TSG_CallCenter_Model_Queue::PRODUCTS_TYPE_GADGETS,
                        'orders_type' => TSG_CallCenter_Model_Queue::ORDERS_TYPE_DAY
                    ]
                ],
                [
                    [
                        'id' => 100,
                        'customer_email' => 'test@example.com',
                        'created_at' => '2013-04-04 05:34:48',
                        'ordered_items' => [
                            ['custom_product_type' => ''],
                            ['custom_product_type' => '']
                        ]
                    ],
                    [
                        'id' => 200,
                        'customer_email' => 'test2@example.com',
                        'created_at' => '2013-04-04 03:34:48',
                        'ordered_items' => [
                            ['custom_product_type' => ''],
                            ['custom_product_type' => '']
                        ]
                    ],
                    [
                        'id' => 300,
                        'customer_email' => 'test3@example.com',
                        'created_at' => '2013-04-04 12:34:48',
                        'ordered_items' => [
                            ['custom_product_type' => 'Гаджеты'],
                            ['custom_product_type' => '']
                        ]
                    ],
                    [
                        'id' => 400,
                        'customer_email' => 'test3@example.com',
                        'created_at' => '2013-04-04 14:34:48',
                        'ordered_items' => [
                            ['custom_product_type' => 'Гаджеты']
                        ]
                    ]
                ],
                [8 => [200], 9 => [300, 400]]
            ],
            'two user three matched orders and time check' => [
                [
                    [
                        'queue_id' => 1,
                        'user_id' => 8,
                        'products_type' => TSG_CallCenter_Model_Queue::PRODUCTS_TYPE_NOT_SPECIFIED,
                        'orders_type' => TSG_CallCenter_Model_Queue::ORDERS_TYPE_NIGHT
                    ],
                    [
                        'queue_id' => 2,
                        'user_id' => 9,
                        'products_type' => TSG_CallCenter_Model_Queue::PRODUCTS_TYPE_GADGETS,
                        'orders_type' => TSG_CallCenter_Model_Queue::ORDERS_TYPE_DAY
                    ]
                ],
                [
                    [
                        'id' => 100,
                        'customer_email' => 'test2@example.com',
                        'created_at' => '2013-04-04 08:09:59',
                        'ordered_items' => [
                            ['custom_product_type' => ''],
                            ['custom_product_type' => '']
                        ]
                    ],
                    [
                        'id' => 200,
                        'customer_email' => 'test3@example.com',
                        'created_at' => '2013-04-04 08:10:00',
                        'ordered_items' => [
                            ['custom_product_type' => 'Гаджеты'],
                            ['custom_product_type' => '']
                        ]
                    ],
                    [
                        'id' => 300,
                        'customer_email' => 'test3@example.com',
                        'created_at' => '2013-04-04 20:54:59',
                        'ordered_items' => [
                            ['custom_product_type' => 'Гаджеты']
                        ]
                    ]
                ],
                [8 => [100], 9 => [200, 300]]
            ],
            'two user three matched orders and one not matched and time check' => [
                [
                    [
                        'queue_id' => 1,
                        'user_id' => 8,
                        'products_type' => TSG_CallCenter_Model_Queue::PRODUCTS_TYPE_NOT_SPECIFIED,
                        'orders_type' => TSG_CallCenter_Model_Queue::ORDERS_TYPE_NIGHT
                    ],
                    [
                        'queue_id' => 2,
                        'user_id' => 9,
                        'products_type' => TSG_CallCenter_Model_Queue::PRODUCTS_TYPE_GADGETS,
                        'orders_type' => TSG_CallCenter_Model_Queue::ORDERS_TYPE_DAY
                    ]
                ],
                [
                    [
                        'id' => 100,
                        'customer_email' => 'test@example.com',
                        'created_at' => '2013-04-04 08:10:01',
                        'ordered_items' => [
                            ['custom_product_type' => 'Гаджеты'],
                            ['custom_product_type' => '']
                        ]
                    ],
                    [
                        'id' => 200,
                        'customer_email' => 'test2@example.com',
                        'created_at' => '2013-04-04 08:09:59',
                        'ordered_items' => [
                            ['custom_product_type' => ''],
                            ['custom_product_type' => '']
                        ]
                    ],
                    [
                        'id' => 300,
                        'customer_email' => 'test2@example.com',
                        'created_at' => '2013-04-04 20:55:00',
                        'ordered_items' => [
                            ['custom_product_type' => '']
                        ]
                    ],
                    [
                        'id' => 400,
                        'customer_email' => 'test2@example.com',
                        'created_at' => '2013-04-04 20:54:59',
                        'ordered_items' => [
                            ['custom_product_type' => '']
                        ]
                    ]
                ],
                [8 => [200, 300], 9 => [100]]
            ]
        ];
        return $result;
    }

    /**
     * @param $itemsQueue
     * @return TSG_CallCenter_Model_Adapter_Queue_Collection
     * @throws Exception
     */
    private function getCollectionQueueData($itemsQueue): TSG_CallCenter_Model_Adapter_Queue_Collection
    {
        $collection = $this->adapterModelQueue;

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
        $collection = $this->adapterModelOrder;

        $sortedItemsOrder = $this->getSortedArray($itemsOrder);

        foreach ($sortedItemsOrder as $itemOrder) {
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

    /**
     * Sorting assoc array by key
     *
     * @param array $items
     * @param string $sortKey
     * @return array
     */
    private function getSortedArray(array $items, string $sortKey = 'created_at'): array
    {
        $sortBy = array();
        foreach ($items as $key => $row) {
            $sortBy[$key] = $row[$sortKey];
        }
        array_multisort($sortBy, SORT_ASC, $items);
        return $items;
    }
}