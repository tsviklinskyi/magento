<?php

/**
 * Admin search model
 *
 * @category    TSV
 * @package     TSV_RequestPrice
 * @author      TSV
 */
class TSV_RequestPrice_Model_Adminhtml_Search_Pricerequest extends Varien_Object
{
    /**
     * Load search results
     *
     * @access public
     * @return TSV_RequestPrice_Model_Adminhtml_Search_Pricerequest
     * @author TSV
     */
    public function load()
    {
        $arr = array();
        if (!$this->hasStart() || !$this->hasLimit() || !$this->hasQuery()) {
            $this->setResults($arr);
            return $this;
        }
        $collection = Mage::getResourceModel('tsv_requestprice/pricerequest_collection')
            ->addFieldToFilter('name', array('like' => $this->getQuery().'%'))
            ->setCurPage($this->getStart())
            ->setPageSize($this->getLimit())
            ->load();
        foreach ($collection->getItems() as $pricerequest) {
            $arr[] = array(
                'id'          => 'pricerequest/1/'.$pricerequest->getId(),
                'type'        => Mage::helper('tsv_requestprice')->__('Price Request'),
                'name'        => $pricerequest->getName(),
                'description' => $pricerequest->getName(),
                'url' => Mage::helper('adminhtml')->getUrl(
                    '*/requestprice_pricerequest/edit',
                    array('id'=>$pricerequest->getId())
                ),
            );
        }
        $this->setResults($arr);
        return $this;
    }
}
