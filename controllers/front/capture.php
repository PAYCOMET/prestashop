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

include_once(_PS_MODULE_DIR_ . '/paytpv/classes/WSClient.php');
include_once(_PS_MODULE_DIR_ . '/paytpv/classes/PaycometApiRest.php');
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

        $this->context->smarty->assign(
            array('this_path' => Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'modules/' .
            $this->module->name . '/')
        );

        $paytpv = $this->module;

        $total_pedido = $this->context->cart->getOrderTotal(true, Cart::BOTH);

        $datos_pedido = $paytpv->terminalCurrency($this->context->cart);
        $importe = $datos_pedido["importe"];
        $currency_iso_code = $datos_pedido["currency_iso_code"];
        $idterminal = $datos_pedido["idterminal"];
        $idterminal_ns = $datos_pedido["idterminal_ns"];
        $pass = $datos_pedido["password"];
        $pass_ns = $datos_pedido["password_ns"];
        $jetid = $datos_pedido["jetid"];
        $jetid_ns = $datos_pedido["jetid_ns"];

        // BANKSTORE JET

        $token = Tools::getIsset('paytpvToken') ? Tools::getValue('paytpvToken') : "";
        $savecard_jet = Tools::getIsset('paytpv_savecard') ? 1 : 0;

        $jetPayment = 0;
        if ($token && Tools::strlen($token) == 64) {
            // PAGO SEGURO
            if ($idterminal > 0) {
                $secure_pay = $paytpv->isSecureTransaction($idterminal, $total_pedido, 0) ? 1 : 0;
            } else {
                $secure_pay = $paytpv->isSecureTransaction($idterminal_ns, $total_pedido, 0) ? 1 : 0;
            }

            // Miramos a ver por que terminal enviamos la operacion
            if ($secure_pay) {
                $idterminal_sel = $idterminal;
                $pass_sel = $pass;
                $jetid_sel = $jetid;
            } else {
                $idterminal_sel = $idterminal_ns;
                $pass_sel = $pass_ns;
                $jetid_sel = $jetid_ns;
            }

            if ($paytpv->apikey != '') {
                $notify = 2; // No notificar HTTP

                $apiRest = new PaycometApiRest($paytpv->apikey);
                $addUserResponse = $apiRest->addUser(
                    $idterminal_sel,
                    $token,
                    $this->context->cart->id,
                    $notify
                );
                $addUserResponseErrorCode = $addUserResponse->errorCode;

                $idUser = $addUserResponse->idUser;
                $tokenUser = $addUserResponse->tokenUser;
            } else {
                $client = new WSClient(
                    array(
                        'endpoint_paytpv' => $paytpv->endpoint_paytpv,
                        'clientcode' => $paytpv->clientcode,
                        'term' => $idterminal_sel,
                        'pass' => $pass_sel,
                        'jetid' => $jetid_sel
                    )
                );

                $addUserResponse = $client->addUserToken($token);
                $addUserResponseErrorCode = $addUserResponse[ 'DS_ERROR_ID' ];
                $idUser = $addUserResponse["DS_IDUSER"];
                $tokenUser = $addUserResponse["DS_TOKEN_USER"];
            }

            if ((int) $addUserResponseErrorCode > 0) {
                $this->context->smarty->assign(
                    'error_msg',
                    $paytpv->l('Cannot operate with given credit card', 'capture')
                );
                $this->context->smarty->assign(
                    array(
                        'this_path' => Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'modules/' .
                         $this->module->name . '/',
                        'base_dir' =>  __PS_BASE_URI__
                    )
                );
                $this->setTemplate('module:paytpv/views/templates/front/payment_fail.tpl');
                return;
            } else {
                $data = array();
                $data["IDUSER"] = $idUser;
                $data["TOKEN_USER"] = $tokenUser;

                $jetPayment = 1;
            }
            // TOKENIZED CARD
        } else {
            $data = PaytpvCustomer::getCardTokenCustomer(
                Tools::getValue('TOKEN_USER'),
                $this->context->cart->id_customer
            );

            if (!isset($data["IDUSER"])) {
                $this->context->smarty->assign(
                    'error_msg',
                    $paytpv->l('Cannot operate with given credit card', 'capture')
                );
                $this->context->smarty->assign(
                    array(
                    'this_path' => Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'modules/' .
                     $this->module->name . '/',
                    'base_dir' =>  __PS_BASE_URI__
                    )
                );
                $this->setTemplate('module:paytpv/views/templates/front/payment_fail.tpl');
                return;
            }

            if ($idterminal > 0) {
                $secure_pay = $paytpv->isSecureTransaction($idterminal, $total_pedido, $data["IDUSER"]) ? 1 : 0;
            } else {
                $secure_pay = $paytpv->isSecureTransaction($idterminal_ns, $total_pedido, $data["IDUSER"]) ? 1 : 0;
            }

            // Miramos a ver por que terminal enviamos la operacion
            if ($secure_pay) {
                $idterminal_sel = $idterminal;
                $pass_sel = $pass;
            } else {
                $idterminal_sel = $idterminal_ns;
                $pass_sel = $pass_ns;
            }
        }

        $suscription = (Tools::getIsset("paytpv_suscripcion"))?1:0;
        $periodicity = (Tools::getIsset("paytpv_periodicity"))?Tools::getValue("paytpv_periodicity"):0;
        $cycles = (Tools::getIsset("paytpv_cycles"))?Tools::getValue("paytpv_cycles"):0;

        PaytpvOrderInfo::saveOrderInfo(
            (int) $this->context->customer->id,
            $this->context->cart->id,
            $savecard_jet,
            $suscription,
            $periodicity,
            $cycles,
            $data["IDUSER"]
        );

        // Si el cliente solo tiene un terminal seguro, el segundo pago va siempre por seguro.
        // Si tiene un terminal NO Seguro ó ambos, el segundo pago siempre lo mandamos por NO Seguro

        $score = $paytpv->transactionScore($this->context->cart);
        $MERCHANT_SCORING = $scoring = $score["score"];

        $values = array(
            'id_cart' => (int) $this->context->cart->id,
            'key' => Context::getContext()->customer->secure_key
        );
        $ssl = Configuration::get('PS_SSL_ENABLED');

        // PAGO SEGURO
        if ($secure_pay) {
            $paytpv_order_ref = str_pad($this->context->cart->id, 8, "0", STR_PAD_LEFT);

            $URLOK = Context::getContext()->link->getModuleLink($paytpv->name, 'urlok', $values, $ssl);
            $URLKO = Context::getContext()->link->getModuleLink($paytpv->name, 'urlko', $values, $ssl);

            $language = $paytpv->getPaycometLang($this->context->language->language_code);

            $subscription_startdate = date("Ymd");
            $susc_periodicity = $periodicity;
            $subs_cycles = $cycles;

            // Si es indefinido, ponemos como fecha tope la fecha + 5 años.
            if ($subs_cycles == 0) {
                $subscription_enddate = date("Y") + 5 . date("m") . date("d");
            } else {
                // Dias suscripcion
                $dias_subscription = $subs_cycles * $susc_periodicity;
                $subscription_enddate = date('Ymd', strtotime("+" . $dias_subscription . " days"));
            }

            if ($paytpv->apikey == '') {
                if ($jetPayment &&
                    (Tools::getIsset('suscripcion') && Tools::getValue('suscripcion') == 1)
                ) {
                    $OPERATION = "110";
                    $signature = hash('sha512', $paytpv->clientcode . $data["IDUSER"] . $data['TOKEN_USER'] .
                    $idterminal_sel . $OPERATION . $paytpv_order_ref . $importe . $currency_iso_code . md5($pass_sel));
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
                        'IDUSER' => $data["IDUSER"],
                        'TOKEN_USER' => $data['TOKEN_USER'],
                        'URLOK' => $URLOK,
                        'URLKO' => $URLKO,
                        '3DSECURE' => $secure_pay
                    );
                } else {
                    $OPERATION = "109"; //exec_purchase_token
                    $signature = hash('sha512', $paytpv->clientcode . $data["IDUSER"] . $data['TOKEN_USER'] .
                     $idterminal_sel . $OPERATION . $paytpv_order_ref . $importe . $currency_iso_code . md5($pass_sel));

                    $fields = array(
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

                if ($MERCHANT_SCORING != null) {
                    $fields["MERCHANT_SCORING"] = $MERCHANT_SCORING;
                }

                $query = http_build_query($fields);

                $vhash = hash('sha512', md5($query . md5($pass_sel)));

                $salida = $paytpv->url_paytpv . "?" . $query . "&VHASH=" . $vhash;
            } else {
                $merchantData = $paytpv->getMerchantData($this->context->cart);
                $userInteraction = '1';
                $methodId = '1';

                $apiRest = new PaycometApiRest($paytpv->apikey);

                if ($jetPayment &&
                (Tools::getIsset("paytpv_suscripcion") && Tools::getValue("paytpv_suscripcion")==1)) {
                    $createSubscriptionResponse = $apiRest->createSubscription(
                        $subscription_startdate,
                        $subscription_enddate,
                        $susc_periodicity,
                        $idterminal_sel,
                        $methodId,
                        $paytpv_order_ref,
                        $importe,
                        $currency_iso_code,
                        Tools::getRemoteAddr(),
                        $data["IDUSER"],
                        $data['TOKEN_USER'],
                        $secure_pay,
                        $URLOK,
                        $URLKO,
                        $scoring,
                        '',
                        '',
                        $userInteraction,
                        [],
                        '',
                        '',
                        $merchantData
                    );
                    $salida = $createSubscriptionResponse->challengeUrl;
                } else {
                    try {
                        $executePurchaseResponse = $apiRest->executePurchase(
                            $idterminal_sel,
                            $paytpv_order_ref,
                            $importe,
                            $currency_iso_code,
                            $methodId,
                            Tools::getRemoteAddr(),
                            $secure_pay,
                            $data["IDUSER"],
                            $data['TOKEN_USER'],
                            $URLOK,
                            $URLKO,
                            $scoring,
                            '',
                            '',
                            $userInteraction,
                            [],
                            '',
                            '',
                            $merchantData
                        );

                        $salida = $URLKO;

                        if ($executePurchaseResponse->challengeUrl != "") {
                            $salida = $executePurchaseResponse->challengeUrl;
                        }
                    } catch (exception $e) {
                        $salida = $URLKO;
                    }
                }
            }

            Tools::redirect($salida);
            exit;
        }
        /* FIN AÑADIDO */

        $client = new WSClient(
            array(
                'endpoint_paytpv' => $paytpv->endpoint_paytpv,
                'clientcode' => $paytpv->clientcode,
                'term' => $idterminal_sel,
                'pass' => $pass_sel,
            )
        );
        $paytpv_order_ref = str_pad($this->context->cart->id, 8, "0", STR_PAD_LEFT);

        if ($jetPayment && (Tools::getIsset('paytpv_suscription') && Tools::getValue('paytpv_suscription') == 1)) {
            $subscription_startdate = date("Y-m-d");
            $susc_periodicity = Tools::getValue("paytpv_periodicity");
            $subs_cycles = Tools::getValue("paytpv_cycles");

            // Si es indefinido, ponemos como fecha tope la fecha + 10 años.
            if ($subs_cycles == 0) {
                $subscription_enddate = date("Y") + 5 . "-" . date("m") . "-" . date("d");
            } else {
                // Dias suscripcion
                $dias_subscription = $subs_cycles * $susc_periodicity;
                $subscription_enddate = date('Y-m-d', strtotime("+" . $dias_subscription . " days"));
            }

            if ($paytpv->apikey == '') {
                $charge = $client->createSubscriptionToken(
                    $data['IDUSER'],
                    $data['TOKEN_USER'],
                    $importe,
                    $subscription_startdate,
                    $subscription_enddate,
                    $susc_periodicity,
                    $MERCHANT_SCORING,
                    null,
                    $currency_iso_code,
                    $paytpv_order_ref
                );
            } else {
                $apiRest = new PaycometApiRest($paytpv->apikey);

                $URLOK=Context::getContext()->link->getModuleLink($paytpv->name, 'urlok', $values, $ssl);
                $URLKO=Context::getContext()->link->getModuleLink($paytpv->name, 'urlko', $values, $ssl);

                $userInteraction = '1';
                $methodId = '1';
                $merchantData = $paytpv->getMerchantData($this->context->cart);

                $createSubscriptionResponse = $apiRest->createSubscription(
                    $subscription_startdate,
                    $subscription_enddate,
                    $susc_periodicity,
                    $idterminal_sel,
                    $methodId,
                    $paytpv_order_ref,
                    $importe,
                    $currency_iso_code,
                    Tools::getRemoteAddr(),
                    $data["IDUSER"],
                    $data['TOKEN_USER'],
                    $secure_pay,
                    $URLOK,
                    $URLKO,
                    $scoring,
                    '',
                    '',
                    $userInteraction,
                    [],
                    '',
                    '',
                    $merchantData
                );

                if ($createSubscriptionResponse->challengeUrl != "") {
                    Tools::redirect($createSubscriptionResponse->challengeUrl);
                    exit;
                } else {
                    $charge = array();
                    $charge["DS_RESPONSE"] = ($createSubscriptionResponse->errorCode > 0)? 0 : 1;
                    $charge['DS_ERROR_ID'] = $createSubscriptionResponse->errorCode;
                }
            }
        } else {
            // REST
            if ($paytpv->apikey != '') {
                $apiRest = new PaycometApiRest($paytpv->apikey);
                $URLOK=Context::getContext()->link->getModuleLink($paytpv->name, 'urlok', $values, $ssl);
                $URLKO=Context::getContext()->link->getModuleLink($paytpv->name, 'urlko', $values, $ssl);

                $userInteraction = '1';
                $methodId = '1';
                $merchantData = $paytpv->getMerchantData($this->context->cart);

                try {
                    $executePurchaseResponse = $apiRest->executePurchase(
                        $idterminal_sel,
                        $paytpv_order_ref,
                        $importe,
                        $currency_iso_code,
                        $methodId,
                        Tools::getRemoteAddr(),
                        $secure_pay,
                        $data["IDUSER"],
                        $data['TOKEN_USER'],
                        $URLOK,
                        $URLKO,
                        '',
                        '',
                        '',
                        $userInteraction,
                        [],
                        '',
                        '',
                        $merchantData
                    );

                    $charge = array();
                    $charge["DS_RESPONSE"] = ($executePurchaseResponse->errorCode > 0)? 0 : 1;
                    $charge['DS_ERROR_ID'] = $executePurchaseResponse->errorCode;

                    if ($executePurchaseResponse->challengeUrl != "") {
                        Tools::redirect($executePurchaseResponse->challengeUrl);
                        exit;
                    }
                } catch (exception $e) {
                    $charge = array();
                    $charge["DS_RESPONSE"] = ($executePurchaseResponse->errorCode > 0)? 0 : 1;
                    $charge['DS_ERROR_ID'] = $executePurchaseResponse->errorCode;
                }
            } else {
                $charge = $client->executePurchase(
                    $data['IDUSER'],
                    $data['TOKEN_USER'],
                    $idterminal_sel,
                    $currency_iso_code,
                    $importe,
                    $paytpv_order_ref,
                    $MERCHANT_SCORING,
                    null
                );
            }
        }

        if ((Tools::getIsset($charge['DS_RESPONSE']) &&
            (int) $charge['DS_RESPONSE'] == 1) ||
            $charge['DS_ERROR_ID'] == 0
        ) {
            //Esperamos a que la notificación genere el pedido
            sleep(3);
            $id_order = Order::getOrderByCartId((int) $this->context->cart->id);

            if ($jetPayment) {
                $importe_ps  = number_format($importe / 100, 2, ".", "");

                // Save paytpv order
                PaytpvOrder::addOrder(
                    $data['IDUSER'],
                    $data['TOKEN_USER'],
                    0,
                    $this->context->cart->id_customer,
                    $id_order,
                    $importe_ps
                );

                if ($savecard_jet == 1) {
                    $result = array();
                    if ($paytpv->apikey != '') {
                        $apiRest = new PaycometApiRest($paytpv->apikey);

                        $infoUserResponse = $apiRest->infoUser(
                            $data['IDUSER'],
                            $data['TOKEN_USER'],
                            $idterminal_sel
                        );

                        $result['DS_MERCHANT_PAN'] = $infoUserResponse->pan;
                        $result['DS_CARD_BRAND'] = $infoUserResponse->cardBrand;
                    } else {
                        $result = $client->infoUser($data['IDUSER'], $data['TOKEN_USER']);
                    }

                    $result = $paytpv->saveCard(
                        $this->context->cart->id_customer,
                        $data['IDUSER'],
                        $data['TOKEN_USER'],
                        $result['DS_MERCHANT_PAN'],
                        $result['DS_CARD_BRAND']
                    );
                }
            }

            $values = array(
                'id_cart' => $this->context->cart->id,
                'id_module' => (int) $this->module->id,
                'id_order' => $id_order,
                'key' => $this->context->customer->secure_key
            );

            Tools::redirect(Context::getContext()->link->getPageLink('order-confirmation', $this->ssl, null, $values));
            return;
        } else {
            //se anota el pedido como no pagado
            ClassRegistro::add(
                $this->context->cart->id_customer,
                $this->context->cart->id,
                $importe,
                $charge['DS_RESPONSE']
            );
        }

        $this->context->smarty->assign('error_msg', $paytpv->l('Cannot operate with given credit card', 'capture'));
        $this->context->smarty->assign('base_dir', __PS_BASE_URI__);
        $this->setTemplate('module:paytpv/views/templates/front/payment_fail.tpl');
    }
}
