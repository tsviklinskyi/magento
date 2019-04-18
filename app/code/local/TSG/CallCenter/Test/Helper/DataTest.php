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
        $collectionQueue = $this->_modelQueue->getCollection();
        $collectionQueue->addItem($this->_modelQueue->setQueue_id(200)->setUserId(9));
        return $collectionQueue;
    }

    public function getResultQueueData()
    {
        return array(
            '9' => array('195', '196')
        );
    }

    public function binaryGap($str)
    {
        $maxBinaryGap = 0;
        $first1 = strpos($str, '1');
        if ($first1 !== false) {
            $nextStr = substr($str,$first1 + 1);
            $next1 = strpos($nextStr, '1');
            while ($next1 !== false) {
                if ($next1 > $maxBinaryGap) {
                    $maxBinaryGap = $next1;
                }
                $nextStr = substr($nextStr,$next1 + 1);
                $next1 = strpos($nextStr, '1');
            }
        }
        return $maxBinaryGap;
    }

    public function testBinaryGap()
    {
        $str = '1000010001';
        $this->assertEquals($this->checkBbinaryGap($str), $this->binaryGap($str));
    }

    public function checkBbinaryGap($str)
    {
        $binaryGap = 0;
        switch ($str){
            case '1000010001' :
                $binaryGap = 4;
                break;
            case '100000001' :
                $binaryGap = 7;
                break;
            case '10000000' :
                $binaryGap = 0;
                break;
            case '000000' :
                $binaryGap = 0;
                break;
            case '111111111' :
                $binaryGap = 0;
                break;
            case '00000001' :
                $binaryGap = 0;
                break;
            case '1001000001' :
                $binaryGap = 5;
                break;
        }
        return $binaryGap;
    }
}