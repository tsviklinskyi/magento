<?php

class TSV_RequestPrice_IndexController extends Mage_Core_Controller_Front_Action
{
    public function popupAction()
    {
        $popup = $this->getPopupBlock();
        if ($this->getRequest()->getPost('product_sku')) {
            $popup->setProductSku($this->getRequest()->getPost('product_sku'));
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($popup->toHtml()));
        }
    }

    public function requestpriceAction()
    {
        $result['status'] = false;
        parse_str($this->getRequest()->getPost('form_data'), $params);
        if ($this->isValidParams($params)) {
            $data = [
                'name' => $params['name'],
                'email' => $params['email'],
                'product_sku' => $params['product_sku'],
                'comment' => $params['comment'],
                'stores' => [Mage::app()->getStore()->getId()]
            ];
            try {
                $pricerequest = Mage::getModel('tsv_requestprice/pricerequest');
                $pricerequest->addData($data);
                $pricerequest->save();
                $result['status'] = true;
                $result['message'] = 'Your request was successfully saved!';
            } catch (Exception $e) {
                Mage::logException($e);
                $result['message'] = 'Error when saving request';
            }
        } else {
            $result['message'] = 'Please fill correct data';
        }
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * @return TSV_RequestPrice_Block_RequestPrice_Popup
     */
    protected function getPopupBlock(): TSV_RequestPrice_Block_RequestPrice_Popup
    {
        $this->loadLayout('tsv_requestprice_popup');

        return $this->getLayout()
            ->getBlock('requestprice.popup');
    }

    /**
     * @param array $params
     * @return bool
     * @throws Zend_Validate_Exception
     */
    protected function isValidParams(array $params): bool
    {
        $error = false;

        if (!Zend_Validate::is(trim($params['name']), 'NotEmpty')) {
            $error = true;
        }

        if (!Zend_Validate::is(trim($params['email']), 'EmailAddress')) {
            $error = true;
        }

        return !$error;
    }
}