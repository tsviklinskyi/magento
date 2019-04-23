<?php
class TSG_CallCenter_Model_Observer_Sales_Quote_Item
{
    public function setCustomProductType(Varien_Event_Observer $observer)
    {
        $quoteItem = $observer->getQuoteItem();
        $product = $observer->getProduct();
        if ($product->getAttributeText('custom_product_type')) {
            $quoteItem->setCustomProductType($product->getAttributeText('custom_product_type'));
        }
    }
}