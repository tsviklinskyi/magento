<?php
class TSG_CallCenter_Block_Adminhtml_Sales_Order_Grid extends Mage_Adminhtml_Block_Sales_Order_Grid
{
    protected function _prepareMassaction()
    {
        parent::_prepareMassaction();

        if (Mage::getModel('callcenter/queue')->isAllowedByRole(2)) {
            $this->getMassactionBlock()->addItem('clear_initiator', array(
                'label'=> Mage::helper('sales')->__('Clear Initiator'),
                'url'  => $this->getUrl('*/sales_order/clearInitiator'),
            ));
        }

        return $this;
    }
}