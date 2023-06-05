<?php
class Network_Sap_Model_Observer
{
    public function __construct()
    {

    }

    /*
     *  Mail do BOK-u o rejestracji nowego klienta w systemie
     */
    public function sendNewCustomerMail(Varien_Event_Observer $observer)
    {

//        if($_SERVER['REMOTE_ADDR'] == '46.174.210.45') {
//            return;
//        }

        $customer = $observer->getCustomer();

        $templateId = 1;
        $sender = Array('name' => Mage::getStoreConfig('trans_email/ident_general/name'), 'email' => Mage::getStoreConfig('trans_email/ident_general/email'));
        $emaiName = Mage::getStoreConfig('trans_email/ident_general/name');

        $email = Mage::getStoreConfig('trans_email/ident_custom1/email');

        foreach ($customer->getAddresses() as $address)
        {
            $adres = $address;
            break;
        }
        $vars = Array();
        $vars = Array(
                'customername'=>$customer->getName(),
                'customer'=>$customer,
                'adres' => $adres

            );
        $storeId = Mage::app()->getStore()->getId();
        $translate = Mage::getSingleton('core/translate');
        Mage::getModel('core/email_template')->sendTransactional($templateId, $sender, $email, $emailName, $vars, $storeId);
        $translate->setTranslateInline(true);
        $this->sendActivationEmail($vars);
    }


    public function saveOrderComment($observer)
    {
        $order = $observer->getEvent()->getOrder();
        $save = 0;
        if (Mage::app()->getRequest()->has('ordercomment')) {
                $save = 1;
            $comment = Mage::app()->getRequest()->get('ordercomment');
            $order->setOrdercomment($comment);
        }

        if (Mage::app()->getRequest()->has('customer_order_id')) {
                $save = 1;
            $customer_order_id = Mage::app()->getRequest()->get('customer_order_id');
            $order->setCustomerOrderId($customer_order_id);
        }

        if (Mage::app()->getRequest()->has('datadostawy')) {
            $save = 1;
            $dostawa = Mage::app()->getRequest()->get('datadostawy');
            $order->setDatadostawy($dostawa);
        }
        if (Mage::app()->getRequest()->has('termin_realizacji')) {
            $save = 1;
            $termin_realizacji = Mage::app()->getRequest()->get('termin_realizacji');
            $order->setTerminRealizacji($termin_realizacji);
        }
        if (Mage::app()->getRequest()->has('prods_dostawa_zew')) {
            $save = 1;
            $prods_dostawa_zew = Mage::app()->getRequest()->get('prods_dostawa_zew');
            $order->setProdsDostawaZew($prods_dostawa_zew);
        }

        if ($save == 1) {
            $order->save();
        }


        Mage::helper('sap')->generateCsv($order);
    }


    public function sendActivationEmail($data){

        $day = date("D");
        $dayDate = date("d-m");
        $dayDateYear = date("d-m-Y");
        $curentTime = date("d-m-Y H:i");

        $closedEnd = date("d-m-Y" . " 7:00",strtotime("+1 day", strtotime($dayDateYear)));
        $closedStart = date(date("d-m-Y"). " 16:00");

        $isWeekend = false;
        if($day== "Sun" || $day== "Sat")
            $isWeekend = true;

        if($curentTime>=$closedStart && $curentTime<$closedEnd && $isWeekend==false){
            $isWeekend = true;
        }

        $wolne=array("01-01","06-01","01-05","03-05","15-08","01-11","11-11","25-12","26-12");

        $wielkanoc = date("d-m-Y",easter_date(date('Y')));
        $wolne[] = date("d-m",easter_date(date('Y')));

        $wolne[] = date("d-m",strtotime("+1 day", strtotime($wielkanoc)));
        $wolne[] = date("d-m",strtotime("+60 day", strtotime($wielkanoc)));



        $template = 'aktywacja_konta_data';
        $emailTemplateVariables = array();


        if ($isWeekend || in_array($dayDate, $wolne))
        {
            $emailTemplateVariables['nextday'] = 'nextday';
            setcookie("register", $emailTemplateVariables['nextday'], time()+3600);  /* expire in 1 hour */

        }else{
                $emailTemplateVariables['hour'] = 'hour';
                setcookie("register", $emailTemplateVariables['hour'], time()+3600);  /* expire in 1 hour */


        }
/*
        $emailTemplate  = Mage::getModel('core/email_template')
                                ->load(20); 


                $emailTemplate->setSenderEmail(Mage::getStoreConfig('trans_email/ident_general/email', 1));

                $emailTemplate->setSenderName(Mage::getStoreConfig('trans_email/ident_general/name', 1));



        $processedTemplate = $emailTemplate->getProcessedTemplate($emailTemplateVariables);
        $customer = $data['customer'];
        try {
            $emailTemplate->send($customer->getEmail(),$data['customername'], $emailTemplateVariables);

        } catch (Exception $e) {
            var_dump($e->getMessage());
        }
*/

    }
}
