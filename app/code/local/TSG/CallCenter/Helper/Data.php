<?php
class TSG_CallCenter_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Generate array of filters by hours range
     * @param $from
     * @param $to
     * @return array
     */
    public function getTimeRangeArray($from, $to)
    {
        $arr = array();
        $n = $to;
        if ($from > $to){
            $n = $from + 23;
        }
        for($i = $from; $i < $n; $i++){
            if($i == 24){
                $i = 0;
                $n = $to;
            }
            $like = $i;
            if(strlen($like) == 1){
                $like = '0' . $like;
            }
            $arr[] = array('like' => '% '.$like.':%');
        }
        return $arr;
    }

    /**
     * Generate criteria for collection by products
     * @param $productsType
     * @return array
     */
    public function generateProductsCriteria($productsType)
    {
        $criteria = array();
        switch ($productsType){
            case '1':
                $criteria = array(
                    Mage::getModel('callcenter/queue')->getProductTypes()['1'] => true
                );
                break;
            case '2':
                $criteria = array(
                    Mage::getModel('callcenter/queue')->getProductTypes()['1'] => false,
                    Mage::getModel('callcenter/queue')->getProductTypes()['2'] => true
                );
                break;
            case '3':
                $criteria = array(
                    Mage::getModel('callcenter/queue')->getProductTypes()['1'] => false,
                    Mage::getModel('callcenter/queue')->getProductTypes()['2'] => false,
                    Mage::getModel('callcenter/queue')->getProductTypes()['3'] => true
                );
                break;
            case '0':
                $criteria = array(
                    Mage::getModel('callcenter/queue')->getProductTypes()['1'] => false,
                    Mage::getModel('callcenter/queue')->getProductTypes()['2'] => false,
                    Mage::getModel('callcenter/queue')->getProductTypes()['3'] => false,
                    Mage::getModel('callcenter/queue')->getProductTypes()['0'] => true
                );
                break;
            default:
                // do nothing
                break;
        }
        return $criteria;
    }

    /**
     * Collection processing and save relations users with orders and clear queue
     * @param $ordersCollection
     * @param $productsCriteria
     * @param $initiatorId
     * @param $queueId
     * @return array
     */
    public function checkCollectionAndSaveRelations($ordersCollection, $productsCriteria, $initiatorId, $queueId)
    {
        $matchedEmails = array();
        foreach ($ordersCollection as $order) {
            $orderMatch = false;
            foreach ($order->getAllItems() as $orderItem) {
                $customProductType = Mage::getModel('catalog/product')->load($orderItem->getProductId())->getAttributeText('custom_product_type');
                //$customProductType = Mage::getResourceModel('catalog/product')->getAttributeRawValue($orderItem->getProductId(), 'custom_product_type', $order->getStoreId());
                if (true === $productsCriteria[$customProductType] && count($productsCriteria) == 1) {
                    $orderMatch = true;
                    break;
                }else{
                    if (false === $productsCriteria[$customProductType]) {
                        continue 2; // if find one 'false' go to check next order
                    }
                    if (true === $productsCriteria[$customProductType]) {
                        $orderMatch = true;
                    }
                }
            }
            if ($orderMatch) {
                $matchedEmails[] = $order->getCustomerEmail();
                $order->setInitiatorId($initiatorId);
                if(null === $order->getPrimaryInitiatorId()){
                    $order->setPrimaryInitiatorId($initiatorId);
                }
                $order->save();
                $model = Mage::getModel('callcenter/queue');
                try {
                    $model->setId($queueId)->delete();
                } catch (Exception $e){
                    echo $e->getMessage();
                }
            }
        }
        return $matchedEmails;
    }
}