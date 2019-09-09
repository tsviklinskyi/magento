<?php

/**
 * Price Request admin edit tabs
 *
 * @category    TSV
 * @package     TSV_RequestPrice
 * @author      TSV
 */
class TSV_RequestPrice_Block_Adminhtml_Pricerequest_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    /**
     * Initialize Tabs
     *
     * @access public
     * @author TSV
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('pricerequest_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('tsv_requestprice')->__('Price Request'));
    }

    /**
     * before render html
     *
     * @access protected
     * @return TSV_RequestPrice_Block_Adminhtml_Pricerequest_Edit_Tabs
     * @author TSV
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'form_pricerequest',
            array(
                'label'   => Mage::helper('tsv_requestprice')->__('Price Request'),
                'title'   => Mage::helper('tsv_requestprice')->__('Price Request'),
                'content' => $this->getLayout()->createBlock(
                    'tsv_requestprice/adminhtml_pricerequest_edit_tab_form'
                )
                ->toHtml(),
            )
        );
        if (!Mage::app()->isSingleStoreMode()) {
            $this->addTab(
                'form_store_pricerequest',
                array(
                    'label'   => Mage::helper('tsv_requestprice')->__('Store views'),
                    'title'   => Mage::helper('tsv_requestprice')->__('Store views'),
                    'content' => $this->getLayout()->createBlock(
                        'tsv_requestprice/adminhtml_pricerequest_edit_tab_stores'
                    )
                    ->toHtml(),
                )
            );
        }
        return parent::_beforeToHtml();
    }

    /**
     * Retrieve price request entity
     *
     * @access public
     * @return TSV_RequestPrice_Model_Pricerequest
     * @author TSV
     */
    public function getPricerequest()
    {
        return Mage::registry('current_pricerequest');
    }
}
