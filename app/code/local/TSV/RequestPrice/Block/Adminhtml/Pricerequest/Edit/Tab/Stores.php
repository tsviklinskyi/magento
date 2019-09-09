<?php

/**
 * store selection tab
 *
 * @category    TSV
 * @package     TSV_RequestPrice
 * @author      TSV
 */
class TSV_RequestPrice_Block_Adminhtml_Pricerequest_Edit_Tab_Stores extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * prepare the form
     *
     * @access protected
     * @return TSV_RequestPrice_Block_Adminhtml_Pricerequest_Edit_Tab_Stores
     * @author TSV
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $form->setFieldNameSuffix('pricerequest');
        $this->setForm($form);
        $fieldset = $form->addFieldset(
            'pricerequest_stores_form',
            array('legend' => Mage::helper('tsv_requestprice')->__('Store views'))
        );
        $field = $fieldset->addField(
            'store_id',
            'multiselect',
            array(
                'name'     => 'stores[]',
                'label'    => Mage::helper('tsv_requestprice')->__('Store Views'),
                'title'    => Mage::helper('tsv_requestprice')->__('Store Views'),
                'required' => true,
                'values'   => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
            )
        );
        $renderer = $this->getLayout()->createBlock('adminhtml/store_switcher_form_renderer_fieldset_element');
        $field->setRenderer($renderer);
        $form->addValues(Mage::registry('current_pricerequest')->getData());
        return parent::_prepareForm();
    }
}
