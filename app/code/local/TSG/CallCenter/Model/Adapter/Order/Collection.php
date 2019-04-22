<?php
class TSG_CallCenter_Model_Adapter_Order_Collection extends Varien_Data_Collection
{
    /**
     * @param Mage_Sales_Model_Resource_Order_Collection $ordersCollection
     */
    public function adaptCollection(Mage_Sales_Model_Resource_Order_Collection $ordersCollection): void
    {
        $items = [];
        foreach ($ordersCollection as $order) {
            $item = $this->adaptOrder($order);
            $items[] = $item;
        }
        $this->_items = $items;
    }

    public function getItemByColumnValueLike($column, $value)
    {
        $this->load();

        foreach ($this as $item) {
            if (strpos($item->getData($column), $value) !== false) {
                return $item;
            }
        }
        return null;
    }

    /**
     * Adapt order
     *
     * @param Mage_Sales_Model_Order $order
     * @return Varien_Object
     */
    protected function adaptOrder(Mage_Sales_Model_Order $order): Varien_Object
    {
        $result = new Varien_Object();

        $result->setId($order->getId());
        $result->setCustomerEmail($order->getCustomerEmail());
        $result->setCreatedAt($order->getCreatedAt());

        $orderItems = [];
        foreach ($order->getAllItems() as $orderItem) {
            $resultOrderItem = new Varien_Object();
            //$customProductType = Mage::getModel('catalog/product')->load($orderItem->getProductId())->getAttributeText('custom_product_type');
            //$customProductType = Mage::getResourceModel('catalog/product')->getAttributeRawValue($orderItem->getProductId(), 'custom_product_type', $order->getStoreId());
            $resultOrderItem->setCustomProductType($orderItem->getCustomProductType());
            $orderItems[] = $resultOrderItem;
        }
        $result->setOrderedItems($orderItems);
        return $result;
    }
}