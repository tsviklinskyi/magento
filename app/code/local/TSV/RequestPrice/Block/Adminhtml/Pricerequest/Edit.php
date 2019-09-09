<?php

/**
 * Price Request admin edit form
 *
 * @category    TSV
 * @package     TSV_RequestPrice
 * @author      TSV
 */
class TSV_RequestPrice_Block_Adminhtml_Pricerequest_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
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
        parent::__construct();
        $this->_blockGroup = 'tsv_requestprice';
        $this->_controller = 'adminhtml_pricerequest';
        $this->_updateButton(
            'save',
            'label',
            Mage::helper('tsv_requestprice')->__('Save Price Request')
        );
        $this->_updateButton(
            'delete',
            'label',
            Mage::helper('tsv_requestprice')->__('Delete Price Request')
        );
        $this->_addButton(
            'saveandcontinue',
            array(
                'label'   => Mage::helper('tsv_requestprice')->__('Save And Continue Edit'),
                'onclick' => 'saveAndContinueEdit()',
                'class'   => 'save',
            ),
            -100
        );
        $this->_formScripts[] = "
            function saveAndContinueEdit() {
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";
    }

    /**
     * get the edit form header
     *
     * @access public
     * @return string
     * @author TSV
     */
    public function getHeaderText()
    {
        if (Mage::registry('current_pricerequest') && Mage::registry('current_pricerequest')->getId()) {
            return Mage::helper('tsv_requestprice')->__(
                "Edit Price Request '%s'",
                $this->escapeHtml(Mage::registry('current_pricerequest')->getName())
            );
        } else {
            return Mage::helper('tsv_requestprice')->__('Add Price Request');
        }
    }
}
