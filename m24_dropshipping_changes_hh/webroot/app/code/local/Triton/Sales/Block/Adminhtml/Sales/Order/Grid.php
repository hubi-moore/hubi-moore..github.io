<?php
class Triton_Sales_Block_Adminhtml_Sales_Order_Grid extends Mage_Adminhtml_Block_Sales_Order_Grid
{
    protected function _prepareMassaction()
    {
        parent::_prepareMassaction();

		$this->getMassactionBlock()->removeItem('pdfinvoices_order');
		$this->getMassactionBlock()->removeItem('pdfshipments_order');
		$this->getMassactionBlock()->removeItem('pdfcreditmemos_order');
		$this->getMassactionBlock()->removeItem('pdfdocs_order');
		$this->getMassactionBlock()->removeItem('print_shipping_label');
		$this->getMassactionBlock()->removeItem('fooman_pdforders_order');

        $this->getMassactionBlock()->addItem('massdelete', array(
            'label'=> Mage::helper('sales')->__('Wygeneruj CSV do SAP'),
            'url'  => $this->getUrl('*/sales_order/massRegeneratecsv'),
        ));
		
		
        return $this;
    }

	protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel($this->_getCollectionClass());

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
	
	protected function _prepareColumns(){
		parent::_prepareColumns();

        $this->addColumn('datadostawy', array(
            'header' => Mage::helper('sales')->__('Data dostawy'),
            'index' => 'increment_id',
            'type'  => 'text',
            'renderer'  => 'Triton_Sales_Block_Adminhtml_Sales_Order_Renderer_Datadostawy',
            'filter_condition_callback' => array($this, '_searchInDataDostawy')
        ));
        $this->addColumn('termin_realizacji', array(
            'header' => Mage::helper('sales')->__('Termin Realizacji'),
            'index' => 'increment_id',
            'type'  => 'text',
            'renderer'  => 'Triton_Sales_Block_Adminhtml_Sales_Order_Renderer_TerminRealizacji',
            'filter_condition_callback' => array($this, '_searchInTerminRealizacji')
        ));
        $this->addColumn('prods_dostawa_zew', array(
            'header' => Mage::helper('sales')->__('Posiada produkty z poza oferty?'),
            'index' => 'increment_id',
            'type'  => 'text',
            'renderer'  => 'Triton_Sales_Block_Adminhtml_Sales_Order_Renderer_ProdsDostawaZew',
            'filter_condition_callback' => array($this, '_searchInProdsDostawaZew')
        ));
        $this->addColumn('sapnr', array(
            'header' => Mage::helper('sales')->__('SAP NR'),
            'index' => 'increment_id',
            'type'  => 'text',
            'renderer'  => 'Triton_Sales_Block_Adminhtml_Sales_Order_Renderer_Sapnr',
            'filter_condition_callback' => array($this, '_searchInSap')
        ));
		
        $this->addColumn('fv', array(
            'header' => Mage::helper('sales')->__('Link FV'),
            'index' => 'increment_id',
            'type'  => 'text',
            'renderer'  => 'Triton_Sales_Block_Adminhtml_Sales_Order_Renderer_Fv'
        ));
		$this->addColumn('sapcustomerid', array(
            'header' => Mage::helper('sales')->__('Id klienta'),
            'index' => 'increment_id',
            'type'  => 'text',
            'renderer'  => 'Triton_Sales_Block_Adminhtml_Sales_Order_Renderer_Customersap'
        ));


	}

    protected function _searchInSap($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue())
        {
            return $this;
        }


        $this->getCollection()->getSelect()
            ->join(
                array('t2' => 'mar_sales_flat_order'),
                'main_table.entity_id = t2.entity_id','t2.sapnr'
            )
            ->where("t2.sapnr like ?", "%$value%");

        return $this;



    }

    protected function _searchInDataDostawy($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue())
        {
            return $this;
        }
        $this->getCollection()->getSelect()
            ->join(
                array('t2' => 'mar_sales_flat_order'),
                'main_table.entity_id = t2.entity_id','t2.datadostawy'
            )
            ->where("t2.datadostawy like ?", "%$value%");
        return $this;
    }

    protected function _searchInTerminRealizacji($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue())
        {
            return $this;
        }
        $this->getCollection()->getSelect()
            ->join(
                array('t2' => 'mar_sales_flat_order'),
                'main_table.entity_id = t2.entity_id','t2.termin_realizacji'
            )
            ->where("t2.termin_realizacji like ?", "%$value%");
        return $this;
    }

    protected function _searchInProdsDostawaZew($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue())
        {
            return $this;
        }
        $this->getCollection()->getSelect()
            ->join(
                array('t2' => 'mar_sales_flat_order'),
                'main_table.entity_id = t2.entity_id','t2.prods_dostawa_zew'
            )
            ->where("t2.prods_dostawa_zew like ?", "%$value%");
        return $this;
    }
	

	
}
