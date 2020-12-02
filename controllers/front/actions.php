<?php
/**
 * 2007-2019 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author     PAYCOMET <info@paycomet.com>
 *  @copyright  2019 PAYTPV ON LINE ENTIDAD DE PAGO S.L
 *  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class PaytpvActionsModuleFrontController extends ModuleFrontController
{

    public function postProcess()
    {

        if (Tools::getValue('process') == 'removeCard') {
            $this->processRemoveCard();
        }

        if (Tools::getValue('process') == 'saveDescriptionCard') {
            $this->saveDescriptionCard();
        }

        if (Tools::getValue('process') == 'cancelSuscription') {
            $this->processCancelSuscription();
        }

        if (Tools::getValue('process') == 'addCard') {
            $this->processAddCard();
        }

        if (Tools::getValue('process') == 'saveOrderInfo') {
            $this->processSaverOrderInfo();
        }

        if (Tools::getValue('process') == 'suscribe') {
            $this->processSuscribe();
        }

        exit;
    }


    /**
     * Remove card
     */
    public function processRemoveCard()
    {
        $paytpv = $this->module;

        if ($paytpv->removeCard(Tools::getValue('paytpv_iduser'))) {
            die('0');
        }
        die('1');
    }

    

    /**
     * Remove card
     */
    public function saveDescriptionCard()
    {

        if (PaytpvCustomer::saveCustomerCarDesc(
            (int) $this->context->customer->id,
            Tools::getValue('paytpv_iduser'),
            Tools::getValue('card_desc')
        )) {
            die('0');
        }
        die('1');
    }

    /**
     * Remove suscription
     */
    public function processCancelSuscription()
    {
        $paytpv = $this->module;
        $res = $paytpv->cancelSuscription(Tools::getValue('id_suscription'));
        print json_encode($res);
    }

    /**
     * add Card
     */
    public function processAddCard()
    {

        $paytpv = $this->module;

        $id_cart = Tools::getValue('id_cart');

        $cart = new Cart($id_cart);

        $paytpv_agree = Tools::getValue('paytpv_agree');
        $suscripcion = 0;
        $periodicity = 0;
        $cycles = 0;

        // Valor de compra
        $id_currency = (int) Configuration::get('PS_CURRENCY_DEFAULT');

        if (!is_object(Context::getContext()->currency)) {
            Context::getContext()->currency = new Currency($id_currency);
        }

        $total_pedido = $cart->getOrderTotal(true, Cart::BOTH);

        $datos_pedido = $paytpv->terminalCurrency($cart);
        $importe = $datos_pedido["importe"];
        $currency_iso_code = $datos_pedido["currency_iso_code"];
        $idterminal = $datos_pedido["idterminal"];
        $idterminal_ns = $datos_pedido["idterminal_ns"];
        $pass = $datos_pedido["password"];
        $pass_ns = $datos_pedido["password_ns"];


        $values = array(
            'id_cart' => $cart->id,
            'key' => Context::getContext()->customer->secure_key
        );


        $ssl = Configuration::get('PS_SSL_ENABLED');

        $URLOK = Context::getContext()->link->getModuleLink($paytpv->name, 'urlok', $values, $ssl);
        $URLKO = Context::getContext()->link->getModuleLink($paytpv->name, 'urlko', $values, $ssl);

        $paytpv_order_ref = str_pad($cart->id, 8, "0", STR_PAD_LEFT);

        $language = $paytpv->getPaycometLang($this->context->language->language_code);

        if ($idterminal > 0) {
            $secure_pay = $paytpv->isSecureTransaction($idterminal, $total_pedido, 0) ? 1 : 0;
        } else {
            $secure_pay = $paytpv->isSecureTransaction($idterminal_ns, $total_pedido, 0) ? 1 : 0;
        }

        // Miramos a ver por que terminal enviamos la operacion
        if ($secure_pay) {
            $idterminal_sel = $idterminal;
            $pass_sel = $pass;
        } else {
            $idterminal_sel = $idterminal_ns;
            $pass_sel = $pass_ns;
        }


        $arrReturn = array();
        $arrReturn["error"] = 1;
        if (PaytpvOrderInfo::saveOrderInfo(
            (int) $this->context->customer->id,
            $cart->id,
            $paytpv_agree,
            $suscripcion,
            $periodicity,
            $cycles,
            0
        )) {
            $OPERATION = "1";
            
            if ($paytpv->apikey != '') {
                
                include_once(_PS_MODULE_DIR_ . '/paytpv/classes/PaycometApiRest.php');

               
                $userInteraction = '1';
                $merchantData = $paytpv->getMerchantData($cart);

                $score = $paytpv->transactionScore($cart);
                $scoring = $score["score"];
               
                try {
                    
                    $apiRest = new PaycometApiRest($paytpv->apikey);                    
                     
                    $payment =  [
                        'terminal' => (int) $idterminal_sel,
                        'order' => (string) $paytpv_order_ref,
                        'amount' => (string) $importe,
                        'currency' => (string) $currency_iso_code,
                        'userInteraction' => (string) $userInteraction,
                        'secure' => $secure_pay,                        
                        'merchantData' => $merchantData,
                        'urlOk' => $URLOK,
                        'urlKo' => $URLKO
                    ];
                    
                    if ($scoring != null)
                        $payment['scoring'] = (int) $scoring;                    
                        
                    $formResponse = $apiRest->form(
                        $OPERATION,
                        $language,
                        $idterminal_sel,
                        '',
                        $payment
                    );                    

                    $url_paytpv = $formResponse->challengeUrl;
                    
                } catch (exception $e){                    
                    $url_paytpv = "";
                }
    
            } else {

                // Cálculo Firma
                $signature = hash('sha512', $paytpv->clientcode . $idterminal_sel . $OPERATION . $paytpv_order_ref .
                $importe . $currency_iso_code . md5($pass_sel));                           

                $score = $paytpv->transactionScore($cart);
                $MERCHANT_SCORING = $score["score"];
                $MERCHANT_DATA = $paytpv->getMerchantData($cart);

                $fields = array(
                    'MERCHANT_MERCHANTCODE' => $paytpv->clientcode,
                    'MERCHANT_TERMINAL' => $idterminal_sel,
                    'OPERATION' => $OPERATION,
                    'LANGUAGE' => $language,
                    'MERCHANT_MERCHANTSIGNATURE' => $signature,
                    'MERCHANT_ORDER' => $paytpv_order_ref,
                    'MERCHANT_AMOUNT' => $importe,
                    'MERCHANT_CURRENCY' => $currency_iso_code,
                    'URLOK' => $URLOK,
                    'URLKO' => $URLKO,
                    '3DSECURE' => $secure_pay
                );

                if ($MERCHANT_SCORING != null) {
                    $fields["MERCHANT_SCORING"] = $MERCHANT_SCORING;
                }
                if ($MERCHANT_DATA != null) {
                    $fields["MERCHANT_DATA"] = $MERCHANT_DATA;
                }

                $query = http_build_query($fields);

                $url_paytpv = $paytpv->url_paytpv . "?" . $query;

                $vhash = hash('sha512', md5($query . md5($pass_sel)));

                $url_paytpv = $paytpv->url_paytpv . "?" . $query . "&VHASH=" . $vhash;
            }


            $arrReturn["error"] = 0;
            $arrReturn["url"] = $url_paytpv;
        }

        print json_encode($arrReturn);
    }



    /**
     * save Card
     */
    public function processSaverOrderInfo()
    {
        $id_cart = Tools::getValue('id_cart');

        $cart = new Cart($id_cart);

        $paytpv_agree = Tools::getValue('paytpv_agree');
        $suscripcion = Tools::getValue('paytpv_suscripcion');
        $periodicity = Tools::getValue('paytpv_periodicity');
        $cycles = Tools::getValue('paytpv_cycles');

        $arrReturn = array();
        $arrReturn["error"] = 1;
        
        if (PaytpvOrderInfo::saveOrderInfo(
            (int) $this->context->customer->id,
            $cart->id,
            $paytpv_agree,
            $suscripcion,
            $periodicity,
            $cycles,
            0
        )) {
            $arrReturn["error"] = 0;
        }

        print json_encode($arrReturn);
    }




    /**
     * add Card
     */
    public function processSuscribe()
    {
        $paytpv = $this->module;

        $id_cart = Tools::getValue('id_cart');

        $cart = new Cart($id_cart);

        $paytpv_agree = Tools::getValue('paytpv_agree');
        $suscripcion = Tools::getValue('paytpv_suscripcion');
        $periodicity = Tools::getValue('paytpv_periodicity');
        $cycles = Tools::getValue('paytpv_cycles');

        // Valor de compra
        $id_currency = (int) Configuration::get('PS_CURRENCY_DEFAULT');

        if (!is_object(Context::getContext()->currency)) {
            Context::getContext()->currency = new Currency($id_currency);
        }

        $total_pedido = $cart->getOrderTotal(true, Cart::BOTH);

        $datos_pedido = $paytpv->terminalCurrency($cart);
        $importe = $datos_pedido["importe"];
        $currency_iso_code = $datos_pedido["currency_iso_code"];
        $idterminal = $datos_pedido["idterminal"];
        $idterminal_ns = $datos_pedido["idterminal_ns"];
        $pass = $datos_pedido["password"];
        $pass_ns = $datos_pedido["password_ns"];


        $values = array(
            'id_cart' => $cart->id,
            'key' => Context::getContext()->customer->secure_key
        );

        $ssl = Configuration::get('PS_SSL_ENABLED');

        $URLOK = Context::getContext()->link->getModuleLink($paytpv->name, 'urlok', $values, $ssl);
        $URLKO = Context::getContext()->link->getModuleLink($paytpv->name, 'urlko', $values, $ssl);

        $paytpv_order_ref = str_pad($cart->id, 8, "0", STR_PAD_LEFT);

        if ($idterminal > 0) {
            $secure_pay = $paytpv->isSecureTransaction($idterminal, $total_pedido, 0) ? 1 : 0;
        } else {
            $secure_pay = $paytpv->isSecureTransaction($idterminal_ns, $total_pedido, 0) ? 1 : 0;
        }

        // Miramos a ver por que terminal enviamos la operacion
        if ($secure_pay) {
            $idterminal_sel = $idterminal;
            $pass_sel = $pass;
        } else {
            $idterminal_sel = $idterminal_ns;
            $pass_sel = $pass_ns;
        }

        $arrReturn = array();
        $arrReturn["error"] = 1;
        if (PaytpvOrderInfo::saveOrderInfo(
            (int) $this->context->customer->id,
            $cart->id,
            $paytpv_agree,
            $suscripcion,
            $periodicity,
            $cycles,
            0
        )) {
            $OPERATION = "9";
            $subscription_startdate = date("Ymd");
            $susc_periodicity = $periodicity;
            $subs_cycles = $cycles;

            // Si es indefinido, ponemos como fecha tope la fecha + 10 años.
            if ($subs_cycles == 0) {
                $subscription_enddate = date("Y") + 5 . date("m") . date("d");
            } else {
                // Dias suscripcion
                $dias_subscription = $subs_cycles * $susc_periodicity;
                $subscription_enddate = date('Ymd', strtotime("+" . $dias_subscription . " days"));
            }            

            $language = $paytpv->getPaycometLang($this->context->language->language_code);

            $score = $paytpv->transactionScore($cart);
            $MERCHANT_SCORING = $scoring = $score["score"];

            if ($paytpv->apikey != '') {
                
                include_once(_PS_MODULE_DIR_ . '/paytpv/classes/PaycometApiRest.php');

                $merchantData = $paytpv->getMerchantData($cart);                
                
                $userInteraction = '1';
                
                try {
                    $apiRest = new PaycometApiRest($paytpv->apikey);                
                        
                    $payment =  [
                        'terminal' => (int) $idterminal_sel,
                        'order' => (string) $paytpv_order_ref,
                        'amount' => (string) $importe,
                        'currency' => (string) $currency_iso_code,
                        'userInteraction' => (string) $userInteraction,
                        'secure' => $secure_pay,                        
                        'merchantData' => $merchantData,
                        'urlOk' => $URLOK,
                        'urlKo' => $URLKO
                    ];
                    
                    if ($scoring != null)
                        $payment['scoring'] = (int) $scoring;        
                    
                    $subscription =  [
                        'startDate' => (string) $subscription_startdate,
                        'endDate' => (string) $subscription_enddate,
                        'periodicity' => $susc_periodicity
                        ];
                        
                    $formResponse = $apiRest->form(
                        $OPERATION,
                        $language,
                        $idterminal_sel,
                        '',
                        $payment,
                        $subscription
                    );       
                 
                    $url_paytpv = $formResponse->challengeUrl;
                } catch (exception $e){                    
                    $url_paytpv = "";
                }                
            
            } else {

                $signature = hash('sha512', $paytpv->clientcode . $idterminal_sel . $OPERATION . $paytpv_order_ref .
                $importe . $currency_iso_code . md5($pass_sel));                

                $fields = array(
                    'MERCHANT_MERCHANTCODE' => $paytpv->clientcode,
                    'MERCHANT_TERMINAL' => $idterminal_sel,
                    'OPERATION' => $OPERATION,
                    'LANGUAGE' => $language,
                    'MERCHANT_MERCHANTSIGNATURE' => $signature,
                    'MERCHANT_ORDER' => $paytpv_order_ref,
                    'MERCHANT_AMOUNT' => $importe,
                    'MERCHANT_CURRENCY' => $currency_iso_code,
                    'SUBSCRIPTION_STARTDATE' => $subscription_startdate,
                    'SUBSCRIPTION_ENDDATE' => $subscription_enddate,
                    'SUBSCRIPTION_PERIODICITY' => $susc_periodicity,
                    'URLOK' => $URLOK,
                    'URLKO' => $URLKO,
                    '3DSECURE' => $secure_pay
                );

                if ($MERCHANT_SCORING != null) {
                    $fields["MERCHANT_SCORING"] = $MERCHANT_SCORING;
                }
                
                $query = http_build_query($fields);                

                $vhash = hash('sha512', md5($query . md5($pass_sel)));

                $url_paytpv = $paytpv->url_paytpv . "?" . $query . "&VHASH=" . $vhash;
            }

            $arrReturn["error"] = 0;
            $arrReturn["url"] = $url_paytpv;
        }

        print json_encode($arrReturn);
    }
}
