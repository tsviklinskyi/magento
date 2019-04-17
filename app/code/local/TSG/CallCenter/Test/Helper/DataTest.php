<?php
class TSG_Callcenter_Test_Helper_DataTest extends PHPUnit_Framework_TestCase
{
    /** @var TSG_CallCenter_Helper_Data $_dataHelper */
    protected $_dataHelper;

    /** @var TSG_CallCenter_Model_Queue $_modelQueue */
    protected $_modelQueue;

    public function __construct($name = null, array $data = array(), $dataName = '')
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
        $this->_dataHelper = Mage::helper('callcenter');
        $this->_modelQueue = Mage::getModel('callcenter/queue');
        parent::setUp();
    }

    public function tearDown()
    {
        $this->_dataHelper = null;
        $this->_modelQueue = null;
        parent::tearDown();
    }

    /**
     * Test that method generateDataByQueue work as expected.
     *
     * @covers TSG_CallCenter_Helper_Data::generateDataByQueue
     */
    public function testGenerateDataByQueue()
    {
        $collectionQueue = $this->getCollectionQueueData();
        $queueData = $this->_dataHelper->generateDataByQueue($collectionQueue);
        $this->assertEquals($this->getResultQueueData(), $queueData);
    }

    public function getCollectionQueueData()
    {
        return array(
            $this->_modelQueue->setQueue_id(200)->setUserId(9)
        );
    }

    public function getResultQueueData()
    {
        return array(
            '9' => array('195', '196')
        );
    }
}