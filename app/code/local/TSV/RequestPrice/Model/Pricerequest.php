<?php

/**
 * Price Request model
 *
 * @category    TSV
 * @package     TSV_RequestPrice
 * @author      TSV
 */
class TSV_RequestPrice_Model_Pricerequest extends Mage_Core_Model_Abstract
{
    /**
     * Entity code.
     * Can be used as part of method name for entity processing
     */
    const ENTITY    = 'tsv_requestprice_pricerequest';
    const CACHE_TAG = 'tsv_requestprice_pricerequest';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'tsv_requestprice_pricerequest';

    /**
     * Parameter name in event
     *
     * @var string
     */
    protected $_eventObject = 'pricerequest';

    /**
     * constructor
     *
     * @access public
     * @return void
     * @author TSV
     */
    public function _construct()
    {
        parent::_construct();
        $this->_init('tsv_requestprice/pricerequest');
    }

    /**
     * before save price request
     *
     * @access protected
     * @return TSV_RequestPrice_Model_Pricerequest
     * @author TSV
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();
        $now = Mage::getSingleton('core/date')->gmtDate();
        if ($this->isObjectNew()) {
            $this->setCreatedAt($now);
        }
        $this->setUpdatedAt($now);
        return $this;
    }

    /**
     * save price request relation
     *
     * @access public
     * @return TSV_RequestPrice_Model_Pricerequest
     * @author TSV
     */
    protected function _afterSave()
    {
        return parent::_afterSave();
    }

    /**
     * get default values
     *
     * @access public
     * @return array
     * @author TSV
     */
    public function getDefaultValues()
    {
        $values = array();
        $values['status'] = '1';

        return $values;
    }
    
}
