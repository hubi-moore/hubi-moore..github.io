<?php

class Network_ProductSubstitutes_DostawazewController extends Mage_Core_Controller_Front_Action
{
    public function getdostawazewitemsAction()
    {
        $response = array();
        $saphelper = Mage::helper('sap/products');
        $customer = Mage::getSingleton('customer/session')->getCustomer();
        $customerId = $customer->getId();
        $params = $this->getRequest()->getPost();
        if(file_exists(Mage::getBaseDir('base')."/c2perclient/".Mage::getSingleton('customer/session')->getId()))
            $c2Array=json_decode(file_get_contents(Mage::getBaseDir('base')."/c2perclient/".Mage::getSingleton('customer/session')->getId()),true);//$timehelper = Mage::helper('sap/products');
        if (isset($params['data_zew_ids']) && count($params['data_zew_ids']) > 0):
            $product_ids = explode(',', $params['data_zew_ids']);
            $response['ids'] = $product_ids;
            $product_data = array();
            $tr_ids = array();
            foreach ($product_ids as $id) {
                $product = Mage::getModel('catalog/product')->load($id);
                $inox_label = '';
                $prodRozmiary = $this->getSingleProdRozmiary($product);
                $priceRoundJM = round($saphelper->getCenaJednostkowaPoRabacieByCustomerId($product,$customerId),2);
                $pricePackage = Mage::helper('checkout')->formatPrice($product->getCalculationPrice());
                $trId = $product->getSku().'_'.$product->getData('wielkosc_opakowania');
                $newTrId = str_replace('.', '-', $trId);
                $tr_ids[] = $newTrId;
                if (strpos($product->getSku(), 'I') === 0) {
                    $inox_label = '<div class="is_inox_label_wrap"><span class="is_inox_label">INOX</span></div>';
                }
                $product_data[$id] = array(
                    'name' => $product->getName(),
                    'trid' => $newTrId,
                    'sku' => $product->getSku(),
                    'cena_jm' => '<span class="price">'.number_format($priceRoundJM, 2, ',', '').' zł'.'</span>',
                    'inox_label' => $inox_label,
                    'wielkosc_opakowania' => $product->getData('wielkosc_opakowania') . ' ' . $product->getAttributeText('jednostka_miary'),
                    'rozmiar_label' => $prodRozmiary['label'],
                    'rozmiar_value' => $prodRozmiary['value']
                );
            }
            $response['products'] = $product_data;
            $response['getURL'] = Mage::getUrl('checkout/cart');
            $response['tr_ids'] = $tr_ids;
        else:
            $response['status'] = 'Fail';
            $response['msg'] = 'No products found';
        endif;
        $response['params'] = $params;
        $this->_sendJson($response);
        return $response;
    }

    /**
     * send json respond
     *
     * @param array $response - response data
     */
    private function _sendJson($response)
    {
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode($response));
    }

    private function getSingleProdRozmiary($product)
    {
        $subStrSku = substr($product->getSku(), 0, 4);
        $rozmiary_label = '';
        $rozmiary_value = '';
        if ($subStrSku == '9611'):
            $rozmiary_label = 'Gramatura';
            $rozmiary_value = $product->getAttributeText("pimcore_gramatura");
        else:
            $rozmiary_label = 'Rozmiary';
            $defined = array();
            $currentPartials = 0;
            $maxPartials = 2;
            $namePartials = array();
            if ($product->getTypeId() == "simple") {
                $parentIds = Mage::getModel('catalog/product_type_grouped')->getParentIdsByChild($product->getId());
                if (!$parentIds)
                    $parentIds = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($product->getId());
                $parent = Mage::getModel('catalog/product')->load(max($parentIds));
                $defined = json_decode('{' . rtrim($parent->getKolumny(), ",") . '}', true);
                $hasParent = true;
            }
            $pimcoreDisables = array('pimcore_id', 'pimcore_group', 'pimcore_subgroup', 'pimcore_materialindex');
            if (empty($defined)) {
                $defined = array(
                    'rodzaj' => 'rodzaj',
                    'parametr' => 'parametr',
                    'color' => 'color',
                    "pojemnosc_ml" => 'pojemnosc_ml',
                    "kolor" => "kolor",
                    "rozmiar_parametr" => "rozmiar_parametr",
                    "rozmiar_d" => "rozmiar_d",
                    "rozmiar_l" => "rozmiar_l",
                    "rodzaj_wglebienia" => "rodzaj_wglebienia",
                    "rodzaj_gwintu" => "rodzaj_gwintu",
                    "rodzaj_pokrycia" => "rodzaj_pokrycia",
                    "rozmiar_srednica" => "rozmiar_srednica",
                    "rozmiar_drugi" => "rozmiar_drugi",
                    "rozmiar_srednica2" => "rozmiar_srednica2",
                    "rozmiar_granulacja" => "rozmiar_granulacja"
                );            }
            foreach ($pimcoreDisables as $pim_code) {
                unset($defined[$pim_code]);
            }
            if (isset($defined['pimcore_rozmiar_d_male']) && isset($defined['pimcore_rozmiar_dmale']))
                unset($defined['pimcore_rozmiar_d_male']);

            foreach ($defined as $code => $label) {
                $preVal = $product->getAttributeTextOrValue($code);
                if (strpos($code, 'rozmiar') !== false) {
                    $currentPartials++;
                    if ($currentPartials <= $maxPartials) {
                        $namePartials[] = $preVal;
                    }
                }
            }
            if (count($namePartials) > 1) {
                $rozmiary_value = implode(' x ', $namePartials);
            } else {
                $rozmiary_value = $product->getRozmiary();
            }
        endif;
        return array('label' => $rozmiary_label, 'value' => $rozmiary_value);
    }
}