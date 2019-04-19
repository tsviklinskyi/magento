<?php
require_once 'abstract.php';

class TSG_Shell_Queue extends Mage_Shell_Abstract
{
    /**
     * Queue handler object
     *
     * @var TSG_CallCenter_Model_Observer_Queue_Handler
     */
    protected $handler;

    /**
     * Get queue handler object
     *
     * @return TSG_CallCenter_Model_Observer_Queue_Handler
     */
    protected function getHandler(): TSG_CallCenter_Model_Observer_Queue_Handler
    {
        if ($this->handler === null) {
            $this->handler = Mage::getModel('callcenter/observer_queue_handler');
        }
        return $this->handler;
    }

    /**
     * Run script
     *
     */
    public function run()
    {
        while (true) {
            try {
                $this->getHandler()->queueDistribution();
                echo "Queue distribution successfully finished\n";
            } catch (Exception $e) {
                echo "Queue distribution error:\n\n";
                echo $e . "\n";
            }
            sleep(3);
        }

    }

    /**
     * Retrieve Usage Help Message
     *
     */
    public function usageHelp()
    {
        return <<<USAGE
Usage:  php -f queue.php

  help                   This help
 
USAGE;
    }
}

$shell = new TSG_Shell_Queue();
$shell->run();
