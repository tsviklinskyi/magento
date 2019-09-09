<?php

/**
 * Price Request admin controller
 *
 * @category    TSV
 * @package     TSV_RequestPrice
 * @author      TSV
 */
class TSV_RequestPrice_Adminhtml_Requestprice_PricerequestController extends TSV_RequestPrice_Controller_Adminhtml_RequestPrice
{
    /**
     * init the price request
     *
     * @access protected
     * @return TSV_RequestPrice_Model_Pricerequest
     */
    protected function _initPricerequest()
    {
        $pricerequestId  = (int) $this->getRequest()->getParam('id');
        $pricerequest    = Mage::getModel('tsv_requestprice/pricerequest');
        if ($pricerequestId) {
            $pricerequest->load($pricerequestId);
        }
        Mage::register('current_pricerequest', $pricerequest);
        return $pricerequest;
    }

    /**
     * default action
     *
     * @access public
     * @return void
     * @author TSV
     */
    public function indexAction()
    {
        $this->loadLayout();
        $this->_title(Mage::helper('tsv_requestprice')->__('Price Requests'))
             ->_title(Mage::helper('tsv_requestprice')->__('Price Requests'));
        $this->renderLayout();
    }

    /**
     * grid action
     *
     * @access public
     * @return void
     * @author TSV
     */
    public function gridAction()
    {
        $this->loadLayout()->renderLayout();
    }

    /**
     * edit price request - action
     *
     * @access public
     * @return void
     * @author TSV
     */
    public function editAction()
    {
        $pricerequestId    = $this->getRequest()->getParam('id');
        $pricerequest      = $this->_initPricerequest();
        if ($pricerequestId && !$pricerequest->getId()) {
            $this->_getSession()->addError(
                Mage::helper('tsv_requestprice')->__('This price request no longer exists.')
            );
            $this->_redirect('*/*/');
            return;
        }
        $data = Mage::getSingleton('adminhtml/session')->getPricerequestData(true);
        if (!empty($data)) {
            $pricerequest->setData($data);
        }
        Mage::register('pricerequest_data', $pricerequest);
        $this->loadLayout();
        $this->_title(Mage::helper('tsv_requestprice')->__('Price Requests'))
             ->_title(Mage::helper('tsv_requestprice')->__('Price Requests'));
        if ($pricerequest->getId()) {
            $this->_title($pricerequest->getName());
        } else {
            $this->_title(Mage::helper('tsv_requestprice')->__('Add price request'));
        }
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
        $this->renderLayout();
    }

    /**
     * new price request action
     *
     * @access public
     * @return void
     * @author TSV
     */
    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * save price request - action
     *
     * @access public
     * @return void
     * @author TSV
     */
    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost('pricerequest')) {
            try {
                $pricerequest = $this->_initPricerequest();
                $pricerequest->addData($data);
                $pricerequest->save();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('tsv_requestprice')->__('Price Request was successfully saved')
                );
                Mage::getSingleton('adminhtml/session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $pricerequest->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setPricerequestData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            } catch (Exception $e) {
                Mage::logException($e);
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('tsv_requestprice')->__('There was a problem saving the price request.')
                );
                Mage::getSingleton('adminhtml/session')->setPricerequestData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('tsv_requestprice')->__('Unable to find price request to save.')
        );
        $this->_redirect('*/*/');
    }

    /**
     * delete price request - action
     *
     * @access public
     * @return void
     * @author TSV
     */
    public function deleteAction()
    {
        if ( $this->getRequest()->getParam('id') > 0) {
            try {
                $pricerequest = Mage::getModel('tsv_requestprice/pricerequest');
                $pricerequest->setId($this->getRequest()->getParam('id'))->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('tsv_requestprice')->__('Price Request was successfully deleted.')
                );
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('tsv_requestprice')->__('There was an error deleting price request.')
                );
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                Mage::logException($e);
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('tsv_requestprice')->__('Could not find price request to delete.')
        );
        $this->_redirect('*/*/');
    }

    /**
     * mass delete price request - action
     *
     * @access public
     * @return void
     * @author TSV
     */
    public function massDeleteAction()
    {
        $pricerequestIds = $this->getRequest()->getParam('pricerequest');
        if (!is_array($pricerequestIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('tsv_requestprice')->__('Please select price requests to delete.')
            );
        } else {
            try {
                foreach ($pricerequestIds as $pricerequestId) {
                    $pricerequest = Mage::getModel('tsv_requestprice/pricerequest');
                    $pricerequest->setId($pricerequestId)->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('tsv_requestprice')->__('Total of %d price requests were successfully deleted.', count($pricerequestIds))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('tsv_requestprice')->__('There was an error deleting price requests.')
                );
                Mage::logException($e);
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * mass status change - action
     *
     * @access public
     * @return void
     * @author TSV
     */
    public function massStatusAction()
    {
        $pricerequestIds = $this->getRequest()->getParam('pricerequest');
        if (!is_array($pricerequestIds)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('tsv_requestprice')->__('Please select price requests.')
            );
        } else {
            try {
                foreach ($pricerequestIds as $pricerequestId) {
                $pricerequest = Mage::getSingleton('tsv_requestprice/pricerequest')->load($pricerequestId)
                            ->setStatus($this->getRequest()->getParam('status'))
                            ->setIsMassupdate(true)
                            ->save();
                }
                $this->_getSession()->addSuccess(
                    $this->__('Total of %d price requests were successfully updated.', count($pricerequestIds))
                );
            } catch (Mage_Core_Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError(
                    Mage::helper('tsv_requestprice')->__('There was an error updating price requests.')
                );
                Mage::logException($e);
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * Check if admin has permissions to visit related pages
     *
     * @access protected
     * @return boolean
     * @author TSV
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('tsv_requestprice/pricerequest');
    }
}
