<?php
class TSG_CallCenter_Block_Adminhtml_Widget_Grid_Column_Filter_Text extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Text
{
    /**
     * @return array
     */
    public function getCondition()
    {
        /* @var TSG_CallCenter_Model_Queue $callcenterQueue */
        $callcenterQueue = Mage::getModel('callcenter/queue');
        if ($this->getColumn()->getIndex() === 'customer_email' && $callcenterQueue->isAllowedByRole(1)) {
            return array('eq' => $this->getValue());
        }else{
            return parent::getCondition();
        }
    }
}