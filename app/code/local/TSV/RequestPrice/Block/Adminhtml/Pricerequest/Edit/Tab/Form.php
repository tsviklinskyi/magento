<?php

/**
 * Price Request edit form tab
 *
 * @category    TSV
 * @package     TSV_RequestPrice
 * @author      TSV
 */
class TSV_RequestPrice_Block_Adminhtml_Pricerequest_Edit_Tab_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * prepare the form
     *
     * @access protected
     * @return TSV_RequestPrice_Block_Adminhtml_Pricerequest_Edit_Tab_Form
     * @author TSV
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('pricerequest_');
        $form->setFieldNameSuffix('pricerequest');
        $this->setForm($form);
        $fieldset = $form->addFieldset(
            'pricerequest_form',
            array('legend' => Mage::helper('tsv_requestprice')->__('Price Request'))
        );

        $fieldset->addField(
            'name',
            'text',
            array(
                'label' => Mage::helper('tsv_requestprice')->__('Name'),
                'name'  => 'name',
                'required'  => true,
                'class' => 'required-entry',

           )
        );

        $fieldset->addField(
            'email',
            'text',
            array(
                'label' => Mage::helper('tsv_requestprice')->__('Email'),
                'name'  => 'email',
                'required'  => true,
                'class' => 'required-entry',

           )
        );

        $fieldset->addField(
            'product_sku',
            'text',
            array(
                'label' => Mage::helper('tsv_requestprice')->__('Product SKU'),
                'name'  => 'product_sku',
                'required'  => true,
                'class' => 'required-entry',

           )
        );

        $fieldset->addField(
            'comment',
            'textarea',
            array(
                'label' => Mage::helper('tsv_requestprice')->__('Comment'),
                'name'  => 'comment',

           )
        );

        $fieldset->addField(
            'status',
            'select',
            array(
                'label' => Mage::helper('tsv_requestprice')->__('Status'),
                'name'  => 'status',
                'required'  => true,
                'class' => 'required-entry',

                'values'=> Mage::getModel('tsv_requestprice/pricerequest_attribute_source_status')->getAllOptions(true),
           )
        );
        if (Mage::app()->isSingleStoreMode()) {
            $fieldset->addField(
                'store_id',
                'hidden',
                array(
                    'name'      => 'stores[]',
                    'value'     => Mage::app()->getStore(true)->getId()
                )
            );
            Mage::registry('current_pricerequest')->setStoreId(Mage::app()->getStore(true)->getId());
        }
        $formValues = Mage::registry('current_pricerequest')->getDefaultValues();
        if (!is_array($formValues)) {
            $formValues = array();
        }
        if (Mage::getSingleton('adminhtml/session')->getPricerequestData()) {
            $formValues = array_merge($formValues, Mage::getSingleton('adminhtml/session')->getPricerequestData());
            Mage::getSingleton('adminhtml/session')->setPricerequestData(null);
        } elseif (Mage::registry('current_pricerequest')) {
            $formValues = array_merge($formValues, Mage::registry('current_pricerequest')->getData());
        }
        $form->setValues($formValues);
        return parent::_prepareForm();
    }
}
