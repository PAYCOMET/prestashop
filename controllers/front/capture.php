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

        $datos_pedido = $paytpv->terminalCurrency($this->context->cart);
        $importe = $datos_pedido["importe"];
        $currency_iso_code = $datos_pedido["currency_iso_code"];
        $idterminal = $datos_pedido["idterminal"];        
        
        // BANKSTORE JET

        $token = Tools::getIsset('paytpvToken') ? Tools::getValue('paytpvToken') : "";
        $savecard_jet = Tools::getIsset('paytpv_savecard') ? 1 : 0;

        $jetPayment = 0;
        $secure_pay = true;

        if ($token && Tools::strlen($token) == 64) {
            if ($paytpv->apikey != '') {
                $notify = 2; // No notificar HTTP

                $apiRest = new PaycometApiRest($paytpv->apikey);
                $addUserResponse = $apiRest->addUser(
                    $idterminal,
                    $token,
                    $this->context->cart->id,
                    '',
                    'ES',
                    $notify
                );

                $addUserResponseErrorCode = $addUserResponse->errorCode;

                if ($addUserResponse->errorCode == 0) {
                    $idUser = $addUserResponse->idUser;
                    $tokenUser = $addUserResponse->tokenUser;
                }
            } else {
                $addUserResponseErrorCode = 1004;
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
        $scoring = $score["score"];

        $values = array(
            'id_cart' => (int) $this->context->cart->id,
            'key' => Context::getContext()->customer->secure_key
        );
        $ssl = Configuration::get('PS_SSL_ENABLED');


        /* INICIO PAGO SEGURO */
        $paytpv_order_ref = str_pad($this->context->cart->id, 8, "0", STR_PAD_LEFT);

        $URLOK = Context::getContext()->link->getModuleLink($paytpv->name, 'urlok', $values, $ssl);
        $URLKO = Context::getContext()->link->getModuleLink($paytpv->name, 'urlko', $values, $ssl);

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

        if ($paytpv->apikey != '') {
            $merchantData = $paytpv->getMerchantData($this->context->cart);            
            $userInteraction = '1';
            $methodId = '1';
            $notifyDirectPayment = 1;

            $apiRest = new PaycometApiRest($paytpv->apikey);

            if ($jetPayment &&
            (Tools::getIsset("paytpv_suscripcion") && Tools::getValue("paytpv_suscripcion")==1)) {
                $createSubscriptionResponse = $apiRest->createSubscription(
                    $subscription_startdate,
                    $subscription_enddate,
                    $susc_periodicity,
                    $idterminal,
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

                // Hay challenge
                if (isset($createSubscriptionResponse->challengeUrl) &&
                    $createSubscriptionResponse->challengeUrl != ""
                ) {
                    $salida = $createSubscriptionResponse->challengeUrl;
                // Frictionless
                } elseif (isset($createSubscriptionResponse->errorCode) &&
                    $createSubscriptionResponse->errorCode == 0 &&
                    isset($createSubscriptionResponse->authCode) &&
                    $createSubscriptionResponse->authCode != "") {
                    $salida = $URLOK;
                // Error
                } else {
                    $salida = $URLKO;
                }
            } else {
                try {
                    $executePurchaseResponse = $apiRest->executePurchase(
                        $idterminal,
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
                        $merchantData,
                        $notifyDirectPayment
                    );

                    // Hay challenge
                    if (isset($executePurchaseResponse->challengeUrl) &&
                        $executePurchaseResponse->challengeUrl != ""
                    ) {
                        $salida = $executePurchaseResponse->challengeUrl;
                    // Frictionless
                    } elseif (isset($executePurchaseResponse->errorCode) &&
                        $executePurchaseResponse->errorCode == 0 &&
                        isset($executePurchaseResponse->authCode) &&
                        $executePurchaseResponse->authCode != "") {
                        $salida = $URLOK;
                    // Error
                    } else {
                        $salida = $URLKO;
                    }
                } catch (exception $e) {
                    $salida = $URLKO;
                }
            }
        } else {
            $salida = $URLKO;
        }

        Tools::redirect($salida);
        exit;
    }
}
