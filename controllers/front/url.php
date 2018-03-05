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
*  @author     Jose Ramon Garcia <jrgarcia@paytpv.com>
*  @copyright  2015 PAYTPV ON LINE S.L.
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/
/**
 * @since 1.5.0
 */

class PaytpvUrlModuleFrontController extends ModuleFrontController
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

		$esURLOK = false;
		$pagoRegistrado = false;
		$result = 666;
		$paytpv = $this->module;

		$reg_estado = $paytpv->reg_estado;

		$suscripcion = 0;
	
		// Notify response
		// (execute_purchase)
		if (Tools::getValue('TransactionType')==="1"
			AND Tools::getValue('Order')
			AND Tools::getValue('Response')
			AND Tools::getValue('ExtendedSignature'))
		{
			$importe  = number_format(Tools::getValue('Amount')/ 100, 2, ".","");
			$ref = Tools::getValue('Order');
			$result = Tools::getValue('Response')=='OK'?0:-1;
			$sign = Tools::getValue('ExtendedSignature');
			$esURLOK = false;

			$arrTerminal = Paytpv_Terminal::getTerminalByIdTerminal(Tools::getValue('TpvID'));
			$idterminal = $arrTerminal["idterminal"];
			$idterminal_ns = $arrTerminal["idterminal_ns"];
			$pass = $arrTerminal["password"];
			$pass_ns = $arrTerminal["password_ns"];

			if (Tools::getValue('TpvID')==$idterminal){
				$idterminal_sel = $idterminal;
				$pass_sel = $pass;
			}
			if (Tools::getValue('TpvID')==$idterminal_ns){
				$idterminal_sel = $idterminal_ns;
				$pass_sel = $pass_ns;
			}

			$local_sign = md5($paytpv->clientcode.$idterminal_sel.Tools::getValue('TransactionType').$ref.Tools::getValue('Amount').Tools::getValue('Currency').md5($pass_sel).Tools::getValue('BankDateTime').Tools::getValue('Response'));
			
			// Check Signature
			if ($sign!=$local_sign)	die('Error 1');
			
		// (add_user)
		}else if (Tools::getValue('TransactionType')==="107"){
			$ref = Tools::getValue('Order');
			$sign = Tools::getValue('Signature');
			$esURLOK = false;

			$arrTerminal = Paytpv_Terminal::getTerminalByIdTerminal(Tools::getValue('TpvID'));
			$idterminal = $arrTerminal["idterminal"];
			$idterminal_ns = $arrTerminal["idterminal_ns"];
			$pass = $arrTerminal["password"];
			$pass_ns = $arrTerminal["password_ns"];

			if (Tools::getValue('TpvID')==$idterminal){
				$idterminal_sel = $idterminal;
				$pass_sel = $pass;
			}
			if (Tools::getValue('TpvID')==$idterminal_ns){
				$idterminal_sel = $idterminal_ns;
				$pass_sel = $pass_ns;
			}
			$local_sign = md5($paytpv->clientcode.$idterminal_sel.Tools::getValue('TransactionType').$ref.Tools::getValue('DateTime').md5($pass_sel));

			// Check Signature
			if ($sign!=$local_sign)	die('Error 2');

			include_once(_PS_MODULE_DIR_.'/paytpv/ws_client.php');
			$client = new WS_Client(
				array(
					'clientcode' => $paytpv->clientcode,
					'term' => $idterminal_sel,
					'pass' => $pass_sel,
				)
			);

			$id_customer = Tools::getValue('Order');
			$result = $client->info_user( Tools::getValue('IdUser'),Tools::getValue('TokenUser'));
			$paytpv->saveCard($id_customer,Tools::getValue('IdUser'),Tools::getValue('TokenUser'),$result['DS_MERCHANT_PAN'],$result['DS_CARD_BRAND']);
			
			die('Usuario Registrado');
		
		// (create_subscription)
		}else if (Tools::getValue('TransactionType')==="9"){

			$result = Tools::getValue('Response')=='OK'?0:-1;
			$sign = Tools::getValue('ExtendedSignature');
			$esURLOK = false;

			$arrTerminal = Paytpv_Terminal::getTerminalByIdTerminal(Tools::getValue('TpvID'));
			$idterminal = $arrTerminal["idterminal"];
			$idterminal_ns = $arrTerminal["idterminal_ns"];
			$pass = $arrTerminal["password"];
			$pass_ns = $arrTerminal["password_ns"];

			if (Tools::getValue('TpvID')==$idterminal){
				$idterminal_sel = $idterminal;
				$pass_sel = $pass;
			}
			if (Tools::getValue('TpvID')==$idterminal_ns){
				$idterminal_sel = $idterminal_ns;
				$pass_sel = $pass_ns;
			}


			$local_sign = md5($paytpv->clientcode.$idterminal_sel.Tools::getValue('TransactionType').Tools::getValue('Order').Tools::getValue('Amount').Tools::getValue('Currency').md5($pass_sel).Tools::getValue('BankDateTime').Tools::getValue('Response'));
			
			// Check Signature
			if ($sign!=$local_sign)	die('Error 3');

			$suscripcion = 1;  // Inicio Suscripcion
			$importe  = number_format(Tools::getValue('Amount')/ 100, 2, ".","");
			$ref = Tools::getValue('Order');

			// Look if is initial order or a subscription payment (orden[Iduser]Fecha)
			$datos = explode("[",$ref);
			$ref = $datos[0];

			// Check if is a suscription payment
			$id_cart = (int)substr($ref,0,8);
			$id_order = Order::getOrderByCartId(intval($id_cart));

			// if exits cart order is a suscription payment
			if ($id_order){
				$suscripcion = 2;
			}
		}


		if($result == 0){
			$id_cart = (int)substr($ref,0,8);
			$cart = new Cart($id_cart);
			$customer = new Customer((int) $cart->id_customer);
			$context = Context::getContext();
			$context->cart = $cart;
			$context->customer = $customer;
			$_GET['id_shop'] = $cart->id_shop;
            Shop::initialize();

		
			$id_order = Order::getOrderByCartId(intval($id_cart));
			
			$transaction = array(
				'transaction_id' => Tools::getValue('AuthCode'),
				'result' => $result
			);

			// EXIST ORDER
			if($id_order){

				$order = new Order($id_order);

				$sql = 'SELECT COUNT(oh.`id_order_history`) AS nb
						FROM `'._DB_PREFIX_.'order_history` oh
						WHERE oh.`id_order` = '.(int)$id_order.'
				AND oh.id_order_state = '.Configuration::get('PS_OS_PAYMENT');
				$n = Db::getInstance()->getValue($sql);
				$pagoRegistrado = $n>0;

				// If a subscription payment
				// SUSCRIPCION
				if (Tools::getValue('TransactionType')==="9" && $suscripcion==2){
					$cart_problem_txt = "";

					$new_cart = $cart->duplicate();
					$data_suscription = Paytpv_Suscription::get_Suscription_Order($cart->id_customer,$id_order);
					$id_suscription = $data_suscription["id_suscription"];
					$paytpv_iduser = $data_suscription["paytpv_iduser"];
					$paytpv_tokenuser = $data_suscription["paytpv_tokenuser"];

					if (!$new_cart || !Validate::isLoadedObject($new_cart['cart'])){
						exit;
					}else if (!$new_cart['success']){

						// Refund amount
						include_once(_PS_MODULE_DIR_.'/paytpv/ws_client.php');
						$client = new WS_Client(
							array(
								'clientcode' => $paytpv->clientcode,
								'term' => $idterminal_sel,
								'pass' => $pass_sel,
							)
						);

						// Refund amount of transaction
						$result = $client->execute_refund($paytpv_iduser, $paytpv_tokenuser, Tools::getValue('Order'), Tools::getValue('Currency'), Tools::getValue('AuthCode'), Tools::getValue('Amount'));
						$refund = 1;
						if ( ( int ) $result[ 'DS_RESPONSE' ] != 1 ) {
							$refund = 0;
						}

						$cart_problem_txt = $paytpv->l("Any subscription product is no longer available",(int)$cart->id_lang) . "<br>";

						// Mailing to Customer: Product in suscription is no longer available **********************
						$message = "<br> " .  $paytpv->l('Dear Customer. There have been changes in the order to which you are subscribed',(int)$cart->id_lang) . " (". $order->reference .")";
						$message .= "<br><br>" .  $paytpv->l($cart_problem_txt,(int)$cart->id_lang);
						$message .= "<br> " .  $paytpv->l('The payment amount of the subscription has been refunded to your account',(int)$cart->id_lang);
						$message .= "<br> " .  $paytpv->l("You can Unsubscribe from your acount if desired",(int)$cart->id_lang);

						$params = array(
							'{firstname}' => $this->context->customer->firstname,
							'{lastname}' => $this->context->customer->lastname,
							'{email}' => $this->context->customer->email,
							'{order_name}' => $order->reference,
							'{message}' => $message
						);

						Mail::Send(
							(int)$order->id_lang,
							'order_merchant_comment',
							sprintf(Mail::l('Problem with subscription order %s', (int)$order->id_lang), $order->reference),
							$params,
							$this->context->customer->email,
							$this->context->customer->firstname.' '.$this->context->customer->lastname,
							null, null, null, null, _PS_MAIL_DIR_, false, (int)$order->id_shop
						);
						// ***********************************************************************

						// Mailing to Merchant: Subscription payment error **********************
						$params = array(
							'{firstname}' => $this->context->customer->firstname,
							'{lastname}' => $this->context->customer->lastname,
							'{email}' => $this->context->customer->email,
							'{id_order}' => (int)($order->id),
							'{order_name}' => $order->getUniqReference(),
							'{message}' => sprintf(Mail::l('Subscription payment error to order %s', (int)$order->id_lang), $order->reference) . " -- Referencia PayTPV: " . Tools::getValue('Order')
						);

						if (!Configuration::get('PS_MAIL_EMAIL_MESSAGE'))
							$to = strval(Configuration::get('PS_SHOP_EMAIL'));
						else
						{
							$to = new Contact((int)(Configuration::get('PS_MAIL_EMAIL_MESSAGE')));
							$to = strval($to->email);
						}
						$toName = strval(Configuration::get('PS_SHOP_NAME'));

						// Mailing 
						Mail::Send(
							(int)$order->id_lang,
							'order_customer_comment',
							sprintf(Mail::l('Subscription payment error in order %s', (int)$order->id_lang), $order->reference),
							$params,
							$to, $toName, $this->context->customer->email, $this->context->customer->firstname.' '.$this->context->customer->lastname);
						// *********************************************************************************
						die ("[Refund " . $refund . "] ".$cart_problem_txt);
					}

					$pagoRegistrado = $paytpv->validateOrder($new_cart['cart']->id, _PS_OS_PAYMENT_, $importe, $paytpv->displayName, NULL, $transaction, NULL, true, $customer->secure_key);
					$id_order = Order::getOrderByCartId(intval($new_cart['cart']->id));

					Order::save_Order($paytpv_iduser,$paytpv_tokenuser,$id_suscription,$cart->id_customer,$id_order,$importe);
				}
			// NO ORDER
			}else{
				 

				$pagoRegistrado = $paytpv->validateOrder($id_cart, _PS_OS_PAYMENT_, $importe, $paytpv->displayName, NULL, $transaction, NULL, false, $customer->secure_key);
			
				$id_order = Order::getOrderByCartId(intval($id_cart));
				$id_suscription = 0;

				$disableoffersavecard = $paytpv->disableoffersavecard;
				$remembercardunselected = $paytpv->remembercardunselected;

				$defaultsavecard = ($disableoffersavecard!=1 && $remembercardunselected!=1)?1:0;
				$datos_order = Paytpv_Order_Info::get_Order_Info($cart->id_customer,$id_cart,$defaultsavecard);


				// BANKSTORE: Si hay notificacion
				if(Tools::getValue('IdUser')){

					$paytpv_iduser = Tools::getValue('IdUser');
					$paytpv_tokenuser = Tools::getValue('TokenUser');
					
					// IF check agreement save token
					if ($suscripcion==0 && $datos_order["paytpvagree"]){
												
						include_once(_PS_MODULE_DIR_.'/paytpv/ws_client.php');
						$client = new WS_Client(
							array(
								'clientcode' => $paytpv->clientcode,
								'term' => Tools::getValue('TpvID'),
								'pass' => $pass,
							)
						);
						$result = $client->info_user( $paytpv_iduser,$paytpv_tokenuser );
						
						$result = $paytpv->saveCard($cart->id_customer,Tools::getValue('IdUser'),Tools::getValue('TokenUser'),$result['DS_MERCHANT_PAN'],$result['DS_CARD_BRAND']);
						$paytpv_iduser = $result["paytpv_iduser"];
						$paytpv_tokenuser = $result["paytpv_tokenuser"];
					}

					// SUSCRIPCION
					if ($suscripcion==1){
						Paytpv_Suscription::save_Suscription($cart->id_customer,$id_order,$paytpv_iduser,$paytpv_tokenuser,$datos_order["periodicity"],$datos_order["cycles"],$importe);

						$data_suscription = Paytpv_Suscription::get_Suscription_Order($cart->id_customer,$id_order);
						$id_suscription = $data_suscription["id_suscription"];

						// Mailing to Merchant: Subscription order info **********************************************
						$order = new Order($id_order);
						
						$params = array(
							'{firstname}' => $this->context->customer->firstname,
							'{lastname}' => $this->context->customer->lastname,
							'{email}' => $this->context->customer->email,
							'{id_order}' => (int)($order->id),
							'{order_name}' => $order->getUniqReference(),
							'{message}' => sprintf(Mail::l('New subscription to order %s', (int)$order->id_lang), $order->reference)
						);

						if (!Configuration::get('PS_MAIL_EMAIL_MESSAGE'))
							$to = strval(Configuration::get('PS_SHOP_EMAIL'));
						else
						{
							$to = new Contact((int)(Configuration::get('PS_MAIL_EMAIL_MESSAGE')));
							$to = strval($to->email);
						}
						$toName = strval(Configuration::get('PS_SHOP_NAME'));

						Mail::Send(
							(int)$order->id_lang,
							'order_customer_comment',
							sprintf(Mail::l('New Subscription to order %s', (int)$order->id_lang), $order->reference),
							$params,
							$to, $toName, $this->context->customer->email, $this->context->customer->firstname.' '.$this->context->customer->lastname);

						// **************************************************************************************************
					}

					if ($suscripcion AND $reg_estado == 1)
						class_registro::removeByCartID($id_cart);

				// Token Payment
				}else{
					$result = Paytpv_Customer::get_Customer_Iduser($datos_order["paytpv_iduser"]);
					$paytpv_iduser = $result["paytpv_iduser"];
					$paytpv_tokenuser = $result["paytpv_tokenuser"];
				}
				// Save paytpv order
				Paytpv_Order::add_Order($paytpv_iduser,$paytpv_tokenuser,$id_suscription,$cart->id_customer,$id_order,$importe);
				
			}
			// if URLOK and registered payemnt go to order confirmation
			if($esURLOK && $pagoRegistrado){
				$values = array(
					'id_cart' => $id_cart,
					'id_module' => (int)$this->module->id,
					'id_order' => $id_order,
					'key' => Tools::getValue('key')
				);              
				Tools::redirect(Context::getContext()->link->getPageLink('order-confirmation',$this->ssl,null,$values));
				return;
			}
			else if($pagoRegistrado){
				die('Pago registrado');
			}
		}else{
			//se anota el pedido como no pagado
			if (isset($reg_estado) && $reg_estado == 1)
				class_registro::add($cart->id_customer, $id_cart, $importe, $result);

			/*if ($sign != $local_sign){
				header("HTTP/1.0 466 Invalid Signature");
				die('HAcking Attenpt!!');
			}*/

		}
		die('Error');
	}

}

