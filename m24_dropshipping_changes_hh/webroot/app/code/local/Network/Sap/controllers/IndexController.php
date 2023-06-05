<?php
class Network_Sap_IndexController extends Mage_Core_Controller_Front_Action{
    public function IndexAction() {

    }

    public function activatecustomerAction(){
        ini_set('display_errors',1);

        $nip = str_replace('-','',$this->getRequest()->getParam('activatenip'));
        $customeremail = $this->getRequest()->getParam('activateemail');

        $templateId = 3;
        $sender = Array('name' => Mage::getStoreConfig('trans_email/ident_general/name'),'email' => Mage::getStoreConfig('trans_email/ident_general/email'));
        //$email = array('grzegorz@network-interactive.pl','marcinik.m@marcopol.pl');
        $emaiName = Mage::getStoreConfig('trans_email/ident_general/name');

        $email = Mage::getStoreConfig('trans_email/ident_custom1/email'); //na tego maila idzie wiadomość
        $vars = Array();
        $vars = Array(
            'nip' => $nip,
            'customeremail' => $customeremail
        );
        $storeId = Mage::app()->getStore()->getId();
        $translate = Mage::getSingleton('core/translate');

        try{
            Mage::getModel('core/email_template')->sendTransactional($templateId, $sender, $email, $emailName, $vars, $storeId);
            Mage::getSingleton('core/session')->addSuccess('Prośba o aktywację konta została wysłana.');
        }
        catch(Exception $e){
            Mage::getSingleton('core/session')->addError('Wystąpił błąd podczas wysyłania: '.$e->getMessage());
        }

        //$this->getResponse()->setBody(json_encode($response));
        $this->_redirectReferer();
        return;
    }

    public function getmailbysapidAction(){
        $response = array();
        $this->getResponse()->clearHeaders()->setHeader('Content-type','application/json',true);

        $idsap = $this->getRequest()->getParam('id_sap');
        $customer = Mage::getModel('customer/customer')
            ->getCollection()
            ->addAttributeToSelect('*')
            ->addFieldToFilter('id_sap',  $idsap)
            ->getFirstItem();
        if($customer->getId()) {
            $response['status'] = 'ok';
            $response['mail'] = $customer->getEmail();
            $this->getResponse()->setBody(json_encode($response));
            return ;
        }

    }

    public function loginAction(){
        $this->loadLayout();
        $this->renderLayout();

    }
    public function terminrealizacjiAction() {
        if (Mage::getSingleton('customer/session')->isLoggedIn()) {
            $newValue = $this->getRequest()->getParam('new_value');
            Mage::getSingleton('customer/session')->setChosenTermin($newValue);
            return $this->getResponse()->setBody(json_encode(array('new_value'=>$newValue)));
        }
        return $this->getResponse()->setBody(json_encode(array('msg'=>'customer not logged in')));
    }
    
    public function changestatusAction()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        try {
            $order = Mage::getModel('sales/order')->load((int)$orderId);
            $orderStatus = $order->getStatus();
            $orderState =  $order->getState();

            if($orderStatus === 'wyslano' && $orderState === 'processing') {
                // 'Zmień na Gotowe do odbioru';
                $order->setStatus('gotowe_do_odbioru')->setState('gotowe_do_odbioru');
            }
            if($orderStatus === 'gotowe_do_odbioru' && $orderState === 'gotowe_do_odbioru') {
                // 'Zmień na Wysłano';
                $order->setStatus("wyslano")->setState("processing");
            }
            $order->save();
            // Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Status i stan zmieniono poprawnie.'));
        } catch (Exception $e) {
            // Mage::getSingleton('adminhtml/session')->addError($this->__('Coś poszło nie tak.'));
            Mage::logException($e);
        }
        $this->_redirectReferer();
    }

    public function getshippingaddressAction()
    {
        $response = array();
        $this->getResponse()->clearHeaders()->setHeader('Content-type','application/json',true);
        $shipping_id = $this->getRequest()->getParam('shipid');
        if(!is_null($shipping_id))
        {
            $shippingAddr = Mage::getModel('customer/address')->load($shipping_id);
            $response['status'] = 'success';
            $response['address'] = $shippingAddr->toArray();
            return $this->getResponse()->setBody(json_encode($response));
        } else {
            $response['status'] = 'fail';
            return $this->getResponse()->setBody(json_encode($response));
        }
    }

    public function setasdefaultshippingAction()
    {
        $response = array();
        $this->getResponse()->clearHeaders()->setHeader('Content-type','application/json',true);
        $shipping_id = $this->getRequest()->getParam('shipid');
        if(!is_null($shipping_id))
        {
            try {
                $shippingAddr = Mage::getModel('customer/address')->load($shipping_id);
                $shippingAddr->setIsDefaultShipping('1');
                $shippingAddr->save();
                $response['status'] = 'success';
                return $this->getResponse()->setBody(json_encode($response));
            } catch (Exception $e) {
                $response['status'] = 'fail';
                $response['message'] = $e->getMessage();
                return $this->getResponse()->setBody(json_encode($response));
            }
        } else {
            $response['status'] = 'fail';
            return $this->getResponse()->setBody(json_encode($response));
        }
    }  
    
    public function updatezgodaAction()
    {
        $customerId = $this->getRequest()->getPost('customer_id');
        $isChecked = $this->getRequest()->getPost('zgoda_na_przetwarzanie_danych');
        $customer = Mage::getModel('customer/customer')->load($customerId);
        #$customer->setZgodaNaPrzetwarzanieDanych($isChecked);
        $date = date('Y-m-d H:i:s');
        try {
            $customer->setData('zgoda_na_przetwarzanie_danych',$isChecked);
            $customer->save();
            $message = "Udzielono zgody na przetwarzanie danych osobowych";
            Mage::getSingleton('core/session')->addSuccess($message);
        } catch (Exception $e) {
            Mage::getSingleton('core/session')->addWarning($e->getMessage());
        }
        $this->_redirect('customer/account');
    }
}
