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

class PaytpvUrlModuleFrontController extends ModuleFrontController
{
    public $display_column_left = false;

    public $ssl = true;
    /**
     * @see FrontController::initContent()
     */

    public function initContent()
    {

        $this->context->smarty->assign(array(
            'this_path' => Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'modules/' . $this->module->name .
            '/'
        ));

        $esURLOK = false;
        $pagoRegistrado = false;
        $result = 666;
        $paytpv = $this->module;

        $reg_estado = $paytpv->reg_estado;

        $suscripcion = 0;

        // Check Notification
        if (Tools::getValue('ping') == "1") {
            die('PING OK');
        }

        // Obtencion de datos
        if (Tools::getValue('paycomet_data') == "1") {
            $arrDatos = array("module_v" => $paytpv->version, "ps_v" => _PS_VERSION_);
            exit(json_encode($arrDatos));
        }
        

        // Notify response
        // (execute_purchase)
        if (Tools::getValue('TransactionType') === "1"
            and Tools::getValue('Order')
            and Tools::getValue('Response')
            and Tools::getValue('NotificationHash')
        ) {
            $importe  = number_format(Tools::getValue('Amount') / 100, 2, ".", "");
            $ref = Tools::getValue('Order');
            $result = Tools::getValue('Response') == 'OK' ? 0 : -1;
            $sign = Tools::getValue('NotificationHash');
            $esURLOK = false;

            $context = Context::getContext();
            $id_cart = (int) Tools::substr($ref, 0, 8);
            $cart = new Cart($id_cart);
            
            if (Context::getContext()->shop->id != $cart->id_shop) {
                $context->shop->id = $cart->id_shop;
            }


            $arrTerminal = PaytpvTerminal::getTerminalByIdTerminal(Tools::getValue('TpvID'));
            $idterminal = $arrTerminal["idterminal"];
            $idterminal_ns = $arrTerminal["idterminal_ns"];
            $pass = $arrTerminal["password"];
            $pass_ns = $arrTerminal["password_ns"];
            

            if (Tools::getValue('TpvID') == $idterminal) {
                $idterminal_sel = $idterminal;
                $pass_sel = $pass;
            }
            if (Tools::getValue('TpvID') == $idterminal_ns) {
                $idterminal_sel = $idterminal_ns;
                $pass_sel = $pass_ns;
            }

            $local_sign = hash('sha512', $paytpv->clientcode . $idterminal_sel . Tools::getValue('TransactionType') .
                                $ref . Tools::getValue('Amount') . Tools::getValue('Currency') . md5($pass_sel) .
                                Tools::getValue('BankDateTime') . Tools::getValue('Response'));

            // Check Signature
            if ($sign != $local_sign) {
                die('Error 1');
            }

            // (add_user)
        } elseif (Tools::getValue('TransactionType') === "107") {
            $ref = Tools::getValue('Order');
            $sign = Tools::getValue('NotificationHash');
            $esURLOK = false;

            $datos_op = explode("_", $ref);
            $id_customer = $datos_op[0];
            $id_shop = $datos_op[1];
            $context = Context::getContext();
            if (Context::getContext()->shop->id != $id_shop) {
                $context->shop->id = $id_shop;
            }


            $arrTerminal = PaytpvTerminal::getTerminalByIdTerminal(Tools::getValue('TpvID'));
            $idterminal = $arrTerminal["idterminal"];
            $idterminal_ns = $arrTerminal["idterminal_ns"];
            $pass = $arrTerminal["password"];
            $pass_ns = $arrTerminal["password_ns"];

            if (Tools::getValue('TpvID') == $idterminal) {
                $idterminal_sel = $idterminal;
                $pass_sel = $pass;
            }
            if (Tools::getValue('TpvID') == $idterminal_ns) {
                $idterminal_sel = $idterminal_ns;
                $pass_sel = $pass_ns;
            }
            $local_sign = hash('sha512', $paytpv->clientcode . $idterminal_sel . Tools::getValue('TransactionType') .
                                 $ref . Tools::getValue('DateTime') . md5($pass_sel));

            // Check Signature
            if ($sign != $local_sign) {
                die('Error 2');
            }
            
            if ($paytpv->apikey != '') {
                include_once(_PS_MODULE_DIR_ . '/paytpv/classes/PaytpvApi.php');
                $apiRest = new PaycometApiRest($paytpv->apikey);
                $infoUserResponse = $apiRest->infoUser(
                    Tools::getValue('IdUser'),
                    Tools::getValue('TokenUser'),
                    $idterminal_sel
                );

                $result = array();
                $result['DS_MERCHANT_PAN'] = $infoUserResponse->pan;
                $result['DS_CARD_BRAND'] = $infoUserResponse->cardBrand;

            } else {
                include_once(_PS_MODULE_DIR_ . '/paytpv/classes/WSClient.php');
                $client = new WSClient(
                    array(
                        'endpoint_paytpv' => $paytpv->endpoint_paytpv,
                        'clientcode' => $paytpv->clientcode,
                        'term' => $idterminal_sel,
                        'pass' => $pass_sel,
                    )
                );

                $result = $client->infoUser(Tools::getValue('IdUser'), Tools::getValue('TokenUser'));
            }

            $paytpv->saveCard(
                $id_customer,
                Tools::getValue('IdUser'),
                Tools::getValue('TokenUser'),
                $result['DS_MERCHANT_PAN'],
                $result['DS_CARD_BRAND']
            );

            die('Usuario Registrado');

            // (create_subscription)
        } elseif (Tools::getValue('TransactionType') === "9") {
            $result = Tools::getValue('Response') == 'OK' ? 0 : -1;
            $sign = Tools::getValue('NotificationHash');
            $esURLOK = false;

            $ref = Tools::getValue('Order');
            // Look if is initial order or a subscription payment (orden[Iduser]Fecha)
            $datos = explode("[", $ref);
            $ref = $datos[0];

            $context = Context::getContext();
            $id_cart = (int) Tools::substr($ref, 0, 8);
            $cart = new Cart($id_cart);
            if (Context::getContext()->shop->id != $cart->id_shop) {
                $context->shop->id = $cart->id_shop;
            }

            $arrTerminal = PaytpvTerminal::getTerminalByIdTerminal(Tools::getValue('TpvID'));
            $idterminal = $arrTerminal["idterminal"];
            $idterminal_ns = $arrTerminal["idterminal_ns"];
            $pass = $arrTerminal["password"];
            $pass_ns = $arrTerminal["password_ns"];

            if (Tools::getValue('TpvID') == $idterminal) {
                $idterminal_sel = $idterminal;
                $pass_sel = $pass;
            }
            if (Tools::getValue('TpvID') == $idterminal_ns) {
                $idterminal_sel = $idterminal_ns;
                $pass_sel = $pass_ns;
            }


            $local_sign = hash('sha512', $paytpv->clientcode . $idterminal_sel . Tools::getValue('TransactionType') .
                Tools::getValue('Order') . Tools::getValue('Amount') . Tools::getValue('Currency') . md5($pass_sel) .
                Tools::getValue('BankDateTime') . Tools::getValue('Response'));

            // Check Signature
            if ($sign != $local_sign) {
                die('Error 3');
            }

            $suscripcion = 1;  // Inicio Suscripcion
            $importe  = number_format(Tools::getValue('Amount') / 100, 2, ".", "");

            // Check if is a suscription payment
            $id_cart = (int) Tools::substr($ref, 0, 8);
            $id_order = Order::getOrderByCartId((int) $id_cart);

            // if exits cart order is a suscription payment
            if ($id_order) {
                $suscripcion = 2;
            }
        }


        if ($result == 0) {
            $context = Context::getContext();
            $id_cart = (int) Tools::substr($ref, 0, 8);
            $cart = new Cart($id_cart);
            $customer = new Customer((int) $cart->id_customer);

            $address = new Address((int) $cart->id_address_invoice);
            $context->cart = $cart;
            $context->customer = new Customer((int) $cart->id_customer);
            $context->country = new Country((int) $address->id_country);
            $context->language = new Language((int) $cart->id_lang);
            $context->currency = new Currency((int) $cart->id_currency);

            $_GET['id_shop'] = $cart->id_shop;
            Shop::initialize();

            $id_order = Order::getOrderByCartId((int) $id_cart);

            $transaction = array(
                'transaction_id' => Tools::getValue('AuthCode'),
                'result' => $result
            );

            // EXIST ORDER
            if ($id_order) {
                $order = new Order($id_order);

                $sql = 'SELECT COUNT(oh.`id_order_history`) AS nb
                        FROM `' . _DB_PREFIX_ . 'order_history` oh
                        WHERE oh.`id_order` = ' . (int) $id_order . '
                AND oh.id_order_state = ' . Configuration::get('PS_OS_PAYMENT');
                $n = Db::getInstance()->getValue($sql);
                $pagoRegistrado = $n > 0;

                // If a subscription payment
                // SUSCRIPCION
                if (Tools::getValue('TransactionType') === "9" && $suscripcion == 2) {
                    $cart_problem_txt = "";

                    $new_cart = $cart->duplicate();
                    $data_suscription = PaytpvSuscription::getSuscriptionOrder($cart->id_customer, $id_order);
                    $id_suscription = $data_suscription["id_suscription"];
                    $paytpv_iduser = $data_suscription["paytpv_iduser"];
                    $paytpv_tokenuser = $data_suscription["paytpv_tokenuser"];

                    if (!$new_cart || !Validate::isLoadedObject($new_cart['cart'])) {
                        exit;
                    } elseif (!$new_cart['success']) {
                        // Refund amount
                        if($paytpv->apikey != '') {
                            include_once(_PS_MODULE_DIR_ . '/paytpv/classes/PaytpvApi.php');
                            
                            $ip = Tools::getRemoteAddr();
                            if ($ip=="::1" || $ip=="") {
                                $ip = "127.0.0.1";
                            }

                            $notifyDirectPayment = 2;
                            
                            $apiRest = new PaycometApiRest($paytpv->apikey);
                            $executeRefundReponse = $apiRest->executeRefund(
                                Tools::getValue('Order'),
                                $idterminal_sel,
                                Tools::getValue('Amount'),
                                Tools::getValue('Currency'),
                                Tools::getValue('AuthCode'),
                                $ip,
                                $notifyDirectPayment
                            );
                            $result = array();
                            $result['DS_RESPONSE'] = $executeRefundReponse->errorCode;
                            $result['DS_MERCHANT_AUTHCODE'] = $executeRefundReponse->authCode;
                        } else {
                            include_once(_PS_MODULE_DIR_ . '/paytpv/classes/WSClient.php');
                            $client = new WSClient(
                                array(
                                    'endpoint_paytpv' => $paytpv->endpoint_paytpv,
                                    'clientcode' => $paytpv->clientcode,
                                    'term' => $idterminal_sel,
                                    'pass' => $pass_sel,
                                )
                            );

                            $result = $client->executeRefund(
                                $paytpv_iduser,
                                $paytpv_tokenuser,
                                Tools::getValue('Order'),
                                Tools::getValue('Currency'),
                                Tools::getValue('AuthCode'),
                                Tools::getValue('Amount')
                            );
                        }

                        $refund = 1;
                        if ((int) $result['DS_RESPONSE'] != 1) {
                            $refund = 0;
                        }

                        $cart_problem_txt = $paytpv->l(
                            "Any subscription product is no longer available",
                            (int) $cart->id_lang
                        ) . "<br>";

                        // Mailing to Customer: Product in suscription is no longer available **********************
                        $message = "<br> " .  $paytpv->l(
                            'Dear Customer. There have been changes in the order to which you are subscribed',
                            (int) $cart->id_lang
                        ) . " (" . $order->reference . ")";
                        $message .= "<br><br>" .  $paytpv->l($cart_problem_txt, (int) $cart->id_lang);
                        $message .= "<br> " .  $paytpv->l(
                            'The payment amount of the subscription has been refunded to your account',
                            (int) $cart->id_lang
                        );
                        $message .= "<br> " .  $paytpv->l(
                            "You can Unsubscribe from your acount if desired",
                            (int) $cart->id_lang
                        );

                        $params = array(
                            '{firstname}' => $this->context->customer->firstname,
                            '{lastname}' => $this->context->customer->lastname,
                            '{email}' => $this->context->customer->email,
                            '{order_name}' => $order->reference,
                            '{message}' => $message
                        );

                        Mail::Send(
                            (int) $order->id_lang,
                            'order_merchant_comment',
                            sprintf(
                                Mail::l(
                                    'Problem with subscription order %s',
                                    (int) $order->id_lang
                                ),
                                $order->reference
                            ),
                            $params,
                            $this->context->customer->email,
                            $this->context->customer->firstname . ' ' . $this->context->customer->lastname,
                            null,
                            null,
                            null,
                            null,
                            _PS_MAIL_DIR_,
                            false,
                            (int) $order->id_shop
                        );
                        // ***********************************************************************

                        // Mailing to Merchant: Subscription payment error **********************
                        $params = array(
                            '{firstname}' => $this->context->customer->firstname,
                            '{lastname}' => $this->context->customer->lastname,
                            '{email}' => $this->context->customer->email,
                            '{id_order}' => (int) ($order->id),
                            '{order_name}' => $order->getUniqReference(),
                            '{message}' => sprintf(
                                Mail::l(
                                    'Subscription payment error to order %s',
                                    (int) $order->id_lang
                                ),
                                $order->reference
                            ) . " -- Referencia PAYCOMET: " . Tools::getValue('Order')
                        );

                        if (!Configuration::get('PS_MAIL_EMAIL_MESSAGE')) {
                            $to = (string) Configuration::get('PS_SHOP_EMAIL');
                        } else {
                            $to = new Contact((int) (Configuration::get('PS_MAIL_EMAIL_MESSAGE')));
                            $to = (string) $to->email;
                        }
                        $toName = (string) Configuration::get('PS_SHOP_NAME');

                        // Mailing
                        Mail::Send(
                            (int) $order->id_lang,
                            'order_customer_comment',
                            sprintf(
                                Mail::l(
                                    'Subscription payment error in order %s',
                                    (int) $order->id_lang
                                ),
                                $order->reference
                            ),
                            $params,
                            $to,
                            $toName,
                            $this->context->customer->email,
                            $this->context->customer->firstname . ' ' . $this->context->customer->lastname
                        );
                        // *********************************************************************************
                        die("[Refund " . $refund . "] " . $cart_problem_txt);
                    }

                    $displayName = $paytpv->displayName;
                    if (Tools::getIsset('MethodName')) {
                        $displayName .= " [" . Tools::getValue('MethodName') . "]";
                    }

                    $pagoRegistrado = $paytpv->validateOrder(
                        $new_cart['cart']->id,
                        _PS_OS_PAYMENT_,
                        $importe,
                        $displayName,
                        null,
                        $transaction,
                        null,
                        true,
                        $customer->secure_key
                    );
                    $id_order = Order::getOrderByCartId((int) $new_cart['cart']->id);

                    PaytpvOrder::addOrder(
                        $paytpv_iduser,
                        $paytpv_tokenuser,
                        $id_suscription,
                        $cart->id_customer,
                        $id_order,
                        $importe
                    );
                }
                // NO ORDER
            } else {
                $displayName = $paytpv->displayName;
                if (Tools::getIsset('MethodName')) {
                    $displayName .= " [" . Tools::getValue('MethodName') . "]";
                }

                $pagoRegistrado = $paytpv->validateOrder(
                    $id_cart,
                    _PS_OS_PAYMENT_,
                    $importe,
                    $displayName,
                    null,
                    $transaction,
                    null,
                    false,
                    $customer->secure_key
                );

                $id_order = Order::getOrderByCartId((int) $id_cart);
                $id_suscription = 0;

                $defaultsavecard = 0;
                $datos_order = PaytpvOrderInfo::getOrderInfo($cart->id_customer, $id_cart, $defaultsavecard);


                // BANKSTORE: Si hay notificacion
                if (Tools::getValue('IdUser')) {
                    $paytpv_iduser = Tools::getValue('IdUser');
                    $paytpv_tokenuser = Tools::getValue('TokenUser');

                    // IF check agreement save token
                    if ($suscripcion == 0 && $datos_order["paytpvagree"]) {
                        if ($paytpv->apikey != '') {
                            include_once(_PS_MODULE_DIR_ . '/paytpv/classes/PaytpvApi.php');

                            $apiRest = new PaycometApiRest($paytpv->apikey);
                            $infoUserResponse = $apiRest->infoUser(
                                $paytpv_iduser,
                                $paytpv_tokenuser,
                                $idterminal_sel
                            );

                            $result = array();
                            $result['DS_MERCHANT_PAN'] = $infoUserResponse->pan;
                            $result['DS_CARD_BRAND'] = $infoUserResponse->cardBrand;
                        } else {
                            include_once(_PS_MODULE_DIR_ . '/paytpv/classes/WSClient.php');

                            $client = new WSClient(
                                array(
                                    'endpoint_paytpv' => $paytpv->endpoint_paytpv,
                                    'clientcode' => $paytpv->clientcode,
                                    'term' => $idterminal_sel,
                                    'pass' => $pass_sel,
                                )
                            );
                            $result = $client->infoUser($paytpv_iduser, $paytpv_tokenuser);
                        }

                        $result = $paytpv->saveCard(
                            $cart->id_customer,
                            Tools::getValue('IdUser'),
                            Tools::getValue('TokenUser'),
                            $result['DS_MERCHANT_PAN'],
                            $result['DS_CARD_BRAND']
                        );
                        $paytpv_iduser = $result["paytpv_iduser"];
                        $paytpv_tokenuser = $result["paytpv_tokenuser"];
                    }

                    // SUSCRIPCION
                    if ($suscripcion == 1) {
                        PaytpvSuscription::saveSuscription(
                            $cart->id_customer,
                            $id_order,
                            $paytpv_iduser,
                            $paytpv_tokenuser,
                            $datos_order["periodicity"],
                            $datos_order["cycles"],
                            $importe
                        );

                        $data_suscription = PaytpvSuscription::getSuscriptionOrder($cart->id_customer, $id_order);
                        $id_suscription = $data_suscription["id_suscription"];

                        // Mailing to Merchant: Subscription order info **********************************************
                        $order = new Order($id_order);

                        $params = array(
                            '{firstname}' => $this->context->customer->firstname,
                            '{lastname}' => $this->context->customer->lastname,
                            '{email}' => $this->context->customer->email,
                            '{id_order}' => (int) ($order->id),
                            '{order_name}' => $order->getUniqReference(),
                            '{message}' => sprintf(
                                Mail::l(
                                    'New subscription to order %s',
                                    (int) $order->id_lang
                                ),
                                $order->reference
                            )
                        );

                        if (!Configuration::get('PS_MAIL_EMAIL_MESSAGE')) {
                            $to = (string) Configuration::get('PS_SHOP_EMAIL');
                        } else {
                            $to = new Contact((int) (Configuration::get('PS_MAIL_EMAIL_MESSAGE')));
                            $to = (string) $to->email;
                        }
                        $toName = (string) Configuration::get('PS_SHOP_NAME');

                        Mail::Send(
                            (int) $order->id_lang,
                            'order_customer_comment',
                            sprintf(
                                Mail::l(
                                    'New Subscription to order %s',
                                    (int) $order->id_lang
                                ),
                                $order->reference
                            ),
                            $params,
                            $to,
                            $toName,
                            $this->context->customer->email,
                            $this->context->customer->firstname . ' ' . $this->context->customer->lastname
                        );

                        // ********************************************************************************************
                    }

                    if ($suscripcion and $reg_estado == 1) {
                        ClassRegistro::removeByCartID($id_cart);
                    }

                // Token Payment or APM
                } else {
                    $result = PaytpvCustomer::getCustomerIduser($datos_order["paytpv_iduser"]);
                    $paytpv_iduser = $result["paytpv_iduser"];
                    $paytpv_tokenuser = $result["paytpv_tokenuser"];
                }
                // Save paytpv order
                PaytpvOrder::addOrder(
                    $paytpv_iduser,
                    $paytpv_tokenuser,
                    $id_suscription,
                    $cart->id_customer,
                    $id_order,
                    $importe
                );
            }
            // if URLOK and registered payemnt go to order confirmation
            if ($esURLOK && $pagoRegistrado) {
                $values = array(
                    'id_cart' => $id_cart,
                    'id_module' => (int) $this->module->id,
                    'id_order' => $id_order,
                    'key' => Tools::getValue('key')
                );
                Tools::redirect(
                    Context::getContext()->link->getPageLink(
                        'order-confirmation',
                        $this->ssl,
                        null,
                        $values
                    )
                );
                return;
            } elseif ($pagoRegistrado) {
                die('Pago registrado');
            }
        } else {
            //se anota el pedido como no pagado
            if (Tools::getIsset($reg_estado) && $reg_estado == 1) {
                ClassRegistro::add($cart->id_customer, $id_cart, $importe, $result);
            }

            /*if ($sign != $local_sign){
                header("HTTP/1.0 466 Invalid Signature");
                die('HAcking Attenpt!!');
            }*/
        }
        die('Error');
    }
}
