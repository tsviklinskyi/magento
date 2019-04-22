<?php
class TSG_CallCenter_Model_Observer_Block_Widget_Modifier
{
    /**
     * Adding new buttons to grid and order view page
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function salesOrderAddInitiatorButtons(Varien_Event_Observer $observer)
    {
        $block = $observer->getBlock();
        if (!isset($block)) return $this;

        switch ($block->getType()) {
            case 'adminhtml/sales_order':
                /* @var TSG_CallCenter_Model_Queue $callcenterQueue */
                $callcenterQueue = Mage::getModel('callcenter/queue');
                if ($callcenterQueue->isAllowedByRole($callcenterQueue->getSpecialistRolesKey()) && $callcenterQueue->getCountOrdersByUser() === 0) {
                    if ($callcenterQueue->getCountByUser()) {
                        $data = array(
                            'label'     => 'Waiting order',
                            'class'     => 'disabled reload-page-5',
                        );
                    }else{
                        $data = array(
                            'label'     => 'Get order',
                            'class'     => '',
                            'onclick'   => 'setLocation(\''  . Mage::helper('adminhtml')->getUrl('adminhtml/callcenter_initiator/addToQueue') . '\')'
                        );
                    }
                    $block->addButton('get-order', $data);
                }
                break;
            case 'adminhtml/sales_order_view':
                /* @var TSG_CallCenter_Model_Queue $callcenterQueue */
                $callcenterQueue = Mage::getModel('callcenter/queue');
                if($callcenterQueue->isAllowedByRole($callcenterQueue->getCoordinatorRolesKey())) {
                    $order = Mage::registry('current_order');
                    $data = array(
                        'label'     => 'Clear Initiator',
                        'class'     => '',
                        'onclick'   => 'setLocation(\''  . Mage::helper('adminhtml')->getUrl('adminhtml/callcenter_initiator/clearInitiator', array('order_id' => $order->getId())) . '\')'
                    );
                    $block->addButton('clear-initiator', $data);
                }
                break;
        }

        return $this;
    }

    /**
     * Adding new massaction to sales order page
     *
     * @param Varien_Event_Observer $observer
     * @throws Mage_Core_Model_Store_Exception
     */
    public function addClearInitiatorMassAction(Varien_Event_Observer $observer) {
        $block = $observer->getBlock();
        /* @var TSG_CallCenter_Model_Queue $callcenterQueue */
        $callcenterQueue = Mage::getModel('callcenter/queue');
        if (get_class($block) === 'Mage_Adminhtml_Block_Widget_Grid_Massaction'
            && $block->getRequest()->getControllerName() === 'sales_order'
            && $callcenterQueue->isAllowedByRole($callcenterQueue->getCoordinatorRolesKey())
        ) {
            $block->addItem('clear_initiator', array(
                'label' => Mage::helper('sales')->__('Clear Initiator'),
                'url' => Mage::app()->getStore()->getUrl('*/callcenter_initiator/clearInitiator'),
            ));
        }
    }
}