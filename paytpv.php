<?php
/*
* 2007-2015 PrestaShop
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
*  @author     Mikel Martin <mmartin@paytpv.com>
*  @author     Jose Ramon Garcia <jrgarcia@paytpv.com>
*  @copyright  2015 PAYTPV ON LINE S.L.
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

if (!defined('_PS_VERSION_'))
	exit;

include_once dirname(__FILE__).'/class_registro.php';
include_once dirname(__FILE__).'/classes/Paytpv_Terminal.php';
include_once dirname(__FILE__).'/classes/Paytpv_Order.php';
include_once dirname(__FILE__).'/classes/Paytpv_Order_Info.php';
include_once dirname(__FILE__).'/classes/Paytpv_Customer.php';
include_once dirname(__FILE__).'/classes/Paytpv_Suscription.php';
include_once dirname(__FILE__).'/classes/Paytpv_Refund.php';

class Paytpv extends PaymentModule {

	private $_html = '';

	private $_postErrors = array();
	
	public function __construct() {

		$this->name = 'paytpv';
		$this->tab = 'payments_gateways';
		$this->author = 'PayTPV';
		$this->version = '7.4.10';

		
        $this->is_eu_compatible = 1;
        $this->ps_versions_compliancy = array('min' => '1.7');
        $this->controllers = array('payment', 'validation');
        

		$this->url_paytpv = "https://secure.paytpv.com/gateway/bnkgateway.php";
		
		//$this->bootstrap = true;
		// Array config:  configuration values
		$config = $this->getConfigValues();
		
		
		if (isset($config['PAYTPV_INTEGRATION']))
			$this->integration = $config['PAYTPV_INTEGRATION'];

		if (isset($config['PAYTPV_CLIENTCODE']))
			$this->clientcode = $config['PAYTPV_CLIENTCODE'];
		

		
		if (isset($config['PAYTPV_COMMERCEPASSWORD']))
			$this->commerce_password = $config['PAYTPV_COMMERCEPASSWORD'];
		if (isset($config['PAYTPV_NEWPAGEPAYMENT']))
			$this->newpage_payment = $config['PAYTPV_NEWPAGEPAYMENT'];
		if (isset($config['PAYTPV_SUSCRIPTIONS']))
			$this->suscriptions = $config['PAYTPV_SUSCRIPTIONS'];		
		if (isset($config['PAYTPV_REG_ESTADO']))
			$this->reg_estado = $config['PAYTPV_REG_ESTADO'];

		
		if (isset($config['PAYTPV_MERCHANTDATA']))
			$this->merchantdata = $config['PAYTPV_MERCHANTDATA'];
		if (isset($config['PAYTPV_FIRSTPURCHASE_SCORING']))
			$this->firstpurchase_scoring = $config['PAYTPV_FIRSTPURCHASE_SCORING'];
		if (isset($config['PAYTPV_FIRSTPURCHASE_SCORING_SCORE']))
			$this->firstpurchase_scoring_score = $config['PAYTPV_FIRSTPURCHASE_SCORING_SCORE'];
		if (isset($config['PAYTPV_SESSIONTIME_SCORING']))
			$this->sessiontime_scoring = $config['PAYTPV_SESSIONTIME_SCORING'];
		if (isset($config['PAYTPV_SESSIONTIME_SCORING_VAL']))
			$this->sessiontime_scoring_val = $config['PAYTPV_SESSIONTIME_SCORING_VAL'];
		if (isset($config['PAYTPV_SESSIONTIME_SCORING_SCORE']))
			$this->sessiontime_scoring_score = $config['PAYTPV_SESSIONTIME_SCORING_SCORE'];
		if (isset($config['PAYTPV_DCOUNTRY_SCORING']))
			$this->dcountry_scoring = $config['PAYTPV_DCOUNTRY_SCORING'];
		if (isset($config['PAYTPV_DCOUNTRY_SCORING_VAL']))
			$this->dcountry_scoring_val = $config['PAYTPV_DCOUNTRY_SCORING_VAL'];
		if (isset($config['PAYTPV_DCOUNTRY_SCORING_SCORE']))
			$this->dcountry_scoring_score = $config['PAYTPV_DCOUNTRY_SCORING_SCORE'];
		if (isset($config['PAYTPV_IPCHANGE_SCORING']))
			$this->ip_change_scoring = $config['PAYTPV_IPCHANGE_SCORING'];
		if (isset($config['PAYTPV_IPCHANGE_SCORING_SCORE']))
			$this->ip_change_scoring_score = $config['PAYTPV_IPCHANGE_SCORING_SCORE'];
		if (isset($config['PAYTPV_BROWSER_SCORING']))
			$this->browser_scoring = $config['PAYTPV_BROWSER_SCORING'];
		if (isset($config['PAYTPV_BROWSER_SCORING_SCORE']))
			$this->browser_scoring_score = $config['PAYTPV_BROWSER_SCORING_SCORE'];
		if (isset($config['PAYTPV_SO_SCORING']))
			$this->so_scoring = $config['PAYTPV_SO_SCORING'];
		if (isset($config['PAYTPV_SO_SCORING_SCORE']))
			$this->so_scoring_score = $config['PAYTPV_SO_SCORING_SCORE'];

		if (isset($config['PAYTPV_DISABLEOFFERSAVECARD']))
			$this->disableoffersavecard = $config['PAYTPV_DISABLEOFFERSAVECARD'];
		
		if (isset($config['PAYTPV_REMEMBERCARDUNSELECTED']))
			$this->remembercardunselected = $config['PAYTPV_REMEMBERCARDUNSELECTED'];

		parent::__construct();
		$this->page = basename(__FILE__, '.php');

		$this->displayName = $this->l('paytpv.com');
		$this->description = $this->l('This module allows you to accept card payments via paytpv.com');
		
		try{
			
			if (!isset($this->clientcode) OR !Paytpv_Terminal::exist_Terminal())
				$this->warning = $this->l('Missing data when configuring the module Paytpv');
		
		}catch (exception $e){}

	}

	protected function write_log(){

		if (Tools::usingSecureMode())
 			$domain = Tools::getShopDomainSsl(true);
 		else
 			$domain = Tools::getShopDomain(true);
		try{
			$url_log = "http://prestashop.paytpv.com/log_paytpv.php?dominio=".$domain."&version_modulo=".$this->version."&tienda=Prestashop&version_tienda="._PS_VERSION_;
			@file_get_contents($url_log);
		}catch (exception $e){}
	}

	
	public function runUpgradeModule(){
		$this->write_log();
		parent::runUpgradeModule();
	}



	public function install() {

		include_once(_PS_MODULE_DIR_.'/'.$this->name.'/paytpv_install.php');
		$paypal_install = new PayTpvInstall();
		$res = $paypal_install->createTables();
		if (!$res){
			$this->error = $this->l('Missing data when configuring the module Paytpv');
			return false;
		}

		$paypal_install->updateConfiguration();
		
		// Valores por defecto al instalar el módulo
		if (!parent::install() ||
			!$this->registerHook('displayPayment') ||
			!$this->registerHook('displayPaymentTop') ||
			!$this->registerHook('displayPaymentReturn') ||
			!$this->registerHook('displayMyAccountBlock') || 
			!$this->registerHook('displayAdminOrder') || 
			!$this->registerHook('displayCustomerAccount') ||
			!$this->registerHook('actionProductCancel') ||
			!$this->registerHook('displayShoppingCart') || 
			!$this->registerHook('paymentOptions') ||
			!$this->registerHook('actionFrontControllerSetMedia') ||
			!$this->registerHook('header')	
			|| !$this->registerHook('displayOrderConfirmation')
			) 
			return false;
		$this->write_log();

		
		return true;
	}

	

	public function uninstall() {
		include_once(_PS_MODULE_DIR_.'/'.$this->name.'/paytpv_install.php');
		$paypal_install = new PayTpvInstall();
		$paypal_install->deleteConfiguration();
		return parent::uninstall();
	}

	public function getPath(){
		return $this->_path;
	}

	private function _postValidation(){

	    // Show error when required fields.
		if (isset($_POST['btnSubmit']))
		{

			
			if (empty($_POST['clientcode']))
				$this->_postErrors[] = $this->l('Client Code required');
			if (empty($_POST['pass']))
				$this->_postErrors[] = $this->l('User Password required');
		

			// Check Terminal empty fields SECURE
			foreach ($_POST['term'] as $key=>$term){
				if (($_POST["terminales"][$key]==0 || $_POST["terminales"][$key]==2) && ($term=="" || !is_numeric($term)) ){
					$this->_postErrors[] = $this->l('Terminal'). " " . ($key+1) . "º. [3D SECURE] ". $this->l('Terminal number invalid');
				}

				if (($_POST["terminales"][$key]==0 || $_POST["terminales"][$key]==2) && $_POST["pass"][$key]==""){
					$this->_postErrors[] = $this->l('Terminal'). " " . ($key+1) . "º. [3D SECURE] ". $this->l('Password invalid');
				}

				if (($_POST["terminales"][$key]==0 || $_POST["terminales"][$key]==2) && $_POST["jetid"][$key]=="" && $_POST["integration"]==1){
					$this->_postErrors[] = $this->l('Terminal'). " " . ($key+1) . "º. [3D SECURE] ". $this->l('JET ID number invalid');
				}
			}

			// Check Terminal empty fields NO SECURE
			foreach ($_POST['term_ns'] as $key=>$term_ns){
				if (($_POST["terminales"][$key]==1 || $_POST["terminales"][$key]==2) && ($term_ns=="" || !is_numeric($term_ns)) ){
					$this->_postErrors[] = $this->l('Terminal'). " " . ($key+1) . "º. [NO 3D SECURE] ". $this->l('Terminal number invalid');
				}

				if (($_POST["terminales"][$key]==1 || $_POST["terminales"][$key]==2) && $_POST["pass_ns"][$key]==""){
					$this->_postErrors[] = $this->l('Terminal'). " " . ($key+1) . "º. [NO 3D SECURE] ". $this->l('Password invalid');
				}

				if (($_POST["terminales"][$key]==1 || $_POST["terminales"][$key]==2) && $_POST["jetid_ns"][$key]=="" && $_POST["integration"]==1){
					$this->_postErrors[] = $this->l('Terminal'). " " . ($key+1) . "º. [NO 3D SECURE] ". $this->l('JET ID number invalid');
				}				
			}

			// Check 3Dmin and Currency
			foreach ($_POST['term_ns'] as $key=>$term_ns){
				if ($_POST["terminales"][$key]==2 && ($_POST["tdmin"][$key]!="" && !is_numeric($_POST["tdmin"][$key]))){
					$this->_postErrors[] = $this->l('Terminal'). " " . ($key+1) . "º. " . $this->l('Use 3D Secure on purchases over invalid');
				}

				if (empty($_POST['moneda'][$key]))
					$this->_postErrors[] = $this->l('Terminal'). " " . ($key+1) . "º. ". $this->l('Currency required');
			}

			// Check Duplicate Terms
			$arrTerminales = array_unique($_POST['term']);
			if (sizeof($arrTerminales) != sizeof($_POST['term']))
				$this->_postErrors[] = $this->l('Duplicate Terminals');

			// Check Duplicate Currency
			$arrMonedas = array_unique($_POST['moneda']);
			if (sizeof($arrMonedas) != sizeof($_POST['moneda']))
				$this->_postErrors[] = $this->l('Duplicate Currency. Specify a different currency for each terminal');

		}

	}


	private function _postProcess(){

	    // Update databse configuration
		if (isset($_POST['btnSubmit'])){
			
			Configuration::updateValue('PAYTPV_CLIENTCODE', $_POST['clientcode']);

			
			Configuration::updateValue('PAYTPV_COMMERCEPASSWORD', $_POST['commerce_password']);
			Configuration::updateValue('PAYTPV_NEWPAGEPAYMENT', $_POST['newpage_payment']);
			Configuration::updateValue('PAYTPV_SUSCRIPTIONS', $_POST['suscriptions']); 

			Configuration::updateValue('PAYTPV_INTEGRATION', $_POST['integration']); 
			
			// Save Paytpv Terminals
			Paytpv_Terminal::remove_Terminals();

			foreach ($_POST["term"] as $key=>$terminal){
				$_POST['tdmin'][$key] = ($_POST['tdmin'][$key]=='' || $_POST["terminales"][$key]!=2)?0:$_POST['tdmin'][$key];
				$_POST['term'][$key] = ($_POST['term'][$key]=='')?"":$_POST['term'][$key];
				$_POST['term_ns'][$key] = ($_POST['term_ns'][$key]=='')?"":$_POST['term_ns'][$key];
				Paytpv_Terminal::add_Terminal($key+1,$_POST["term"][$key],$_POST["term_ns"][$key],$_POST["pass"][$key],$_POST["pass_ns"][$key],$_POST["jetid"][$key],$_POST["jetid_ns"][$key],$_POST["moneda"][$key],$_POST["terminales"][$key],$_POST["tdfirst"][$key],$_POST["tdmin"][$key]);
				
			}

			// Datos Scoring
        
	        Configuration::updateValue('PAYTPV_MERCHANTDATA', $_POST['merchantdata']); 
			Configuration::updateValue('PAYTPV_FIRSTPURCHASE_SCORING', $_POST['firstpurchase_scoring']); 
			Configuration::updateValue('PAYTPV_FIRSTPURCHASE_SCORING_SCORE', $_POST['firstpurchase_scoring_score']); 
			Configuration::updateValue('PAYTPV_SESSIONTIME_SCORING', $_POST['sessiontime_scoring']); 
			Configuration::updateValue('PAYTPV_SESSIONTIME_SCORING_VAL', $_POST['sessiontime_scoring_val']); 
			Configuration::updateValue('PAYTPV_SESSIONTIME_SCORING_SCORE', $_POST['sessiontime_scoring_score']); 
			Configuration::updateValue('PAYTPV_DCOUNTRY_SCORING', $_POST['dcountry_scoring']); 
			Configuration::updateValue('PAYTPV_DCOUNTRY_SCORING_VAL', isset($_POST['dcountry_scoring_val'])?implode(",",$_POST['dcountry_scoring_val']):''); 
			Configuration::updateValue('PAYTPV_DCOUNTRY_SCORING_SCORE', $_POST['dcountry_scoring_score']); 
			Configuration::updateValue('PAYTPV_IPCHANGE_SCORING', $_POST['ip_change_scoring']); 
			Configuration::updateValue('PAYTPV_IPCHANGE_SCORING_SCORE', $_POST['ip_change_scoring_score']); 
			Configuration::updateValue('PAYTPV_BROWSER_SCORING', $_POST['browser_scoring']); 
			Configuration::updateValue('PAYTPV_BROWSER_SCORING_SCORE', $_POST['browser_scoring_score']); 
			Configuration::updateValue('PAYTPV_SO_SCORING', $_POST['so_scoring']); 
			Configuration::updateValue('PAYTPV_SO_SCORING_SCORE', $_POST['so_scoring_score']); 

			Configuration::updateValue('PAYTPV_DISABLEOFFERSAVECARD', $_POST['disableoffersavecard']);
			Configuration::updateValue('PAYTPV_REMEMBERCARDUNSELECTED', $_POST['remembercardunselected']); 


			return '<div class="bootstrap"><div class="alert alert-success">'.$this->l('Configuration updated').'</div></div>';          
		}

	}


		public function transactionScore($cart){

		include_once(_PS_MODULE_DIR_.'/paytpv/paytpv_api.php');

		$api = new Paytpv_Api();

        $config = $this->getConfigValues();

       
        // Initialize array Score
        $arrScore = array();
        $arrScore["score"] = null;
        $arrScore["merchantdata"] = null;
        $arrScore["scoreCalc"] = null;

        if ($config["PAYTPV_MERCHANTDATA"]){
            $merchantData = $this->getMerchantData($cart);
            $arrScore["merchantdata"] =  urlencode(base64_encode(json_encode($merchantData)));
        }

        $shipping_address_country = "";

        $shippingAddressData = new Address($cart->id_address_delivery);
        if ($shippingAddressData){
        	$address_country = new Country($shippingAddressData->id_country);
        	$shipping_address_country = $address_country->iso_code;
        }

        // First Purchase 
        if ($config["PAYTPV_FIRSTPURCHASE_SCORING"]){
            $firstpurchase_scoring_score = $config["PAYTPV_FIRSTPURCHASE_SCORING_SCORE"];
            if (Paytpv_Order::isFirstPurchaseCustomer($this->context->customer->id)){
                $arrScore["scoreCalc"]["firstpurchase"] = $firstpurchase_scoring_score;
            }
        }

        // Complete Session Time
        if ($config["PAYTPV_SESSIONTIME_SCORING"]){
            $sessiontime_scoring_val = $config["PAYTPV_SESSIONTIME_SCORING_VAL"];
            $sessiontime_scoring_score = $config["PAYTPV_SESSIONTIME_SCORING_SCORE"];

            $cookie = $this->context->cookie;
            if ($cookie && $cookie->id_connections){
            	$connection = new Connection($cookie->id_connections);
                $first_visit_at = $connection->date_add;               

                $now = date('Y-m-d H:i:s');

                $time_ss = strtotime($now) - strtotime($first_visit_at);
                $time_mm = floor($time_ss / 60);

                if ($time_mm>$sessiontime_scoring_val){
                    $arrScore["scoreCalc"]["completesessiontime"] = $sessiontime_scoring_score;
                }
            }
        }


        // Destination 
        if ($config["PAYTPV_DCOUNTRY_SCORING"]){
            $dcountry_scoring_val = explode(",",$config["PAYTPV_DCOUNTRY_SCORING_VAL"]);
            $dcountry_scoring_score = $config["PAYTPV_DCOUNTRY_SCORING_SCORE"];

            if (in_array($shipping_address_country,$dcountry_scoring_val))
                $arrScore["scoreCalc"]["destination"] = $dcountry_scoring_score;
        }

        // Ip Change 
        if ($config["PAYTPV_IPCHANGE_SCORING"]){
        	$connection = new Connection($cookie->id_connections);
            $ip_change_scoring = $config["PAYTPV_IPCHANGE_SCORING_SCORE"];
            $ip = Tools::getRemoteAddr() ? (int)ip2long(Tools::getRemoteAddr()) : '';
            $ip_session = $connection->ip_address ? (int)ip2long($connection->ip_address) : '';

            if ($ip!=$ip_session)
                $arrScore["scoreCalc"]["ipchange"] = $ip_change_scoring;
        }

        // Browser Unidentified 
        if ($config["PAYTPV_BROWSER_SCORING"]){
            $browser_scoring_score = $config["PAYTPV_BROWSER_SCORING_SCORE"];
            if ($api->browser_detection('browser_name')=="")
                $arrScore["scoreCalc"]["browser_unidentified"] = $browser_scoring_score;

        }

        // Operating System Unidentified 
        if ($config["PAYTPV_SO_SCORING"]){
            $so_scoring_score = $config["PAYTPV_SO_SCORING_SCORE"];
            if ($api->browser_detection('os')=="")
                $arrScore["scoreCalc"]["operating_system_unidentified"] = $so_scoring_score;
        }

        // CALC ORDER SCORE
        if (sizeof($arrScore["scoreCalc"])>0){
            //$score = floor(array_sum($arrScore["scoreCalc"]) / sizeof($arrScore["scoreCalc"]));   // Media
            $score = floor(array_sum($arrScore["scoreCalc"])); // Suma de valores. Si es superior a 100 asignamos 100
            if ($score>100) $score = 100;
            $arrScore["score"] = $score;
        }
        
        return $arrScore;

    }


	public function getMerchantData($cart){
        /*Datos Scoring*/
       
        $Merchant_Data["scoring"]["customer"]["id"] = $this->context->customer->id;
        $Merchant_Data["scoring"]["customer"]["name"] = $this->context->customer->firstname;
        $Merchant_Data["scoring"]["customer"]["surname"] = $this->context->customer->lastname;
        $Merchant_Data["scoring"]["customer"]["email"] = $this->context->customer->email;

        $phone = "";

        $billing = new Address(intval($cart->id_address_invoice));
        if (!empty($billing))   $phone = $billing->phone;

        $Merchant_Data["scoring"]["customer"]["phone"] = $phone;
        $Merchant_Data["scoring"]["customer"]["mobile"] = "";
        $Merchant_Data["scoring"]["customer"]["firstBuy"] = Paytpv_Order::isFirstPurchaseCustomer($this->context->customer->id);
        
        // Shipping
        // Address
        $shippingAddressData = new Address($cart->id_address_delivery);
        if ($shippingAddressData){
            $street0 = $shippingAddressData->address1;
            $street1 = $shippingAddressData->address2;
            $shipping_address_country = new Country($shippingAddressData->id_country);
            $shipping_address_state = new State($shippingAddressData->id_state);
        }

        $Merchant_Data["scoring"]["shipping"]["address"]["streetAddress"] = ($shippingAddressData)?$street0:"";
        $Merchant_Data["scoring"]["shipping"]["address"]["extraAddress"] = ($shippingAddressData)?$street1:"";
        $Merchant_Data["scoring"]["shipping"]["address"]["city"] = ($shippingAddressData)?$shippingAddressData->city:"";
        $Merchant_Data["scoring"]["shipping"]["address"]["postalCode"] = ($shippingAddressData)?$shippingAddressData->postcode:"";
        $Merchant_Data["scoring"]["shipping"]["address"]["state"] = ($shippingAddressData)?$shipping_address_state->name:"";
        $Merchant_Data["scoring"]["shipping"]["address"]["country"] = ($shippingAddressData)?$shipping_address_country->iso_code:"";

       
        // Time
        $Merchant_Data["scoring"]["shipping"]["time"] = "";

        // Billing
        $billingAddressData = $billing;
        if ($billingAddressData){
            $street0 = $billingAddressData->address1;
            $street1 = $billingAddressData->address2;
            $billing_address_country = new Country($shippingAddressData->id_country);
            $billing_address_state = new State($shippingAddressData->id_state);
        }

        $Merchant_Data["scoring"]["billing"]["address"]["streetAddress"] = ($billingAddressData)?$street0:"";
        $Merchant_Data["scoring"]["billing"]["address"]["extraAddress"] = ($billingAddressData)?$street1:"";
        $Merchant_Data["scoring"]["billing"]["address"]["city"] = ($billingAddressData)?$billingAddressData->city:"";
        $Merchant_Data["scoring"]["billing"]["address"]["postalCode"] = ($billingAddressData)?$billingAddressData->postcode:"";
        $Merchant_Data["scoring"]["billing"]["address"]["state"] = ($billingAddressData)?$billing_address_state->name:"";
        $Merchant_Data["scoring"]["billing"]["address"]["country"] = ($billingAddressData)?$billing_address_country->iso_code:"";

        $Merchant_Data["futureData"] = "";

        return $Merchant_Data;
    }



	public function getContent() {

		$errorMessage = '';
		if (!empty($_POST)) {
			$this->_postValidation();
			if (!sizeof($this->_postErrors))
				$errorMessage = $this->_postProcess();
			else{
				$errorMessage .= '<div class="bootstrap"><div class="alert alert-warning"><strong>'.$this->l('Error').'</strong><ol>';
				foreach ($this->_postErrors AS $err)
					$errorMessage .= '<li>' . $err . '</li>';
				$errorMessage .= '</ol></div></div>';
			}
		}else
			$errorMessage = '';

		$conf_values = $this->getConfigValues();

		if (Tools::isSubmit('id_cart'))
			$this->validateOrder($_GET['id_cart'], _PS_OS_PAYMENT_, $_GET['amount'], $this->displayName, NULL);

		if (Tools::isSubmit('id_registro'))
			class_registro::remove($_GET['id_registro']);
		
		$carritos = class_registro::select();

		$id_currency = intval(Configuration::get('PS_CURRENCY_DEFAULT'));
		$currency_array =   Currency::getCurrenciesByIdShop(Context::getContext()->shop->id);

		if (Configuration::get('PS_RESTRICT_DELIVERED_COUNTRIES')) {
            $countries = Carrier::getDeliveredCountries($this->context->language->id, true, true);
        } else {
            $countries = Country::getCountries($this->context->language->id, true);
        }

        // Datos Scoring
        $merchantdata = isset($_POST["merchantdata"])?$_POST["merchantdata"]:$conf_values['PAYTPV_MERCHANTDATA'];

        $firstpurchase_scoring = isset($_POST["firstpurchase_scoring"])?$_POST["firstpurchase_scoring"]:$conf_values['PAYTPV_FIRSTPURCHASE_SCORING'];
        $firstpurchase_scoring_score = isset($_POST["firstpurchase_scoring_score"])?$_POST["firstpurchase_scoring_score"]:$conf_values['PAYTPV_FIRSTPURCHASE_SCORING_SCORE'];

        $sessiontime_scoring = isset($_POST["sessiontime_scoring"])?$_POST["sessiontime_scoring"]:$conf_values['PAYTPV_SESSIONTIME_SCORING'];
        $sessiontime_scoring_val = isset($_POST["sessiontime_scoring_val"])?$_POST["sessiontime_scoring_val"]:$conf_values['PAYTPV_SESSIONTIME_SCORING_VAL'];
        $sessiontime_scoring_score = isset($_POST["sessiontime_scoring_score"])?$_POST["sessiontime_scoring_score"]:$conf_values['PAYTPV_SESSIONTIME_SCORING_SCORE'];


        $dcountry_scoring = isset($_POST["dcountry_scoring"])?$_POST["dcountry_scoring"]:$conf_values['PAYTPV_DCOUNTRY_SCORING'];
        $dcountry_scoring_val = isset($_POST["dcountry_scoring_val"])?implode(",",$_POST["dcountry_scoring_val"]):$conf_values['PAYTPV_DCOUNTRY_SCORING_VAL'];
        $arr_dcountry_scoring_val = explode(",",$dcountry_scoring_val);

        $dcountry_scoring_score = isset($_POST["dcountry_scoring_score"])?$_POST["dcountry_scoring_score"]:$conf_values['PAYTPV_DCOUNTRY_SCORING_SCORE'];

        $ip_change_scoring = isset($_POST["ip_change_scoring"])?$_POST["ip_change_scoring"]:$conf_values['PAYTPV_IPCHANGE_SCORING'];
        $ip_change_scoring_score = isset($_POST["ip_change_scoring_score"])?$_POST["ip_change_scoring_score"]:$conf_values['PAYTPV_IPCHANGE_SCORING_SCORE'];

        $browser_scoring = isset($_POST["browser_scoring"])?$_POST["browser_scoring"]:$conf_values['PAYTPV_BROWSER_SCORING'];
        $browser_scoring_score = isset($_POST["browser_scoring_score"])?$_POST["browser_scoring_score"]:$conf_values['PAYTPV_BROWSER_SCORING_SCORE'];

        $so_scoring = isset($_POST["so_scoring"])?$_POST["so_scoring"]:$conf_values['PAYTPV_SO_SCORING'];
        $so_scoring_score = isset($_POST["so_scoring_score"])?$_POST["so_scoring_score"]:$conf_values['PAYTPV_SO_SCORING_SCORE'];

        $disableoffersavecard = isset($_POST["disableoffersavecard"])?$_POST["disableoffersavecard"]:$conf_values['PAYTPV_DISABLEOFFERSAVECARD'];
        $remembercardunselected = isset($_POST["remembercardunselected"])?$_POST["remembercardunselected"]:$conf_values['PAYTPV_REMEMBERCARDUNSELECTED'];
		

        //print_r($countries);



		$ssl = Configuration::get('PS_SSL_ENABLED');
		// Set the smarty env
		$this->context->smarty->assign('serverRequestUri', Tools::safeOutput($_SERVER['REQUEST_URI']));
		$this->context->smarty->assign('displayName', Tools::safeOutput($this->displayName));
		$this->context->smarty->assign('description', Tools::safeOutput($this->description));
		$this->context->smarty->assign('currentindex',AdminController::$currentIndex);
		$this->context->smarty->assign('token',$_GET['token']);
		$this->context->smarty->assign('name', $this->name);
		$this->context->smarty->assign('reg_estado', $conf_values['PAYTPV_REG_ESTADO']);
		$this->context->smarty->assign('carritos', $carritos);
		$this->context->smarty->assign('errorMessage',$errorMessage);

		
		$this->context->smarty->assign('integration', (isset($_POST["integration"]))?$_POST["integration"]:$conf_values['PAYTPV_INTEGRATION']);
		$this->context->smarty->assign('clientcode', (isset($_POST["clientcode"]))?$_POST["clientcode"]:$conf_values['PAYTPV_CLIENTCODE']);

		$this->context->smarty->assign('terminales_paytpv', $this->obtenerTerminalesConfigurados($_POST));
	
		$this->context->smarty->assign('commerce_password', (isset($_POST["commerce_password"]))?$_POST["commerce_password"]:$conf_values['PAYTPV_COMMERCEPASSWORD']);
		$this->context->smarty->assign('newpage_payment', (isset($_POST["newpage_payment"]))?$_POST["newpage_payment"]:$conf_values['PAYTPV_NEWPAGEPAYMENT']);
		$this->context->smarty->assign('suscriptions', (isset($_POST["suscriptions"]))?$_POST["suscriptions"]:$conf_values['PAYTPV_SUSCRIPTIONS']);
		$this->context->smarty->assign('currency_array', $currency_array);
		$this->context->smarty->assign('default_currency', $id_currency);
		$this->context->smarty->assign('OK',Context::getContext()->link->getModuleLink($this->name, 'urlok',array(),$ssl));
		$this->context->smarty->assign('KO',Context::getContext()->link->getModuleLink($this->name, 'urlko',array(),$ssl));
		$this->context->smarty->assign('NOTIFICACION',Context::getContext()->link->getModuleLink($this->name, 'url',array(),$ssl));
		$this->context->smarty->assign('base_dir', __PS_BASE_URI__);

		// Scoring Data.

		$this->context->smarty->assign('countries', $countries);

		$this->context->smarty->assign('merchantdata', $merchantdata);
		$this->context->smarty->assign('firstpurchase_scoring', $firstpurchase_scoring);
		$this->context->smarty->assign('firstpurchase_scoring_score', $firstpurchase_scoring_score);
		$this->context->smarty->assign('sessiontime_scoring', $sessiontime_scoring);
		$this->context->smarty->assign('sessiontime_scoring_val', $sessiontime_scoring_val);
		$this->context->smarty->assign('sessiontime_scoring_score', $sessiontime_scoring_score);
		$this->context->smarty->assign('dcountry_scoring', $dcountry_scoring);
		$this->context->smarty->assign('arr_dcountry_scoring_val', $arr_dcountry_scoring_val);
		$this->context->smarty->assign('dcountry_scoring_score', $dcountry_scoring_score);
		$this->context->smarty->assign('ip_change_scoring', $ip_change_scoring);
		$this->context->smarty->assign('ip_change_scoring_score', $ip_change_scoring_score);
		$this->context->smarty->assign('browser_scoring', $browser_scoring);
		$this->context->smarty->assign('browser_scoring_score', $browser_scoring_score);
		$this->context->smarty->assign('so_scoring', $so_scoring);
		$this->context->smarty->assign('so_scoring_score', $so_scoring_score);

		$this->context->smarty->assign('disableoffersavecard', $disableoffersavecard);
		$this->context->smarty->assign('remembercardunselected', $remembercardunselected);

		$this->context->controller->addCSS( $this->_path . 'css/admin.css' , 'all' );
		return $this->display(__FILE__, 'views/admin.tpl');

	}

	 
    function obtenerTerminalesConfigurados($params) {
    	if (isset($params["term"])){
    		foreach ($params["term"] as $key=>$term){
    			$terminales[$key]["idterminal"] = $params["term"][$key];
    			$terminales[$key]["password"] = $params["pass"][$key];
    			$terminales[$key]["jetid"] = $params["jetid"][$key];
    			$terminales[$key]["idterminal_ns"] = $params["term_ns"][$key];
    			$terminales[$key]["password_ns"] = $params["pass_ns"][$key];
    			$terminales[$key]["jetid_ns"] = $params["jetid_ns"][$key];
    			$terminales[$key]["terminales"] = $params["terminales"][$key];
    			$terminales[$key]["tdfirst"] = $params["tdfirst"][$key];
    			$terminales[$key]["tdmin"] = $params["tdmin"][$key];
    			$terminales[$key]["currency_iso_code"] = $params["moneda"][$key];
    		}
    		
    	}else{
    		$terminales = Paytpv_Terminal::get_Terminals();
    		if (sizeof($terminales)==0){
    			$id_currency = intval(Configuration::get('PS_CURRENCY_DEFAULT'));
				$currency = new Currency(intval($id_currency));	

    			$terminales[0]["idterminal"] = "";
    			$terminales[0]["password"] = "";
    			$terminales[0]["jetid"] = "";
    			$terminales[0]["idterminal_ns"] = "";
    			$terminales[0]["password_ns"] = "";
    			$terminales[0]["jetid_ns"] = "";
    			$terminales[0]["terminales"] = 0;
    			$terminales[0]["tdfirst"] = 1;
    			$terminales[0]["tdmin"] = 0;
    			$terminales[0]["currency_iso_code"] = $currency->iso_code;
    		}
    	}
    	return $terminales;
    }

    public function hookHeader()
    {
    	// call your media file like this
    	$this->context->controller->addJqueryPlugin('fancybox');
		$this->context->controller->registerStylesheet('paytpv-payment', 'modules/paytpv/css/payment.css');
		$this->context->controller->registerStylesheet('paytpv-fullscreen', 'modules/paytpv/css/fullscreen.css');
		$this->context->controller->registerJavascript('paytpv-js', 'modules/paytpv/js/paytpv.js');

        $paytpv_integration = intval(Configuration::get('PAYTPV_INTEGRATION'));

		// Bankstore JET
		if ($paytpv_integration==1){
        	$this->context->controller->registerJavascript('paytpv-jet', 'modules/paytpv/js/paytpv_jet.js');
        }
		$this->context->controller->registerJavascript('paytpv-fancybox', 'modules/paytpv/js/jquery.fancybox.pack.js');

    }

    public function hookActionFrontControllerSetMedia($params){

	}
   

	public function hookDisplayShoppingCart()
	{
		$this->context->controller->registerJavascript($this->name.'_js', $this->_path.'/js/paytpv.js');

		$this->context->controller->addCSS( $this->_path . 'css/payment.css' , 'all' );
		$this->context->controller->addCSS( $this->_path . 'css/fullscreen.css' , 'all' );
		$this->context->controller->addJS( $this->_path . 'js/paytpv.js');
	}

	

	public function hookDisplayPaymentTop($params) {
		
		$this->context->controller->addCSS( $this->_path . 'css/payment.css' , 'all' );
		$this->context->controller->addCSS( $this->_path . 'css/fullscreen.css' , 'all' );
		$this->context->controller->addJS( $this->_path . 'js/paytpv.js');
		
	}

	public function getTemplateVarInfos()
    {

    	$cart = $this->context->cart;
		$datos_pedido = $this->TerminalCurrency($cart);
		$idterminal = $datos_pedido["idterminal"];
		$idterminal_ns = $datos_pedido["idterminal_ns"];
		$jetid = $datos_pedido["jetid"];
		$jetid_ns = $datos_pedido["jetid_ns"];

		$importe_tienda = $cart->getOrderTotal(true, Cart::BOTH);

		if ($idterminal>0)
			$secure_pay = $this->isSecureTransaction($idterminal,$importe_tienda,0)?1:0;
		else
			$secure_pay = $this->isSecureTransaction($idterminal_ns,$importe_tienda,0)?1:0;

		// Miramos a ver por que terminal enviamos la operacion
		if ($secure_pay){
			$jetid_sel = $jetid;
		}else{
			$jetid_sel = $jetid_ns;
		}


	    // Valor de compra				
		$id_currency = intval(Configuration::get('PS_CURRENCY_DEFAULT'));
		$currency = new Currency(intval($id_currency));		

		$importe = number_format($cart->getOrderTotal(true, Cart::BOTH)*100, 0, '.', '');		

		$ssl = Configuration::get('PS_SSL_ENABLED');
		$values = array(
			'id_cart' => (int)$cart->id,
			'key' => Context::getContext()->customer->secure_key
		);

		$active_suscriptions = intval(Configuration::get('PAYTPV_SUSCRIPTIONS'));

		$saved_card = Paytpv_Customer::get_Cards_Customer((int)$this->context->customer->id);
		$index = 0;
		foreach ($saved_card as $key=>$val){
			$values_aux = array_merge($values,array("TOKEN_USER"=>$val["TOKEN_USER"]));
			$saved_card[$key]['url'] = Context::getContext()->link->getModuleLink($this->name, 'capture',$values_aux,$ssl);	
			$index++;
		}
		$saved_card[$index]['url'] = 0;

		$paytpv_integration = intval(Configuration::get('PAYTPV_INTEGRATION'));
		$newpage_payment = intval(Configuration::get('PAYTPV_NEWPAGEPAYMENT'));

		$disableoffersavecard = Configuration::get('PAYTPV_DISABLEOFFERSAVECARD');
		$remembercardunselected = Configuration::get('PAYTPV_REMEMBERCARDUNSELECTED');

	
		$language_data = explode("-",$this->context->language->language_code);
		$language = $language_data[0];

		return array(
            'msg_paytpv' => '',
            'active_suscriptions'=>$active_suscriptions,
            'saved_card'=>$saved_card,
            'commerce_password'=>$this->commerce_password,
            'id_cart' => $cart->id,
            'paytpv_iframe' => $this->paytpv_iframe_URL(),
            'paytpv_integration' => $paytpv_integration,
            'jet_id' => $jetid_sel,
            'jet_lang' => $language,
            'paytpv_jetid_url' => Context::getContext()->link->getModuleLink($this->name, 'capture',array(),$ssl),
            'base_dir' => __PS_BASE_URI__,
            'capture_url' => Context::getContext()->link->getModuleLink($this->name, 'capture',$values,$ssl),
            'this_path' => $this->_path,
            'hookpayment' => 1,
            'newpage_payment' => $newpage_payment,
            'disableoffersavecard' => $disableoffersavecard,
            'remembercardunselected'=> $remembercardunselected
        );

    }

	public function hookPaymentOptions()
	{
		

	 	// Check New Page payment
		$newpage_payment = intval(Configuration::get('PAYTPV_NEWPAGEPAYMENT'));
		$paytpv_integration = intval(Configuration::get('PAYTPV_INTEGRATION'));
		if ($newpage_payment==1){

			$urltpv = Context::getContext()->link->getModuleLink($this->name, 'payment');
	        
			$form_paytpv = '<form id="payment-form" method="POST" action="'.$urltpv.'"></form>';

			$this->context->smarty->assign('this_path',$this->_path);
			$newOption = new PaymentOption();
			$newOption->setCallToActionText($this->trans('Paga con Tarjeta', array(), 'Modules.Paytpv.Shop'))

			->setLogo(_MODULE_DIR_.'paytpv/views/img/paytpv_logo.svg')
			//->setAdditionalInformation($this->fetch('module:paytpv/views/templates/hook/payment_newpage.tpl'))
			->setForm($form_paytpv)
			->setAction($this->urltpv);
			$payment_options = [
	            $newOption,
	        ];


			//return $this->display(__FILE__, 'payment_newpage.tpl');
		}else{

			$this->smarty->assign(
	            $this->getTemplateVarInfos()
	        );
			
			$newOption = new PaymentOption();
			$newOption->setBinary(true);
			$newOption->setCallToActionText($this->trans('Paga con Tarjeta', array(), 'Modules.Paytpv.Shop'))
				->setAdditionalInformation($this->fetch('module:paytpv/views/templates/hook/payment_bsiframe_hook.tpl'));
			$payment_options = [
	            $newOption,
	        ];

		}
		return $payment_options;
	}

	public function hookDisplayPayment($params) {

		// Check New Page payment
		$newpage_payment = intval(Configuration::get('PAYTPV_NEWPAGEPAYMENT'));
		$paytpv_integration = intval(Configuration::get('PAYTPV_INTEGRATION'));

		$disableoffersavecard = Configuration::get('PAYTPV_DISABLEOFFERSAVECARD');
		$remembercardunselected = Configuration::get('PAYTPV_REMEMBERCARDUNSELECTED');

		if ($newpage_payment==1){
			$this->context->smarty->assign('this_path',$this->_path);
			return $this->display(__FILE__, 'payment_newpage.tpl');
		}else{

			$cart = Context::getContext()->cart;
			$datos_pedido = $this->TerminalCurrency($cart);
			$idterminal = $datos_pedido["idterminal"];
			$idterminal_ns = $datos_pedido["idterminal_ns"];
			$jetid = $datos_pedido["jetid"];
			$jetid_ns = $datos_pedido["jetid_ns"];

			$importe_tienda = $cart->getOrderTotal(true, Cart::BOTH);

			if ($idterminal>0)
				$secure_pay = $this->isSecureTransaction($idterminal,$importe_tienda,0)?1:0;
			else
				$secure_pay = $this->isSecureTransaction($idterminal_ns,$importe_tienda,0)?1:0;

			// Miramos a ver por que terminal enviamos la operacion
			if ($secure_pay){
				$jetid_sel = $jetid;
			}else{
				$jetid_sel = $jetid_ns;
			}

			$this->context->smarty->assign('msg_paytpv',"");
			
			$msg_paytpv = "";

			$this->context->smarty->assign('msg_paytpv',$msg_paytpv);
			

		    // Valor de compra				
			$id_currency = intval(Configuration::get('PS_CURRENCY_DEFAULT'));

			$currency = new Currency(intval($id_currency));		

			$importe = number_format($params['cart']->getOrderTotal(true, Cart::BOTH)*100, 0, '.', '');		

			$paytpv_order_ref = str_pad($params['cart']->id, 8, "0", STR_PAD_LEFT);
			$ssl = Configuration::get('PS_SSL_ENABLED');
			$values = array(
				'id_cart' => (int)$params['cart']->id,
				'key' => Context::getContext()->customer->secure_key
			);

			$active_suscriptions = intval(Configuration::get('PAYTPV_SUSCRIPTIONS'));

			$saved_card = Paytpv_Customer::get_Cards_Customer((int)$this->context->customer->id);
			$index = 0;
			foreach ($saved_card as $key=>$val){
				$values_aux = array_merge($values,array("TOKEN_USER"=>$val["TOKEN_USER"]));
				$saved_card[$key]['url'] = Context::getContext()->link->getModuleLink($this->name, 'capture',$values_aux,$ssl);	
				$index++;
			}
			$saved_card[$index]['url'] = 0;

			$tmpl_vars = array();
			$tmpl_vars['capture_url'] = Context::getContext()->link->getModuleLink($this->name, 'capture',$values,$ssl);
			$this->context->smarty->assign('active_suscriptions',$active_suscriptions);
			$this->context->smarty->assign('saved_card',$saved_card);
			$this->context->smarty->assign('commerce_password',$this->commerce_password);
			$this->context->smarty->assign('id_cart',$params['cart']->id);
			
			$this->context->smarty->assign('paytpv_iframe',$this->paytpv_iframe_URL());

			$this->context->smarty->assign('newpage_payment',$newpage_payment);
			$this->context->smarty->assign('paytpv_integration',$paytpv_integration);

			$this->context->smarty->assign('jet_id',$jetid_sel);

			$language_data = explode("-",$this->context->language->language_code);
			$language = $language_data[0];

			$this->context->smarty->assign('jet_lang',$language);

			$this->context->smarty->assign('paytpv_jetid_url',Context::getContext()->link->getModuleLink($this->name, 'capture',array(),$ssl));

			$this->context->smarty->assign('disableoffersavecard',$disableoffersavecard);
			$this->context->smarty->assign('remembercardunselected',$remembercardunselected);


			$this->context->smarty->assign('base_dir', __PS_BASE_URI__);

			
			$tmpl_vars = array_merge(
				array(
				'this_path' => $this->_path)
			);
			$this->context->smarty->assign($tmpl_vars);

			// Bankstore JET
			if ($paytpv_integration==1){

				$this->context->smarty->assign('js_code', $this->js_minimized_jet());
			}

			return $this->display(__FILE__, 'payment_bsiframe.tpl');
		}

	}


	public function js_minimized_jet(){

		$paytpv_integration = intval(Configuration::get('PAYTPV_INTEGRATION'));

		// Bankstore JET
		if ($paytpv_integration==1){
			include_once(_PS_MODULE_DIR_.'/paytpv/lib/Minifier.php');

			$js_code = "function buildED() {
			    var t = document.getElementById('expiry_date').value,
			        n = t.substr(0, 2),
			        a = t.substr(3, 2);
			    $('[data-paytpv=\'dateMonth\']').val(n), $('[data-paytpv=\'dateYear\']').val(a)
			}

			(function() {
					(function() {
						var $,
						__indexOf = [].indexOf || function(item) { for (var i = 0, l = this.length; i < l; i++) { if (i in this && this[i] === item) return i; } return -1; };

						$ = jQuery;

			$.fn.validateCreditCard = function(callback, options) {
				var bind, card, card_type, card_types, get_card_type, is_valid_length, is_valid_luhn, normalize, validate, validate_number, _i, _len, _ref;
		    	card_types = [
		      {
		        name: 'amex',
		        pattern: /^3[47]/,
		        valid_length: [15]
		      }, {
		        name: 'diners_club_carte_blanche',
		        pattern: /^30[0-5]/,
		        valid_length: [14]
		      }, {
		        name: 'diners_club_international',
		        pattern: /^36/,
		        valid_length: [14]
		      }, {
		        name: 'jcb',
		        pattern: /^35(2[89]|[3-8][0-9])/,
		        valid_length: [16]
		      }, {
		        name: 'laser',
		        pattern: /^(6304|670[69]|6771)/,
		        valid_length: [16, 17, 18, 19]
		      }, {
		        name: 'visa_electron',
		        pattern: /^(4026|417500|4508|4844|491(3|7))/,
		        valid_length: [16]
		      }, {
		        name: 'visa',
		        pattern: /^4/,
		        valid_length: [16]
		      }, {
		        name: 'mastercard',
		        // 20160603 2U7-GQS-M6X3 Cambiamos el patern ya que MC ha incluido nuevos rangos de bines
		        pattern: /^(5[1-5]|222|2[3-6]|27[0-1]|2720)/,
		        // 20160603 2U7-GQS-M6X3 Fin
		        valid_length: [16]
		      }, {
		        name: 'maestro',
		        pattern: /^(5018|5020|5038|6304|6759|676[1-3])/,
		        valid_length: [12, 13, 14, 15, 16, 17, 18, 19]
		      }, {
		        name: 'discover',
		        pattern: /^(6011|622(12[6-9]|1[3-9][0-9]|[2-8][0-9]{2}|9[0-1][0-9]|92[0-5]|64[4-9])|65)/,
		        valid_length: [16]
		      }
		    ];
		    bind = false;
		    if (callback) {
		      if (typeof callback === 'object') {
		        options = callback;
		        bind = false;
		        callback = null;
		      } else if (typeof callback === 'function') {
		        bind = true;
		      }
		    }
		    if (options === null) {
		      options = {};
		    }
		    if (options.accept === null) {
		      options.accept = (function() {
		        var _i, _len, _results;
		        _results = [];
		        for (_i = 0, _len = card_types.length; _i < _len; _i++) {
		          card = card_types[_i];
		          _results.push(card.name);
		        }
		        return _results;
		      })();
		    }
		    _ref = options.accept;
		    for (_i = 0, _len = _ref.length; _i < _len; _i++) {
		      card_type = _ref[_i];
		      if (__indexOf.call((function() {
		        var _j, _len1, _results;
		        _results = [];
		        for (_j = 0, _len1 = card_types.length; _j < _len1; _j++) {
		          card = card_types[_j];
		          _results.push(card.name);
		        }
		        return _results;
		      })(), card_type) < 0) {
		        throw '". $this->l('Credit Card Not Valid')."';
		      }
		    }
		    get_card_type = function(number) {
		      var _j, _len1, _ref1;
		      _ref1 = (function() {
		        var _k, _len1, _ref1, _results;
		        _results = [];
		        for (_k = 0, _len1 = card_types.length; _k < _len1; _k++) {
		          card = card_types[_k];
		          if (_ref1 = card.name, __indexOf.call(options.accept, _ref1) >= 0) {
		            _results.push(card);
		          }
		        }
		        return _results;
		      })();
		      for (_j = 0, _len1 = _ref1.length; _j < _len1; _j++) {
		        card_type = _ref1[_j];
		        if (number.match(card_type.pattern)) {
		          return card_type;
		        }
		      }
		      return null;
		    };
		    is_valid_luhn = function(number) {
		      var digit, n, sum, _j, _len1, _ref1;
		      sum = 0;
		      _ref1 = number.split('').reverse();
		      for (n = _j = 0, _len1 = _ref1.length; _j < _len1; n = ++_j) {
		        digit = _ref1[n];
		        digit = +digit;
		        if (n % 2) {
		          digit *= 2;
		          if (digit < 10) {
		            sum += digit;
		          } else {
		            sum += digit - 9;
		          }
		        } else {
		          sum += digit;
		        }
		      }
		      return sum % 10 === 0;
		    };
		    is_valid_length = function(number, card_type) {
		      var _ref1;
		      return _ref1 = number.length, __indexOf.call(card_type.valid_length, _ref1) >= 0;
		    };
		    validate_number = (function(_this) {
		      return function(number) {
		        var length_valid, luhn_valid;
		        card_type = get_card_type(number);
		        luhn_valid = false;
		        length_valid = false;
		        if (card_type !== null) {
		          luhn_valid = is_valid_luhn(number);
		          length_valid = is_valid_length(number, card_type);
		        }
		        return {
		          card_type: card_type,
		          valid: luhn_valid && length_valid,
		          luhn_valid: luhn_valid,
		          length_valid: length_valid
		        };
		      };
		    })(this);
		    validate = (function(_this) {
		      return function() {
		        var number;
		        number = normalize($(_this).val());
		        return validate_number(number);
		      };
		    })(this);
		    normalize = function(number) {
		      return number.replace(/[ -]/g, '');
		    };
		    if (!bind) {
		      return validate();
		    }
		    this.on('input.jccv', (function(_this) {
		      return function() {
		        $(_this).off('keyup.jccv');
		        return callback.call(_this, validate());
		      };
		    })(this));
		    this.on('keyup.jccv', (function(_this) {
		      return function() {
		        return callback.call(_this, validate());
		      };
		    })(this));
			    callback.call(this, validate());
			    return this;
			  };

			}).call(this);
				$(function() {
					return $('[data-paytpv=\'paNumber\']').validateCreditCard(function(result) {
			    	$(this).removeClass().addClass('paytpv_merchant_pan');
						if (result.card_type === null) {
							return;
						}
						$(this).addClass(result.card_type.name);
						if (result.valid) {
							return $(this).addClass('valid');
						} else {
							return $(this).removeClass('valid');
						}
					}, {
					accept: ['visa', 'visa_electron', 'mastercard', 'maestro', 'discover', 'amex']
					});
				});
			}).call(this);

			$(document).ready(function() {
				var oldLength = 0;
				$('#expiry_date').on('input',function(){
					var curLength = $(this).val().length;
					if(!$(this).val().match(/[/]/)) {
						if((curLength === 2) && (oldLength<curLength) ){
							var newInput = $(this).val();
							newInput += '/';
							$(this).val(newInput);
						}
					}
					oldLength = curLength;
				});
			})";
			return $js_code;
		}
	}


	public function paytpv_iframe_URL(){	
		$cart = Context::getContext()->cart;

		// if not exist Cart -> Redirect to home
		if (!isset($cart->id)){
			 Tools::redirect('index');
		}

		$total_pedido = $cart->getOrderTotal(true, Cart::BOTH);

		$datos_pedido = $this->TerminalCurrency($cart);
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
		
		$URLOK=Context::getContext()->link->getModuleLink($this->name, 'urlok',$values,$ssl);
		$URLKO=Context::getContext()->link->getModuleLink($this->name, 'urlko',$values,$ssl);

		$paytpv_order_ref = str_pad($cart->id, 8, "0", STR_PAD_LEFT);

		if ($idterminal>0)
			$secure_pay = $this->isSecureTransaction($idterminal,$total_pedido,0)?1:0;
		else
			$secure_pay = $this->isSecureTransaction($idterminal_ns,$total_pedido,0)?1:0;

		// Miramos a ver por que terminal enviamos la operacion
		if ($secure_pay){
			$idterminal_sel = $idterminal;
			$pass_sel = $pass;
		}else{
			$idterminal_sel = $idterminal_ns;
			$pass_sel = $pass_ns;
		}

		$language_data = explode("-",$this->context->language->language_code);
		$language = $language_data[0];
		
		$score = $this->transactionScore($cart);
        $MERCHANT_SCORING = $score["score"];
        $MERCHANT_DATA = $score["merchantdata"];
	

		$OPERATION = "1";
		// Cálculo Firma
		$signature = md5($this->clientcode.$idterminal_sel.$OPERATION.$paytpv_order_ref.$importe.$currency_iso_code.md5($pass_sel));
		$fields = array
		(
			'MERCHANT_MERCHANTCODE' => $this->clientcode,
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

		if ($MERCHANT_SCORING!=null)        $fields["MERCHANT_SCORING"] = $MERCHANT_SCORING;
        if ($MERCHANT_DATA!=null)           $fields["MERCHANT_DATA"] = $MERCHANT_DATA;


		$query = http_build_query($fields);

		$url_paytpv = $this->url_paytpv . "?".$query;

		$vhash = hash('sha512', md5($query.md5($pass_sel))); 

		$url_paytpv = $this->url_paytpv . "?".$query . "&VHASH=".$vhash;
		
		return $url_paytpv;
	}

	/**
	 * return array Term,Currency,amount
	 */
	public function TerminalCurrency($cart){

		// Si hay un terminal definido para la moneda del usuario devolvemos ese.
		$result = Paytpv_Terminal::get_Terminal_Currency($this->context->currency->iso_code);
		// Not exists terminal in user currency
		if (empty($result) === true){
			// Search for terminal in merchant default currency
			$id_currency = intval(Configuration::get('PS_CURRENCY_DEFAULT'));
			$currency = new Currency($id_currency);
			$result = Paytpv_Terminal::get_Terminal_Currency($currency->iso_code);

			// If not exists terminal in default currency. Select first terminal defined
			if (empty($result) === true){
				$result = Paytpv_Terminal::get_First_Terminal();
			}
		}

		$arrDatos["idterminal"] = $result["idterminal"];
		$arrDatos["idterminal_ns"] = $result["idterminal_ns"];
		$arrDatos["password"] = $result["password"];
		$arrDatos["password_ns"] = $result["password_ns"];
		$arrDatos["jetid"] = $result["jetid"];
		$arrDatos["jetid_ns"] = $result["jetid_ns"];
		$arrDatos["currency_iso_code"] = $this->context->currency->iso_code;
		$arrDatos["importe"] = number_format($cart->getOrderTotal(true, Cart::BOTH) * 100, 0, '.', '');
		
        return $arrDatos;
	}


	public function isSecureTransaction($idterminal,$importe,$card){
		$arrTerminal = Paytpv_Terminal::getTerminalByIdTerminal($idterminal);

        $terminales = $arrTerminal["terminales"];
        $tdfirst = $arrTerminal["tdfirst"];
        $tdmin = $arrTerminal["tdmin"];
        // Transaccion Segura:
        
        // Si solo tiene Terminal Seguro
        if ($terminales==0)
            return true;   

        // Si esta definido que el pago es 3d secure y no estamos usando una tarjeta tokenizada
        if ($tdfirst && $card==0)
            return true;

        // Si se supera el importe maximo para compra segura
        if ($terminales==2 && ($tdmin>0 && $tdmin < $importe))
            return true;

         // Si esta definido como que la primera compra es Segura y es la primera compra aunque este tokenizada
        if ($terminales==2 && $tdfirst && $card>0 && Paytpv_Order::isFirstPurchaseToken($this->context->customer->id,$card))
            return true;
        
        
        return false;
    }


	public function isSecurePay($importe){
		// Terminal NO Seguro
		if ($this->terminales==1)
			return false;
		// Ambos Terminales, Usar 3D False e Importe < Importe Min 3d secure
		if ($this->terminales==2 && $this->tdfirst==0 && ($this->tdmin==0 || $importe<=$this->tdmin))
			return false;
		return true;
	}

	public function hookDisplayOrderConfirmation($params)
    {
        
    }


	public function hookDisplayPaymentReturn($params) {

		if (!$this->active)
			return;
		$this->context->smarty->assign(array(
			'this_path' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->name.'/'
		));
		
		$id_order = Order::getOrderByCartId(intval($params["order"]->id_cart));
		$order = new Order($id_order);

		$this->context->smarty->assign('reference',$order->reference);
		$this->context->smarty->assign('base_dir',__PS_BASE_URI__);

		$this->_html .= $this->display(__FILE__, 'payment_return.tpl');

		
		$result = Paytpv_Suscription::get_Suscription_Order_Payments($id_order);
		if ($order->module == $this->name && !empty($result)){

			$id_currency = $order->id_currency;
			$currency = new Currency(intval($id_currency));

			$suscription_type = $this->l('This order is a Subscription');
			
			$id_suscription = $result["id_suscription"];
			$id_customer = $result["id_customer"];
			$periodicity = $result["periodicity"];
			$cycles = ($result['cycles']!=0)?$result['cycles']:$this->l('N');
			$status = $result["status"];
			$date = $result["date"];
			$price = number_format($result['price'], 2, '.', '') . " " . $currency->sign;	
			$num_pagos = $result['pagos'];

			if ($status==0)
				$status = $this->l('ACTIVE');
			else if ($status==1)
				$status = $this->l('CANCELLED');
			else if ($num_pagos==$result['cycles'] && $result['cycles']>0)	
				$status = $this->l('ENDED');

			$language_data = explode("-",$this->context->language->language_code);
			$language = $language_data[0];
                               			

			$date_YYYYMMDD = ($language=="es")?date("d-m-Y",strtotime($result['date'])):date("Y-m-d",strtotime($result['date']));


			$this->context->smarty->assign('suscription_type', $suscription_type);
			$this->context->smarty->assign('id_customer', $id_customer);
			$this->context->smarty->assign('periodicity', $periodicity);
			$this->context->smarty->assign('cycles', $cycles);
			$this->context->smarty->assign('status', $status);
			$this->context->smarty->assign('date_yyyymmdd', $date_YYYYMMDD);
			$this->context->smarty->assign('price', $price);

			$this->_html .= $this->display(__FILE__, 'order_suscription_customer_info.tpl');
		}

		
		return $this->_html;

	}
	private function getConfigValues(){
		return Configuration::getMultiple(array('PAYTPV_CLIENTCODE', 'PAYTPV_INTEGRATION', 'PAYTPV_COMMERCEPASSWORD', 'PAYTPV_NEWPAGEPAYMENT', 'PAYTPV_SUSCRIPTIONS','PAYTPV_REG_ESTADO','PAYTPV_MERCHANTDATA','PAYTPV_FIRSTPURCHASE_SCORING','PAYTPV_FIRSTPURCHASE_SCORING_SCORE','PAYTPV_SESSIONTIME_SCORING','PAYTPV_SESSIONTIME_SCORING_VAL','PAYTPV_SESSIONTIME_SCORING_SCORE','PAYTPV_DCOUNTRY_SCORING','PAYTPV_DCOUNTRY_SCORING_VAL','PAYTPV_DCOUNTRY_SCORING_SCORE','PAYTPV_IPCHANGE_SCORING','PAYTPV_IPCHANGE_SCORING_SCORE','PAYTPV_BROWSER_SCORING','PAYTPV_BROWSER_SCORING_SCORE','PAYTPV_SO_SCORING','PAYTPV_SO_SCORING_SCORE','PAYTPV_DISABLEOFFERSAVECARD','PAYTPV_REMEMBERCARDUNSELECTED'));
	}
	
	public function saveCard($id_customer,$paytpv_iduser,$paytpv_tokenuser,$paytpv_cc,$paytpv_brand){

		$paytpv_cc = '************' . substr($paytpv_cc, -4);

		Paytpv_Customer::add_Customer($paytpv_iduser,$paytpv_tokenuser,$paytpv_cc,$paytpv_brand,$id_customer);

		$result["paytpv_iduser"] = $paytpv_iduser;
		$result["paytpv_tokenuser"] = $paytpv_tokenuser;

		return $result;
	}

	
	public function remove_user($paytpv_iduser,$paytpv_tokenuser){
		$arrTerminal = Paytpv_Terminal::getTerminalByCurrency($this->context->currency->iso_code);
		$idterminal = $arrTerminal["idterminal"];
		$idterminal_ns = $arrTerminal["idterminal_ns"];
		$pass = $arrTerminal["password"];
		$pass_ns = $arrTerminal["password_ns"];
		if ($idterminal>0){
			$idterminal_sel = $idterminal;
			$pass_sel = $pass;
		}else{
			$idterminal_sel = $idterminal_ns;
			$pass_sel = $pass_ns;
		}
		
		$client = new WS_Client(
			array(
				'clientcode' => $this->clientcode,
				'term' => $idterminal_sel,
				'pass' => $pass_sel
			)
		);

		$result = $client->remove_user( $paytpv_iduser, $paytpv_tokenuser);
		return $result;
	}


	public function removeCard($paytpv_iduser){
		$arrTerminal = Paytpv_Terminal::getTerminalByCurrency($this->context->currency->iso_code);
		$idterminal = $arrTerminal["idterminal"];
		$idterminal_ns = $arrTerminal["idterminal_ns"];
		$pass = $arrTerminal["password"];
		$pass_ns = $arrTerminal["password_ns"];
		if ($idterminal>0){
			$idterminal_sel = $idterminal;
			$pass_sel = $pass;
		}else{
			$idterminal_sel = $idterminal_ns;
			$pass_sel = $pass_ns;
		}

		include_once(_PS_MODULE_DIR_.'/paytpv/ws_client.php');

		$client = new WS_Client(
			array(
				'clientcode' => $this->clientcode,
				'term' => $idterminal_sel,
				'pass' => $pass_sel
			)
		);
		// Datos usuario

		
		$result = Paytpv_Customer::get_Customer_Iduser($paytpv_iduser);
		if (empty($result) === true){
			return false;
		}else{
			$paytpv_iduser = $result["paytpv_iduser"];
			$paytpv_tokenuser = $result["paytpv_tokenuser"];

			$result = $client->remove_user( $paytpv_iduser, $paytpv_tokenuser);
			Paytpv_Customer::remove_Customer_Iduser((int)$this->context->customer->id,$paytpv_iduser);
			
			
			return true;
		}
	}

	
	public function removeSuscription($id_suscription){

		$arrTerminal = Paytpv_Terminal::getTerminalByCurrency($this->context->currency->iso_code);
		$idterminal = $arrTerminal["idterminal"];
		$idterminal_ns = $arrTerminal["idterminal_ns"];
		$pass = $arrTerminal["password"];
		$pass_ns = $arrTerminal["password_ns"];
		if ($idterminal>0){
			$idterminal_sel = $idterminal;
			$pass_sel = $pass;
		}else{
			$idterminal_sel = $idterminal_ns;
			$pass_sel = $pass_ns;
		}

		include_once(_PS_MODULE_DIR_.'/paytpv/ws_client.php');

		$client = new WS_Client(
			array(
				'clientcode' => $this->clientcode,
				'term' => $idterminal_sel,
				'pass' => $pass_sel
			)
		);
		// Datos usuario

		$result = Paytpv_Suscription::get_Suscription_Id((int)$this->context->customer->id,$id_suscription);
		
		if (empty($result) === true){
			return false;
		}else{
			$paytpv_iduser = $result["paytpv_iduser"];
			$paytpv_tokenuser = $result["paytpv_tokenuser"];

			$result = $client->remove_subscription( $paytpv_iduser, $paytpv_tokenuser);
			

			if ( ( int ) $result[ 'DS_RESPONSE' ] == 1 ) {
				Paytpv_Suscription::remove_Suscription((int)$this->context->customer->id,$id_suscription);
				
				return true;
			}
			return false;
		}
	}

	public function cancelSuscription($id_suscription){
		$arrTerminal = Paytpv_Terminal::getTerminalByCurrency($this->context->currency->iso_code);
		$idterminal = $arrTerminal["idterminal"];
		$idterminal_ns = $arrTerminal["idterminal_ns"];
		$pass = $arrTerminal["password"];
		$pass_ns = $arrTerminal["password_ns"];
		if ($idterminal>0){
			$idterminal_sel = $idterminal;
			$pass_sel = $pass;
		}else{
			$idterminal_sel = $idterminal_ns;
			$pass_sel = $pass_ns;
		}

		include_once(_PS_MODULE_DIR_.'/paytpv/ws_client.php');

		$client = new WS_Client(
			array(
				'clientcode' => $this->clientcode,
				'term' => $idterminal_sel,
				'pass' => $pass_sel
			)
		);

		
		// Datos usuario
		$result = Paytpv_Suscription::get_Suscription_Id((int)$this->context->customer->id,$id_suscription);
		if (empty($result) === true){
			return false;
		}else{
			$paytpv_iduser = $result["paytpv_iduser"];
			$paytpv_tokenuser = $result["paytpv_tokenuser"];

			
			if ($paytpv_tokenuser=="TESTTOKEN"){
				$result[ 'DS_RESPONSE' ] = 1;
			// Operacion real
			}else{
				$result = $client->remove_subscription( $paytpv_iduser, $paytpv_tokenuser);
			}	
		
			if ( ( int ) $result[ 'DS_RESPONSE' ] == 1 ) {
				Paytpv_Suscription::cancel_Suscription((int)$this->context->customer->id,$id_suscription);
				$response["error"] = 0;
			}else{
				$response["error"] = 1;
			}
			return $response;
		}
	}

	public function validPassword($id_customer,$passwd){
		$sql = 'select * from ' . _DB_PREFIX_ .'customer where id_customer = '.pSQL($id_customer) . ' and passwd="'. md5(pSQL(_COOKIE_KEY_.$passwd)) . '"';
		$result = Db::getInstance()->getRow($sql);
		return (empty($result) === true)?false:true;
	}


	/* 
		Refund
	*/

	public function hookActionProductCancel($params)
	{

		if (Tools::isSubmit('generateDiscount'))
			return false;
		elseif ($params['order']->module != $this->name || !($order = $params['order']) || !Validate::isLoadedObject($order))
			return false;
		elseif (!$order->hasBeenPaid())
			return false;

		$order_detail = new OrderDetail((int)$params['id_order_detail']);
		if (!$order_detail || !Validate::isLoadedObject($order_detail))
			return false;

		$paytpv_order = Paytpv_Order::get_Order((int)$order->id);
		if (empty($paytpv_order)){
			die('error');
			return false;
		}

		$paytpv_date = date("Ymd",strtotime($paytpv_order['date']));
		$paytpv_iduser = $paytpv_order["paytpv_iduser"];
		$paytpv_tokenuser = $paytpv_order["paytpv_tokenuser"];

		$id_currency = $order->id_currency;
		$currency = new Currency(intval($id_currency));

		$orderPayment = $order->getOrderPaymentCollection()->getFirst();
		$authcode = $orderPayment->transaction_id;

		$products = $order->getProducts();
		$cancel_quantity = Tools::getValue('cancelQuantity');

		$amt = (float)($products[(int)$order_detail->id]['product_price_wt'] * (int)$cancel_quantity[(int)$order_detail->id]);
		$amount = number_format($amt * 100, 0, '.', '');

		$paytpv_order_ref = str_pad((int)$order->id_cart, 8, "0", STR_PAD_LEFT);

		$response = $this->_makeRefund($paytpv_iduser,$paytpv_tokenuser,$order->id,$paytpv_order_ref,$paytpv_date,$currency->iso_code,$authcode,$amount,1);
		$refund_txt = $response["txt"];

		$message = $this->l('PayTPV Refund ').  ", " . $amt . " " . $currency->sign . " [" . $refund_txt . "]" .  '<br>';
		$this->_addNewPrivateMessage((int)$order->id, $message);

	}

	private function _makeRefund($paytpv_iduser,$paytpv_tokenuser,$order_id,$paytpv_order_ref,$paytpv_date,$currency_iso_code,$authcode,$amount,$type){
		
		$arrTerminal = Paytpv_Terminal::getTerminalByCurrency($currency_iso_code);

		// Refund amount
		include_once(_PS_MODULE_DIR_.'/paytpv/ws_client.php');
		$client = new WS_Client(
			array(
				'clientcode' => $this->clientcode,
				'term' => $arrTerminal["idterminal"],
				'pass' => $arrTerminal["password"]
			)
		);
	
		
		// Refund amount of transaction
		$result = $client->execute_refund($paytpv_iduser, $paytpv_tokenuser, $paytpv_order_ref, $currency_iso_code, $authcode, $amount);
		$refund_txt = $this->l('OK');
		$response["error"] = 0;
		$response["txt"] = $this->l('OK');

		// If is a subscription and error y initial refund.
		if ($result[ 'DS_ERROR_ID']==130){
			$paytpv_order_ref .= "[" . $paytpv_iduser . "]" . $paytpv_date;
			// Refund amount of transaction
			$result = $client->execute_refund($paytpv_iduser, $paytpv_tokenuser, $paytpv_order_ref, $currency_iso_code, $authcode, $amount);
			$refund_txt = $this->l('OK');
			$response["error"] = 0;
			$response["txt"] = $this->l('OK');
		}
		
		if ( ( int ) $result[ 'DS_RESPONSE' ] != 1 ){
			$response["txt"] = $this->l('ERROR') . " " . $result[ 'DS_ERROR_ID'];
			$response["error"] = 1;
		}else{
			$amount = number_format($amount/100, 2, '.', '');
			Paytpv_Refund::add_Refund($order_id,$amount,$type);
		}
		return $response;

	}

	public function _addNewPrivateMessage($id_order, $message)
	{
		if (!(bool)$id_order)
			return false;

		$new_message = new Message();
		$message = strip_tags($message, '<br>');

		if (!Validate::isCleanHtml($message))
			$message = $this->l('Payments messages are invalid, please check the module.');

		$new_message->message = $message;
		$new_message->id_order = (int)$id_order;
		$new_message->private = 1;

		return $new_message->add();
	}

	/*

	Datos cuenta
	*/

	public function hookDisplayCustomerAccount($params)
	{
		// If not disableoffersavecard
		if (!$this->disableoffersavecard==1){
			$this->smarty->assign('in_footer', false);
			return $this->display(__FILE__, 'my-account.tpl');
		}
	}


	/*

	Datos cuenta
	*/

	public function hookDisplayAdminOrder($params)
	{
		
		if (Tools::isSubmit('submitPayTpvRefund'))
			$this->_doTotalRefund($params['id_order']);

		if (Tools::isSubmit('submitPayTpvPartialRefund'))
			$this->_doPartialRefund($params['id_order']);

		$order = new Order((int)$params['id_order']);
		$result = Paytpv_Suscription::get_Suscription_Order_Payments($params["id_order"]);
		if ($order->module == $this->name && !empty($result)){
			
			$id_currency = $order->id_currency;
			$currency = new Currency(intval($id_currency));


			$suscription = $result["suscription"];
			if ($suscription==1){
				$suscription_type = $this->l('This order is a Subscription');
			}else{
				$suscription_type = $this->l('This order is a payment for Subscription');
			}
			$id_suscription = $result["id_suscription"];
			$id_customer = $result["id_customer"];
			$periodicity = $result["periodicity"];
			$cycles = ($result['cycles']!=0)?$result['cycles']:$this->l('N');
			$status = $result["status"];
			$date = $result["date"];
			$price = number_format($result['price'], 2, '.', '') . " " . $currency->sign;	
			$num_pagos = $result['pagos'];

			if ($status==0)
				$status = $this->l('ACTIVE');
			else if ($status==1)
				$status = $this->l('CANCELLED');
			else if ($num_pagos==$result['cycles'] && $result['cycles']>0)	
				$status = $this->l('ENDED');
                               
			$date_YYYYMMDD = ($this->context->language->iso_code=="es")?date("d-m-Y",strtotime($result['date'])):date("Y-m-d",strtotime($result['date']));


			$this->context->smarty->assign('suscription_type', $suscription_type);
			$this->context->smarty->assign('id_customer', $id_customer);
			$this->context->smarty->assign('periodicity', $periodicity);
			$this->context->smarty->assign('cycles', $cycles);
			$this->context->smarty->assign('status', $status);
			$this->context->smarty->assign('date_yyyymmdd', $date_YYYYMMDD);
			$this->context->smarty->assign('price', $price);

			$this->_html .= $this->display(__FILE__, 'order_suscription_info.tpl');
		}

		// Total Refund Template
		if ($order->module == $this->name && $this->_canRefund($order->id)){

			if (version_compare(_PS_VERSION_, '1.5', '>='))
					$order_state = $order->current_state;
				else
					$order_state = OrderHistory::getLastOrderState($order->id);

			$total_amount = $order->total_paid;

			$amount_returned =  Paytpv_Refund::get_TotalRefund($order->id);
			$amount_returned = number_format($amount_returned, 2, '.', '');


			$total_pending = $total_amount - $amount_returned;
			$total_pending =  number_format($total_pending, 2, '.', '');

			$currency = new Currency((int)$order->id_currency);

			$amt_sign = $total_pending . " " . $currency->sign;

			$error_msg = "";
			if (Tools::getValue('paytpPartialRefundAmount')){
				$amt_refund = str_replace(",",".",Tools::getValue('paytpPartialRefundAmount'));
				if (is_numeric($amt_refund))
					$amt_refund = number_format($amt_refund, 2, '.', '');

				if (Tools::getValue('paytpPartialRefundAmount') && ($amt_refund>$total_pending || $amt_refund=="" || !is_numeric($amt_refund))){
					$error_msg = Tools::displayError($this->l('The partial amount should be less than the outstanding amount'));
				}
			}

			$arrRefunds = array();
			if ($amount_returned>0){
				$arrRefunds = Paytpv_Refund::get_Refund($order->id);
			}
			

			$this->context->smarty->assign(
					array(
						'base_url' => _PS_BASE_URL_.__PS_BASE_URI__,
						'module_name' => $this->name,
						'order_state' => $order_state,
						'params' => $params,
						'total_amount' => $total_amount,
						'amount_returned' => $amount_returned,
						'arrRefunds' => $arrRefunds,
						'amount' => $amt_sign,
						'sign'	 => $currency->sign,
						'error_msg' => $error_msg,
						'ps_version' => _PS_VERSION_
					)
				);



			$template_refund = 'views/templates/admin/admin_order/refund.tpl';
			$this->_html .=  $this->display(__FILE__, $template_refund);
			$this->_postProcess();
		}

		return $this->_html;	
	}

	private function _doPartialRefund($id_order)
	{

		$paytpv_order = Paytpv_Order::get_Order((int)$id_order);
		if (empty($paytpv_order)){
			return false;
		}

		$order = new Order((int)$id_order);
		if (!Validate::isLoadedObject($order))
			return false;

		$products = $order->getProducts();
		$currency = new Currency((int)$order->id_currency);
		if (!Validate::isLoadedObject($currency))
			$this->_errors[] = $this->l('Invalid Currency');

		if (count($this->_errors))
			return false;

		$decimals = (is_array($currency) ? (int)$currency['decimals'] : (int)$currency->decimals) * _PS_PRICE_DISPLAY_PRECISION_;

		$total_amount = $order->total_paid;

		$total_pending = $total_amount - Paytpv_Refund::get_TotalRefund($order->id);
		$total_pending =  number_format($total_pending, 2, '.', '');

		$amt_refund  = str_replace(",",".",Tools::getValue('paytpPartialRefundAmount'));
		if (is_numeric($amt_refund))
			$amt_refund = number_format($amt_refund, 2, '.', '');
		
		if ($amt_refund>$total_pending || $amt_refund=="" || !is_numeric($amt_refund)){
			$this->errors[] = Tools::displayError($this->l('The partial amount should be less than the outstanding amount'));
			
		}else{

			$amt = $amt_refund;

			$paytpv_date = date("Ymd",strtotime($paytpv_order['date']));
			$paytpv_iduser = $paytpv_order["paytpv_iduser"];
			$paytpv_tokenuser = $paytpv_order["paytpv_tokenuser"];

			$id_currency = $order->id_currency;
			$currency = new Currency(intval($id_currency));

			$orderPayment = $order->getOrderPaymentCollection()->getFirst();
			$authcode = $orderPayment->transaction_id;

			$amount = number_format($amt * 100, 0, '.', '');

			$paytpv_order_ref = str_pad((int)$order->id_cart, 8, "0", STR_PAD_LEFT);

			$response = $this->_makeRefund($paytpv_iduser,$paytpv_tokenuser,$order->id,$paytpv_order_ref,$paytpv_date,$currency->iso_code,$authcode,$amount,1);
			$refund_txt = $response["txt"];
			$message = $this->l('PayTPV Refund ').  ", " . $amt . " " . $currency->sign . " [" . $refund_txt . "]" .  '<br>';
			
			$this->_addNewPrivateMessage((int)$id_order, $message);

			Tools::redirect($_SERVER['HTTP_REFERER']);
		}
	}

	private function _doTotalRefund($id_order)
	{

		$paytpv_order = Paytpv_Order::get_Order((int)$id_order);
		if (empty($paytpv_order)){
			return false;
		}

		$order = new Order((int)$id_order);
		if (!Validate::isLoadedObject($order))
			return false;

		$products = $order->getProducts();
		$currency = new Currency((int)$order->id_currency);
		if (!Validate::isLoadedObject($currency))
			$this->_errors[] = $this->l('Invalid Currency');

		if (count($this->_errors))
			return false;

		$decimals = (is_array($currency) ? (int)$currency['decimals'] : (int)$currency->decimals) * _PS_PRICE_DISPLAY_PRECISION_;

		$total_amount = $order->total_paid;

		$total_pending = $total_amount - Paytpv_Refund::get_TotalRefund($order->id);
		$total_pending =  number_format($total_pending, 2, '.', '');

		$paytpv_date = date("Ymd",strtotime($paytpv_order['date']));
		$paytpv_iduser = $paytpv_order["paytpv_iduser"];
		$paytpv_tokenuser = $paytpv_order["paytpv_tokenuser"];

		$id_currency = $order->id_currency;
		$currency = new Currency(intval($id_currency));

		$orderPayment = $order->getOrderPaymentCollection()->getFirst();
		$authcode = $orderPayment->transaction_id;

		$products = $order->getProducts();
		$cancel_quantity = Tools::getValue('cancelQuantity');

		$amount = number_format($total_pending * 100, 0, '.', '');

		$paytpv_order_ref = str_pad((int)$order->id_cart, 8, "0", STR_PAD_LEFT);

		$response = $this->_makeRefund($paytpv_iduser,$paytpv_tokenuser,$order->id,$paytpv_order_ref,$paytpv_date,$currency->iso_code,$authcode,$amount,0);
		$refund_txt = $response["txt"];
		$message = $this->l('PayTPV Total Refund ').  ", " . $total_pending . " " . $currency->sign . " [" . $refund_txt . "]" .  '<br>';
		if ($response['error'] == 0)
		{
			if (!Paytpv_Order::set_Order_Refunded($id_order))
				die(Tools::displayError('Error when updating PayTPV database'));

			$history = new OrderHistory();
			$history->id_order = (int)$id_order;
			$history->changeIdOrderState((int)Configuration::get('PS_OS_REFUND'), $history->id_order);
			$history->addWithemail();
			$history->save();
		}

		$this->_addNewPrivateMessage((int)$id_order, $message);

		Tools::redirect($_SERVER['HTTP_REFERER']);
	}


	private function _canRefund($id_order)
	{
		if (!(bool)$id_order)
			return false;

		$paytpv_order = Paytpv_Order::get_Order((int)$id_order);

		return $paytpv_order;//&& $paytpv_order['payment_status'] != 'Refunded';
	}
}

?>