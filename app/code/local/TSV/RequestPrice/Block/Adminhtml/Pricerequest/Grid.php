<?php

/**
 * Price Request admin grid block
 *
 * @category    TSV
 * @package     TSV_RequestPrice
 * @author      TSV
 */
class TSV_RequestPrice_Block_Adminhtml_Pricerequest_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * constructor
     *
     * @access public
     * @author TSV
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('pricerequestGrid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    /**
     * prepare collection
     *
     * @access protected
     * @return TSV_RequestPrice_Block_Adminhtml_Pricerequest_Grid
     * @author TSV
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('tsv_requestprice/pricerequest')
            ->getCollection();
        
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * prepare grid collection
     *
     * @access protected
     * @return TSV_RequestPrice_Block_Adminhtml_Pricerequest_Grid
     * @author TSV
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'entity_id',
            array(
                'header' => Mage::helper('tsv_requestprice')->__('Id'),
                'index'  => 'entity_id',
                'type'   => 'number'
            )
        );
        $this->addColumn(
            'name',
            array(
                'header'    => Mage::helper('tsv_requestprice')->__('Name'),
                'align'     => 'left',
                'index'     => 'name',
            )
        );
        $this->addColumn(
            'email',
            array(
                'header' => Mage::helper('tsv_requestprice')->__('Email'),
                'index'  => 'email',
                'type'=> 'text',

            )
        );
        $this->addColumn(
            'status',
            array(
                'header' => Mage::helper('tsv_requestprice')->__('Status'),
                'index'  => 'status',
                'type'  => 'options',
                'options' => Mage::helper('tsv_requestprice')->convertOptions(
                    Mage::getModel('tsv_requestprice/pricerequest_attribute_source_status')->getAllOptions(false)
                )

            )
        );
        if (!Mage::app()->isSingleStoreMode() && !$this->_isExport) {
            $this->addColumn(
                'store_id',
                array(
                    'header'     => Mage::helper('tsv_requestprice')->__('Store Views'),
                    'index'      => 'store_id',
                    'type'       => 'store',
                    'store_all'  => true,
                    'store_view' => true,
                    'sortable'   => false,
                    'filter_condition_callback'=> array($this, '_filterStoreCondition'),
                )
            );
        }
        $this->addColumn(
            'created_at',
            array(
                'header' => Mage::helper('tsv_requestprice')->__('Created at'),
                'index'  => 'created_at',
                'width'  => '120px',
                'type'   => 'datetime',
            )
        );
        $this->addColumn(
            'action',
            array(
                'header'  =>  Mage::helper('tsv_requestprice')->__('Action'),
                'width'   => '100',
                'type'    => 'action',
                'getter'  => 'getId',
                'actions' => array(
                    array(
                        'caption' => Mage::helper('tsv_requestprice')->__('Edit'),
                        'url'     => array('base'=> '*/*/edit'),
                        'field'   => 'id'
                    )
                ),
                'filter'    => false,
                'is_system' => true,
                'sortable'  => false,
            )
        );
        return parent::_prepareColumns();
    }

    /**
     * prepare mass action
     *
     * @access protected
     * @return TSV_RequestPrice_Block_Adminhtml_Pricerequest_Grid
     * @author TSV
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('pricerequest');
        $this->getMassactionBlock()->addItem(
            'delete',
            array(
                'label'=> Mage::helper('tsv_requestprice')->__('Delete'),
                'url'  => $this->getUrl('*/*/massDelete'),
                'confirm'  => Mage::helper('tsv_requestprice')->__('Are you sure?')
            )
        );
        $this->getMassactionBlock()->addItem(
            'status',
            array(
                'label'      => Mage::helper('tsv_requestprice')->__('Change Status'),
                'url'        => $this->getUrl('*/*/massStatus', array('_current'=>true)),
                'additional' => array(
                    'flag_status' => array(
                        'name'   => 'flag_status',
                        'type'   => 'select',
                        'class'  => 'required-entry',
                        'label'  => Mage::helper('tsv_requestprice')->__('Status'),
                        'values' => Mage::getModel('tsv_requestprice/pricerequest_attribute_source_status')
                            ->getAllOptions(true),

                    )
                )
            )
        );
        return $this;
    }

    /**
     * get the row url
     *
     * @access public
     * @param TSV_RequestPrice_Model_Pricerequest
     * @return string
     * @author TSV
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    /**
     * get the grid url
     *
     * @access public
     * @return string
     * @author TSV
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }

    /**
     * after collection load
     *
     * @access protected
     * @return TSV_RequestPrice_Block_Adminhtml_Pricerequest_Grid
     * @author TSV
     */
    protected function _afterLoadCollection()
    {
        $this->getCollection()->walk('afterLoad');
        parent::_afterLoadCollection();
    }

    /**
     * filter store column
     *
     * @access protected
     * @param TSV_RequestPrice_Model_Resource_Pricerequest_Collection $collection
     * @param Mage_Adminhtml_Block_Widget_Grid_Column $column
     * @return TSV_RequestPrice_Block_Adminhtml_Pricerequest_Grid
     * @author TSV
     */
    protected function _filterStoreCondition($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }
        $collection->addStoreFilter($value);
        return $this;
    }
}
