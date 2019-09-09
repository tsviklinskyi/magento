<?php
class TSV_RequestPrice_Block_RequestPrice_Popup extends Mage_Core_Block_Template
{
    private $productSku;

    public function setProductSku($sku)
    {
        $this->productSku = $sku;
    }

    public function getProductSku()
    {
        return $this->productSku;
    }
}