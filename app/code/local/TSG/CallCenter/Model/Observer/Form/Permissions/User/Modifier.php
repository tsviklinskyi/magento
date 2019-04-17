<?php
class TSG_CallCenter_Model_Observer_Form_Permissions_User_Modifier
{
    /**
     * Adding new fields to form
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function onAdminhtmlBlockHtmlBefore(Varien_Event_Observer $observer)
    {
        $block = $observer->getBlock();
        if (!isset($block)) return $this;

        switch ($block->getType()) {
            case 'adminhtml/permissions_user_edit_tab_main':
                $model = Mage::registry('permissions_user');
                $form = $block->getForm();
                $fieldset = $form->getElement('base_fieldset');
                $fieldset->addField('orders_type', 'select', array(
                    'label'     => Mage::helper('adminhtml')->__('Orders type'),
                    'class'     => 'input-select',
                    'name'      => 'orders_type',
                    'options'   => Mage::getModel('callcenter/queue')->getOrderTypes(),
                    'value'     => $model->getData('orders_type')
                ));
                $fieldset->addField('products_type', 'select', array(
                    'label'     => Mage::helper('adminhtml')->__('Products type'),
                    'class'     => 'input-select',
                    'name'      => 'products_type',
                    'options'   => Mage::getModel('callcenter/queue')->getProductTypes(),
                    'value'     => $model->getData('products_type'),
                ));
                break;
        }
        return $this;
    }
}