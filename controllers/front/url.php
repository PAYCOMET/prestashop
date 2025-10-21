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
if (!defined('_PS_VERSION_')) {
    exit;
}

class PaytpvUrlModuleFrontController extends ModuleFrontController
{
    public $display_column_left = false;

    public $ssl = true;

    /**
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        $this->context->smarty->assign([
            'this_path' => Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'modules/' . $this->module->name .
                '/',
        ]);

        $pagoRegistrado = false;
        $result = 666;
        /** @var Paytpv $paytpv */
        $paytpv = $this->module;

        $idterminal = 0;
        $importe = 0.0;
        $ref = '0';

        $suscripcion = 0;

        // Check Notification
        if (Tools::getValue('ping') == '1') {
            echo 'PING OK';
            exit(0);
        }

        // Obtencion de datos
        if (Tools::getValue('paycomet_data') == '1') {
            $arrTerminal = PaytpvTerminal::getTerminalByIdTerminal(Tools::getValue('terminal'));
            $idterminal = $arrTerminal['idterminal'];
            $pass = $arrTerminal['password'];

            $apiKey = ($paytpv->apikey != '') ? 1 : 0;

            if (
                Tools::getValue('clientcode') && Tools::getValue('clientcode') == $paytpv->clientcode
                && Tools::getValue('terminal') && Tools::getValue('terminal') == $idterminal
            ) {
                $arrDatos = [
                    'module_v' => $paytpv->version,
                    'ps_v' => _PS_VERSION_,
                    'ak' => $apiKey,
                ];
                echo json_encode($arrDatos);
                exit(0);
            }
        }

        // Notify response
        // (execute_purchase)
        if (
            Tools::getValue('TransactionType') === '1'
            and Tools::getValue('Order')
            and Tools::getValue('Response')
            and Tools::getValue('NotificationHash')
        ) {
            $importe = number_format(Tools::getValue('Amount') / 100, 2, '.', '');
            $ref = Tools::getValue('Order');
            $result = Tools::getValue('Response') == 'OK' ? 0 : -1;
            $sign = Tools::getValue('NotificationHash');
            $context = $this->context;
            $id_cart = (int) $ref;
            $cart = new Cart($id_cart);

            if ($context->shop->id != $cart->id_shop) {
                $context->shop->id = $cart->id_shop;
            }

            $arrTerminal = PaytpvTerminal::getTerminalByIdTerminal(Tools::getValue('TpvID'));
            $idterminal = $arrTerminal['idterminal'];
            $pass = $arrTerminal['password'];

            $local_sign = hash('sha512', $paytpv->clientcode . $idterminal . Tools::getValue('TransactionType') .
                $ref . Tools::getValue('Amount') . Tools::getValue('Currency') . md5($pass) .
                Tools::getValue('BankDateTime') . Tools::getValue('Response'));

            // Check Signature
            if ($sign != $local_sign) {
                echo 'Error 1';
                exit(0);
            }

        // (add_user)
        } elseif (Tools::getValue('TransactionType') === '107') {
            $ref = Tools::getValue('Order');
            $sign = Tools::getValue('NotificationHash');

            $datos_op = explode('_', $ref);
            $id_customer = $datos_op[0];
            $id_shop = $datos_op[1];
            $context = $this->context;
            if ($context->shop->id != $id_shop) {
                $context->shop->id = (int) $id_shop;
            }

            $arrTerminal = PaytpvTerminal::getTerminalByIdTerminal(Tools::getValue('TpvID'));
            $idterminal = $arrTerminal['idterminal'];
            $pass = $arrTerminal['password'];

            $local_sign = hash('sha512', $paytpv->clientcode . $idterminal . Tools::getValue('TransactionType') .
                $ref . Tools::getValue('DateTime') . md5($pass));

            // Check Signature
            if ($sign != $local_sign) {
                echo 'Error 2';
                exit(0);
            }

            if ($paytpv->apikey != '') {
                include_once _PS_MODULE_DIR_ . '/paytpv/classes/PaytpvApi.php';
                $apiRest = new PaycometApiRest($paytpv->apikey, $paytpv->paycometHeader);
                $infoUserResponse = $apiRest->infoUser(
                    Tools::getValue('IdUser'),
                    Tools::getValue('TokenUser'),
                    $idterminal
                );

                $result = [];
                $result['DS_MERCHANT_PAN'] = $infoUserResponse->pan;
                $result['DS_CARD_BRAND'] = $infoUserResponse->cardBrand;
                $result['DS_MERCHANT_EXPIRYDATE'] = $infoUserResponse->expiryDate;

                $paytpv->saveCard(
                    $id_customer,
                    Tools::getValue('IdUser'),
                    Tools::getValue('TokenUser'),
                    $result['DS_MERCHANT_PAN'],
                    $result['DS_CARD_BRAND'],
                    $result['DS_MERCHANT_EXPIRYDATE']
                );
                echo 'Usuario Registrado';
                exit(0);
            } else {
                echo 'Error 1004';
                exit(0);
            }

        // (create_subscription)
        } elseif (Tools::getValue('TransactionType') === '9') {
            $result = Tools::getValue('Response') == 'OK' ? 0 : -1;
            $sign = Tools::getValue('NotificationHash');

            $ref = Tools::getValue('Order');
            // Look if is initial order or a subscription payment (orden[Iduser]Fecha)
            $datos = explode('[', $ref);
            $ref = $datos[0];

            $context = $this->context;
            $id_cart = (int) $ref;
            $cart = new Cart($id_cart);
            if ($context->shop->id != $cart->id_shop) {
                $context->shop->id = $cart->id_shop;
            }

            $arrTerminal = PaytpvTerminal::getTerminalByIdTerminal(Tools::getValue('TpvID'));
            $idterminal = $arrTerminal['idterminal'];
            $pass = $arrTerminal['password'];

            $local_sign = hash('sha512', $paytpv->clientcode . $idterminal . Tools::getValue('TransactionType') .
                Tools::getValue('Order') . Tools::getValue('Amount') . Tools::getValue('Currency') . md5($pass) .
                Tools::getValue('BankDateTime') . Tools::getValue('Response'));

            // Check Signature
            if ($sign != $local_sign) {
                echo 'Error 3';
                exit(0);
            }

            $suscripcion = 1;  // Inicio Suscripcion
            $importe = number_format(Tools::getValue('Amount') / 100, 2, '.', '');

            // Check if is a suscription payment
            $id_cart = (int) $ref;
            $id_order = (int) Order::getIdByCartId((int) $id_cart);

            // if exits cart order is a suscription payment
            if ($id_order) {
                $suscripcion = 2;
            }
        }

        if (is_int($result) && $result === 0) {
            $context = $this->context;
            $id_cart = (int) $ref;
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

            $id_order = (int) Order::getIdByCartId((int) $id_cart);

            $transaction = [
                'transaction_id' => Tools::getValue('AuthCode'),
                'result' => $result,
            ];

            // EXIST ORDER
            if ($id_order) {
                $order = new Order($id_order);
                $pagoRegistrado = $paytpv->isOrderPaid($id_order);

                // If a subscription payment
                // SUSCRIPCION
                if (Tools::getValue('TransactionType') === '9' && $suscripcion == 2) {
                    // Evitar duplicidades.
                    $notifDuplicada = $paytpv->isPaymentProcesed(Tools::getValue('AuthCode'));
                    if ($notifDuplicada) {
                        echo 'Notif Duplicada';
                        exit(0);
                    }

                    $cart_problem_txt = '';

                    $new_cart = $cart->duplicate();
                    $data_suscription = PaytpvSuscription::getSuscriptionOrder($cart->id_customer, $id_order);
                    $id_suscription = $data_suscription['id_suscription'];
                    $paytpv_iduser = $data_suscription['paytpv_iduser'];
                    $paytpv_tokenuser = $data_suscription['paytpv_tokenuser'];

                    if (!$new_cart || !Validate::isLoadedObject($new_cart['cart'])) {
                        exit(0);
                    } elseif (!$new_cart['success']) {
                        // Refund amount
                        if ($paytpv->apikey != '') {
                            include_once _PS_MODULE_DIR_ . '/paytpv/classes/PaytpvApi.php';

                            $ip = Tools::getRemoteAddr();
                            if ($ip == '::1' || $ip == '') {
                                $ip = '127.0.0.1';
                            }

                            $notifyDirectPayment = 2;

                            $apiRest = new PaycometApiRest($paytpv->apikey, $paytpv->paycometHeader);
                            $executeRefundReponse = $apiRest->executeRefund(
                                Tools::getValue('Order'),
                                $idterminal,
                                Tools::getValue('Amount'),
                                Tools::getValue('Currency'),
                                Tools::getValue('AuthCode'),
                                $ip,
                                $notifyDirectPayment
                            );
                            $result = [];
                            $result['DS_RESPONSE'] = $executeRefundReponse->errorCode;
                            $result['DS_MERCHANT_AUTHCODE'] = $executeRefundReponse->authCode;
                        } else {
                            $result = [];
                            $result['DS_RESPONSE'] = 1004;
                            $result['DS_MERCHANT_AUTHCODE'] = '';
                        }

                        $refund = 1;
                        if ((int) $result['DS_RESPONSE'] != 1) {
                            $refund = 0;
                        }

                        $cart_problem_txt = $paytpv->l(
                            'Any subscription product is no longer available'
                        ) . '<br>';

                        // Mailing to Customer: Product in suscription is no longer available **********************
                        $message = '<br> ' . $paytpv->l(
                            'Dear Customer. There have been changes in the order to which you are subscribed'
                        ) . ' (' . $order->reference . ')';
                        $message .= '<br><br>' . $paytpv->l($cart_problem_txt);
                        $message .= '<br> ' . $paytpv->l(
                            'The payment amount of the subscription has been refunded to your account'
                        );
                        $message .= '<br> ' . $paytpv->l(
                            'You can Unsubscribe from your acount if desired'
                        );

                        $params = [
                            '{firstname}' => $context->customer->firstname,
                            '{lastname}' => $context->customer->lastname,
                            '{email}' => $context->customer->email,
                            '{order_name}' => $order->reference,
                            '{message}' => $message,
                        ];

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
                        $params = [
                            '{firstname}' => $context->customer->firstname,
                            '{lastname}' => $context->customer->lastname,
                            '{email}' => $context->customer->email,
                            '{id_order}' => (int) $order->id,
                            '{order_name}' => $order->getUniqReference(),
                            '{message}' => sprintf(
                                Mail::l(
                                    'Subscription payment error to order %s',
                                    (int) $order->id_lang
                                ),
                                $order->reference
                            ) . ' -- Referencia PAYCOMET: ' . Tools::getValue('Order'),
                        ];

                        if (!Configuration::get('PS_MAIL_EMAIL_MESSAGE')) {
                            $to = (string) Configuration::get('PS_SHOP_EMAIL');
                        } else {
                            $to = new Contact((int) Configuration::get('PS_MAIL_EMAIL_MESSAGE'));
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
                            $context->customer->email,
                            $context->customer->firstname . ' ' . $context->customer->lastname
                        );
                        // *********************************************************************************
                        echo '[Refund ' . $refund . '] ' . $cart_problem_txt;
                        exit(0);
                    }

                    $displayName = $paytpv->displayName;
                    if (Tools::getIsset('MethodName')) {
                        $displayName .= ' [' . Tools::getValue('MethodName') . ']';
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
                    $id_order = (int) Order::getIdByCartId((int) $new_cart['cart']->id);

                    PaytpvOrder::addOrder(
                        $paytpv_iduser,
                        $paytpv_tokenuser,
                        $id_suscription,
                        $cart->id_customer,
                        $id_order,
                        $importe
                    );
                }
                // Para APMs. Si el estado esta en "Pendient de pago" lo pasamos a Pago Aceptado
                if ($order->getCurrentState() == Configuration::get('PS_CHECKOUT_STATE_WAITING_LOCAL_PAYMENT')) {
                    $order->addOrderPayment((string) $importe, null, Tools::getValue('AuthCode'));

                    $history = new OrderHistory();
                    $history->id_order = (int) $order->id;
                    $history->changeIdOrderState(_PS_OS_PAYMENT_, (int) $order->id, true);
                    $history->addWithemail();
                    $history->save();

                    $pagoRegistrado = true;

                    PaytpvOrder::addOrder(
                        0,
                        0,
                        0,
                        $cart->id_customer,
                        (int) $order->id,
                        $importe
                    );
                }
            // NO ORDER
            } else {
                $displayName = $paytpv->displayName;
                if (Tools::getIsset('MethodName')) {
                    $displayName .= ' [' . Tools::getValue('MethodName') . ']';
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

                $id_order = (int) Order::getIdByCartId((int) $id_cart);
                $id_suscription = 0;

                $defaultsavecard = 0;
                $datos_order = PaytpvOrderInfo::getOrderInfo($cart->id_customer, $id_cart, $defaultsavecard);

                // BANKSTORE: Si hay notificacion
                if (Tools::getValue('IdUser')) {
                    $paytpv_iduser = Tools::getValue('IdUser');
                    $paytpv_tokenuser = Tools::getValue('TokenUser');

                    // IF check agreement save token
                    if ($suscripcion == 0 && $datos_order['paytpvagree']) {
                        if ($paytpv->apikey != '') {
                            include_once _PS_MODULE_DIR_ . '/paytpv/classes/PaytpvApi.php';

                            $apiRest = new PaycometApiRest($paytpv->apikey, $paytpv->paycometHeader);
                            $infoUserResponse = $apiRest->infoUser(
                                $paytpv_iduser,
                                $paytpv_tokenuser,
                                $idterminal
                            );

                            $result = [];
                            $result['DS_MERCHANT_PAN'] = $infoUserResponse->pan;
                            $result['DS_CARD_BRAND'] = $infoUserResponse->cardBrand;
                            $result['DS_MERCHANT_EXPIRYDATE'] = $infoUserResponse->expiryDate;

                            $result = $paytpv->saveCard(
                                $cart->id_customer,
                                Tools::getValue('IdUser'),
                                Tools::getValue('TokenUser'),
                                $result['DS_MERCHANT_PAN'],
                                $result['DS_CARD_BRAND'],
                                $result['DS_MERCHANT_EXPIRYDATE']
                            );

                            $paytpv_iduser = $result['paytpv_iduser'];
                            $paytpv_tokenuser = $result['paytpv_tokenuser'];
                        }
                    }

                    // SUSCRIPCION
                    if ($suscripcion == 1) {
                        PaytpvSuscription::saveSuscription(
                            $cart->id_customer,
                            $id_order,
                            $paytpv_iduser,
                            $paytpv_tokenuser,
                            $datos_order['periodicity'],
                            $datos_order['cycles'],
                            $importe
                        );

                        $data_suscription = PaytpvSuscription::getSuscriptionOrder($cart->id_customer, $id_order);
                        $id_suscription = $data_suscription['id_suscription'];

                        // Mailing to Merchant: Subscription order info **********************************************
                        $order = new Order($id_order);

                        $params = [
                            '{firstname}' => $context->customer->firstname,
                            '{lastname}' => $context->customer->lastname,
                            '{email}' => $context->customer->email,
                            '{id_order}' => (int) $order->id,
                            '{order_name}' => $order->getUniqReference(),
                            '{message}' => sprintf(
                                Mail::l(
                                    'New subscription to order %s',
                                    (int) $order->id_lang
                                ),
                                $order->reference
                            ),
                        ];

                        if (!Configuration::get('PS_MAIL_EMAIL_MESSAGE')) {
                            $to = (string) Configuration::get('PS_SHOP_EMAIL');
                        } else {
                            $to = new Contact((int) Configuration::get('PS_MAIL_EMAIL_MESSAGE'));
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
                            $context->customer->email,
                            $context->customer->firstname . ' ' . $context->customer->lastname
                        );

                        // ********************************************************************************************
                    }

                // Token Payment or APM
                } else {
                    $paytpv_iduser = 0;
                    $paytpv_tokenuser = 0;
                    $result = PaytpvCustomer::getCustomerIduser($datos_order['paytpv_iduser']);
                    if ($result) {
                        $paytpv_iduser = $result['paytpv_iduser'];
                        $paytpv_tokenuser = $result['paytpv_tokenuser'];
                    }
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

            if ($pagoRegistrado) {
                echo 'Pago registrado';
                exit(0);
            }
        } else {
            $ref = Tools::getValue('Order');
            $id_cart = (int) $ref;
            $id_order = (int) Order::getIdByCartId((int) $id_cart);
            $order = new Order($id_order);
            // Para APMs. Si el estado esta en "Pendient de pago" lo pasamos a Pago Aceptado
            if ($order->getCurrentState() && $order->getCurrentState() == Configuration::get('PS_CHECKOUT_STATE_WAITING_LOCAL_PAYMENT')) {
                $order->addOrderPayment((string) $importe, null, Tools::getValue('AuthCode'));
                $history = new OrderHistory();
                $history->id_order = (int) $order->id;
                $history->changeIdOrderState(8, (int) $order->id, true);
                echo 'Pago fallido';
                exit(0);
            }
        }
        echo 'Error';
        exit(0);
    }
}
