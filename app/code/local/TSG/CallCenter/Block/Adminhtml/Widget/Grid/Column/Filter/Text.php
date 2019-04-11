<?php
class TSG_CallCenter_Block_Adminhtml_Widget_Grid_Column_Filter_Text extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Text
{
    public function getCondition()
    {
        if ($this->getColumn()->getIndex() == 'customer_email' && Mage::getModel('callcenter/queue')->isAllowedByRole()) {
            return array('eq' => $this->getValue());
        }else{
            return parent::getCondition();
        }
    }
}