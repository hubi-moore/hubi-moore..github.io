<?php
class Triton_Sales_Block_Adminhtml_Sales_Order_Renderer_TerminRealizacji extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $orderid = $this->_getValue($row);
        $order = Mage::getModel('sales/order')->loadbyIncrementId($orderid);
        $html = $order->getTerminRealizacji();

        return $html;
    }

}
