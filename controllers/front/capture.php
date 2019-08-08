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
*  @author     PAYCOMET <info@paycomet.com>
*  @copyright  2019 PAYTPV ON LINE ENTIDAD DE PAGO S.L
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/
/**
 * @since 1.5.0
 */

include_once(_PS_MODULE_DIR_.'/paytpv/ws_client.php');
class PaytpvCaptureModuleFrontController extends ModuleFrontController
{
    public $display_column_left = false;
    public $ssl = true;
   
    /**
     * @see FrontController::initContent()
     */
   	public function initContent()
    {

    	parent::initContent();
    	
        $this->context->smarty->assign(array(
            'this_path' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->module->name.'/'
        ));  

		$paytpv = $this->module;


		$password_fail = 0;
		$error_msg = "";
		// Verificar contraseña usuario.
		if ($paytpv->commerce_password){
	        if (!$paytpv->validPassword($this->context->cart->id_customer,Tools::getValue('password'))){
	        	$password_fail = 1;
	        	$this->context->smarty->assign('password_fail',$password_fail);
	        	$this->context->smarty->assign('error_msg',$error_msg);
				$this->context->smarty->assign(array(
					'this_path' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->module->name.'/',
					'base_dir' =>  __PS_BASE_URI__
				));
        		$this->setTemplate('module:paytpv/views/templates/front/payment_fail.tpl');

	        	
	        	return;
	        }
	    }
	    $id_currency = intval(Configuration::get('PS_CURRENCY_DEFAULT'));
		$currency = new Currency(intval($id_currency));
		$total_pedido = $this->context->cart->getOrderTotal(true, Cart::BOTH);
		
		$datos_pedido = $paytpv->TerminalCurrency($this->context->cart);
		$importe = $datos_pedido["importe"];
		$currency_iso_code = $datos_pedido["currency_iso_code"];
		$idterminal = $datos_pedido["idterminal"];
		$idterminal_ns = $datos_pedido["idterminal_ns"];
		$pass = $datos_pedido["password"];
		$pass_ns = $datos_pedido["password_ns"];
		$jetid = $datos_pedido["jetid"];
		$jetid_ns = $datos_pedido["jetid_ns"];

	    // BANKSTORE JET
	    $token = isset($_POST["paytpvToken"])?$_POST["paytpvToken"]:"";
	    $savecard_jet = isset($_POST["savecard_jet"])?$_POST["savecard_jet"]:0;
	   
	    $jetPayment = 0;
	    if ($token && strlen($token) == 64){

	    	// PAGO SEGURO
			if ($idterminal>0)
				$secure_pay = $paytpv->isSecureTransaction($idterminal,$total_pedido,0)?1:0;
			else
				$secure_pay = $paytpv->isSecureTransaction($idterminal_ns,$total_pedido,0)?1:0;

			// Miramos a ver por que terminal enviamos la operacion
			if ($secure_pay){
				$idterminal_sel = $idterminal;
				$pass_sel = $pass;
				$jetid_sel = $jetid;
			}else{
				$idterminal_sel = $idterminal_ns;
				$pass_sel = $pass_ns;
				$jetid_sel = $jetid_ns;
			}

	    	$client = new WS_Client(
				array(
					'endpoint_paytpv' => $paytpv->endpoint_paytpv,
					'clientcode' => $paytpv->clientcode,
					'term' => $idterminal_sel,
					'pass' => $pass_sel,
					'jetid' => $jetid_sel
				)
			);

			$addUserResponse = $client->add_user_token($token);
			if ( ( int ) $addUserResponse[ 'DS_ERROR_ID' ] > 0 ) {
				$this->context->smarty->assign('error_msg',$paytpv->l('Cannot operate with given credit card','capture'));
				$this->context->smarty->assign('password_fail',$password_fail);
				$this->context->smarty->assign(array(
					'this_path' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->module->name.'/',
					'base_dir' =>  __PS_BASE_URI__
				));
        		$this->setTemplate('module:paytpv/views/templates/front/payment_fail.tpl');
        		return;

			}else{
				$data["IDUSER"] = $addUserResponse["DS_IDUSER"];
				$data["TOKEN_USER"] = $addUserResponse["DS_TOKEN_USER"];

				$jetPayment = 1;
			}
		// TOKENIZED CARD
		}else{
        	$data = Paytpv_Customer::get_Card_Token_Customer($_GET["TOKEN_USER"],$this->context->cart->id_customer);
        	if (!isset($data["IDUSER"])){
        		$this->context->smarty->assign('error_msg',$paytpv->l('Cannot operate with given credit card','capture'));
        		$this->context->smarty->assign('password_fail',"");
	        	$this->context->smarty->assign(array(
					'this_path' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->module->name.'/',
					'base_dir' =>  __PS_BASE_URI__
				));
	        	$this->setTemplate('module:paytpv/views/templates/front/payment_fail.tpl');
	        	return;
        	}

        	if ($idterminal>0)
				$secure_pay = $paytpv->isSecureTransaction($idterminal,$total_pedido,$data["IDUSER"])?1:0;
			else
				$secure_pay = $paytpv->isSecureTransaction($idterminal_ns,$total_pedido,$data["IDUSER"])?1:0;

			// Miramos a ver por que terminal enviamos la operacion
			if ($secure_pay){
				$idterminal_sel = $idterminal;
				$pass_sel = $pass;
			}else{
				$idterminal_sel = $idterminal_ns;
				$pass_sel = $pass_ns;
			}		
        }

        Paytpv_Order_Info::save_Order_Info((int)$this->context->customer->id,$this->context->cart->id,0,0,0,0,$data["IDUSER"]);
		
		// Si el cliente solo tiene un terminal seguro, el segundo pago va siempre por seguro.
		// Si tiene un terminal NO Seguro ó ambos, el segundo pago siempre lo mandamos por NO Seguro

		$score = $paytpv->transactionScore($this->context->cart);
		$MERCHANT_SCORING = $score["score"];
		$MERCHANT_DATA = $paytpv->getMerchantData($this->context->cart);

		

		// PAGO SEGURO
		if ($secure_pay){

			$paytpv_order_ref = str_pad($this->context->cart->id, 8, "0", STR_PAD_LEFT);

			$values = array(
				'id_cart' => (int)$this->context->cart->id,
				'key' => Context::getContext()->customer->secure_key
			);
			$ssl = Configuration::get('PS_SSL_ENABLED');
			
			$URLOK=Context::getContext()->link->getModuleLink($paytpv->name, 'urlok',$values,$ssl);
			$URLKO=Context::getContext()->link->getModuleLink($paytpv->name, 'urlko',$values,$ssl);

			$language_data = explode("-",$this->context->language->language_code);
			$language = $language_data[0];

			if ($jetPayment && (isset($_POST["suscription"]) && $_POST["suscription"]==1)){
				$subscription_startdate = date("Ymd");
				$susc_periodicity = $_POST["periodicity"];
				$subs_cycles = $_POST["cycles"];

				// Si es indefinido, ponemos como fecha tope la fecha + 10 años.
				if ($subs_cycles==0)
					$subscription_enddate = date("Y")+5 . date("m") . date("d");
				else{
					// Dias suscripcion
					$dias_subscription = $subs_cycles * $susc_periodicity;
					$subscription_enddate = date('Ymd', strtotime("+".$dias_subscription." days"));
				}
				$OPERATION = "110";
				$signature = hash('sha512',$paytpv->clientcode.$data["IDUSER"].$data['TOKEN_USER'].$idterminal_sel.$OPERATION.$paytpv_order_ref.$importe.$currency_iso_code.md5($pass_sel));
				$fields = array
				(
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
					'IDUSER' => $data["IDUSER"],
					'TOKEN_USER' => $data['TOKEN_USER'],
					'URLOK' => $URLOK,
					'URLKO' => $URLKO,
					'3DSECURE' => $secure_pay
				);
			}else{

				$OPERATION = "109"; //exec_purchase_token
				$signature = hash('sha512',$paytpv->clientcode.$data["IDUSER"].$data['TOKEN_USER'].$idterminal_sel.$OPERATION.$paytpv_order_ref.$importe.$currency_iso_code.md5($pass_sel));

				$fields = array
					(
						'MERCHANT_MERCHANTCODE' => $paytpv->clientcode,
						'MERCHANT_TERMINAL' => $idterminal_sel,
						'OPERATION' => $OPERATION,
						'LANGUAGE' => $language,
						'MERCHANT_MERCHANTSIGNATURE' => $signature,
						'MERCHANT_ORDER' => $paytpv_order_ref,
						'MERCHANT_AMOUNT' => $importe,
						'MERCHANT_CURRENCY' => $currency_iso_code,
						'IDUSER' => $data["IDUSER"],
						'TOKEN_USER' => $data['TOKEN_USER'],
						'3DSECURE' => $secure_pay,
						'URLOK' => $URLOK,
						'URLKO' => $URLKO
					);
			}

			if ($MERCHANT_SCORING!=null)        $fields["MERCHANT_SCORING"] = $MERCHANT_SCORING;
        	if ($MERCHANT_DATA!=null)           $fields["MERCHANT_DATA"] = $MERCHANT_DATA;

			$query = http_build_query($fields);

			$vhash = hash('sha512', md5($query.md5($pass_sel))); 

			$salida = $paytpv->url_paytpv . "?".$query . "&VHASH=".$vhash;
			
			header('Location: '.$salida);
			exit;
		}
		/* FIN AÑADIDO */
		
		$client = new WS_Client(
			array(
				'endpoint_paytpv' => $paytpv->endpoint_paytpv,
				'clientcode' => $paytpv->clientcode,
				'term' => $idterminal_sel,
				'pass' => $pass_sel,
			)
		);
		$paytpv_order_ref = str_pad($this->context->cart->id, 8, "0", STR_PAD_LEFT);
		
		if ($jetPayment && (isset($_POST["suscription"]) && $_POST["suscription"]==1)){
			$subscription_startdate = date("Y-m-d");
			$susc_periodicity = $_POST["periodicity"];
			$subs_cycles = $_POST["cycles"];

			// Si es indefinido, ponemos como fecha tope la fecha + 10 años.
			if ($subs_cycles==0)
				$subscription_enddate = date("Y")+5 . "-" . date("m") . "-" . date("d");
			else{
				// Dias suscripcion
				$dias_subscription = $subs_cycles * $susc_periodicity;
				$subscription_enddate = date('Y-m-d', strtotime("+".$dias_subscription." days"));
			}
			
			$charge = $client->create_subscription_token( $data['IDUSER'],$data['TOKEN_USER'],$currency_iso_code,$importe,$paytpv_order_ref,$subscription_startdate,$subscription_enddate,$susc_periodicity,$MERCHANT_SCORING,$MERCHANT_DATA);
		}else{
			$charge = $client->execute_purchase( $data['IDUSER'],$data['TOKEN_USER'],$idterminal_sel,$currency_iso_code,$importe,$paytpv_order_ref,$MERCHANT_SCORING,$MERCHANT_DATA);
		}
		
		if ( (isset($charge[ 'DS_RESPONSE' ]) && ( int )$charge[ 'DS_RESPONSE' ] == 1) || $charge[ 'DS_ERROR_ID' ] == 0) {

			//Esperamos a que la notificación genere el pedido
			sleep ( 3 );
			$id_order = Order::getOrderByCartId(intval($this->context->cart->id));

			if ($jetPayment){
				$importe_ps  = number_format($importe / 100, 2, ".","");

				// Save paytpv order
				Paytpv_Order::add_Order($data['IDUSER'],$data['TOKEN_USER'],0,$this->context->cart->id_customer,$id_order,$importe_ps);

				if ($savecard_jet==1){
					$result = $client->info_user( $data['IDUSER'],$data['TOKEN_USER']);
					$result = $paytpv->saveCard($this->context->cart->id_customer,$data['IDUSER'],$data['TOKEN_USER'],$result['DS_MERCHANT_PAN'],$result['DS_CARD_BRAND']);
				}
			}


			$values = array(
				'id_cart' => $this->context->cart->id,
				'id_module' => (int)$this->module->id,
				'id_order' => $id_order,
				'key' => $this->context->customer->secure_key
			);
			Tools::redirect(Context::getContext()->link->getPageLink('order-confirmation',$this->ssl,null,$values));
			return;
		}else{

			if (isset($reg_estado) && $reg_estado == 1)
			//se anota el pedido como no pagado
			class_registro::add($this->context->cart->id_customer, $this->context->cart->id, $importe, $charge[ 'DS_RESPONSE' ]);
		}
				
		$this->context->smarty->assign('error_msg',$paytpv->l('Cannot operate with given credit card','capture'));
		$this->context->smarty->assign('password_fail',$password_fail);	
		$this->context->smarty->assign('base_dir',__PS_BASE_URI__);
		$this->context->smarty->assign('password_fail',$password_fail);
        $this->setTemplate('module:paytpv/views/templates/front/payment_fail.tpl');
        return;

    }

}

