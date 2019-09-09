<?php

/**
 * Price Request admin block
 *
 * @category    TSV
 * @package     TSV_RequestPrice
 * @author      TSV
 */
class TSV_RequestPrice_Block_Adminhtml_Pricerequest extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    /**
     * constructor
     *
     * @access public
     * @return void
     * @author TSV
     */
    public function __construct()
    {
        $this->_controller         = 'adminhtml_pricerequest';
        $this->_blockGroup         = 'tsv_requestprice';
        parent::__construct();
        $this->_headerText         = Mage::helper('tsv_requestprice')->__('Price Request');
        $this->_updateButton('add', 'label', Mage::helper('tsv_requestprice')->__('Add Price Request'));

    }
}
