<?php
class TSG_CallCenter_Model_Resource_Queue extends Mage_Core_Model_Resource_Db_Abstract{
    protected function _construct()
    {
        $this->_init('callcenter/queue', 'queue_id');
    }
}