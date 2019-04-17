<?php
class TSG_CallCenter_Model_Observer
{
    /**
     * Update collection before load, join tables and add filters
     * @param $observer
     * @return $this
     */
    public function salesOrderGridCollectionLoadBefore($observer)
    {
        $collection = $observer->getOrderGridCollection();
        $select = $collection->getSelect();
        $select->joinLeft(
            array('au' => 'admin_user'),
            'au.user_id = main_table.initiator_id',
            array(
                'initiator_name' => 'CONCAT(au.firstname, " ", au.lastname)'
            )
        );
        $select->joinLeft(
            array('au2' => 'admin_user'),
            'au2.user_id = main_table.primary_initiator_id',
            array(
                'primary_initiator_name' => 'CONCAT(au2.firstname, " ", au2.lastname)'
            )
        );
        $select->group('main_table.entity_id');

        $this->_filterCollectionByRole($collection);
        return $this;
    }

    /**
     * Add user role filter to collection
     * @param $collection
     */
    private function _filterCollectionByRole($collection)
    {
        $modelQueue = Mage::getModel('callcenter/queue');
        if ($modelQueue->isAllowedByRole()) {
            $collection->addAttributeToFilter('initiator_id', Mage::getSingleton('admin/session')->getUser()->getUserId());
        }elseif ($modelQueue->isAllowedByRole(2)) {
            $collection->addAttributeToFilter('initiator_id', array('notnull' => true));
        }
    }

    /**
     * Adding new buttons to grid and order view page
     * @param $observer
     * @return $this
     */
    public function addNewButtons($observer)
    {
        $container = $observer->getBlock();
        $modelQueue = Mage::getModel('callcenter/queue');
        if(null !== $container && $container->getType() == 'adminhtml/sales_order' && $modelQueue->isAllowedByRole() && $modelQueue->getCountOrdersByUser() == 0) {
            if (Mage::getModel('callcenter/queue')->getCountByUser()) {
                $data = array(
                    'label'     => 'Waiting order',
                    'class'     => 'disabled reload-page-5',
                );
            }else{
                $data = array(
                    'label'     => 'Get order',
                    'class'     => '',
                    'onclick'   => 'setLocation(\''  . Mage::helper('adminhtml')->getUrl('adminhtml/callcenter_initiator/setInitiator') . '\')'
                );
            }
            $container->addButton('get-order', $data);
        }

        if(null !== $container && $container->getType() == 'adminhtml/sales_order_view' && $modelQueue->isAllowedByRole(2)) {
            $order = Mage::registry('current_order');
            $data = array(
                'label'     => 'Clear Initiator',
                'class'     => '',
                'onclick'   => 'setLocation(\''  . Mage::helper('adminhtml')->getUrl('adminhtml/callcenter_initiator/clearInitiator', array('order_id' => $order->getId())) . '\')'
            );
            $container->addButton('clear-initiator', $data);
        }

        return $this;
    }

    /**
     * Distribution queue of waiting users, save relations users with orders and clear queue
     */
    public function queueDistribution()
    {
        Mage::log('TSG CallCenter queueDistribution was run at ' . date('Y-m-d H:i:s'), null, 'tsg_callcenter_queue.log', true);
        $modelQueue = Mage::getModel('callcenter/queue');
        $collectionQueue = $modelQueue->getCollection()->setOrder('request_date', 'ASC');
        Mage::helper('callcenter')->queueDistribution($collectionQueue);
        Mage::log('TSG CallCenter queueDistribution finished at ' . date('Y-m-d H:i:s'), null, 'tsg_callcenter_queue.log', true);
    }

    /**
     * @param $observer
     * @throws Mage_Core_Model_Store_Exception
     */
    public function addMassAction($observer) {
        $block = $observer->getEvent()->getBlock();
        if (get_class($block) =='Mage_Adminhtml_Block_Widget_Grid_Massaction'
            && $block->getRequest()->getControllerName() == 'sales_order'
            && Mage::getModel('callcenter/queue')->isAllowedByRole(2)
        ) {
            $block->addItem('clear_initiator', array(
                'label' => Mage::helper('sales')->__('Clear Initiator'),
                'url' => Mage::app()->getStore()->getUrl('*/callcenter_initiator/clearInitiator'),
            ));
        }
    }

    /**
     * @param $observer
     * @throws Exception
     */
    public function saveInitiatorToOrder($observer)
    {
        $id = Mage::app()->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($id);
        if (Mage::getModel('callcenter/queue')->isAllowedByRole()) {
            $userId = Mage::getSingleton('admin/session')->getUser()->getId();
            $orderGridItem = Mage::getModel('sales/order_grid')->load($order->getId());
            $order->setInitiatorId($userId);
            $orderGridItem->setInitiatorId($userId);
            if(null === $order->getPrimaryInitiatorId()){
                $order->setPrimaryInitiatorId($userId);
                $orderGridItem->setPrimaryInitiatorId($userId);
            }
            $order->save();
            $orderGridItem->save();
        }
    }

    /**
     * @param $observer
     */
    public function onAdminhtmlBlockHtmlBefore($observer)
    {
        $block = $observer->getBlock();
        if (!isset($block)) return;

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
    }
}