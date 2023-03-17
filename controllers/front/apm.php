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
class PaytpvApmModuleFrontController extends ModuleFrontController
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
        $id_cart = (int) Tools::getValue('id_cart');
        $url = (string) Tools::getValue('url');
        $methodId = (string) Tools::getValue('methodId');
        $paytpv = $this->module;

        $cart = new Cart($id_cart);
        $datos_pedido = $paytpv->terminalCurrency($cart);
        $importe = $datos_pedido["importe"];
        $currency_iso_code = $datos_pedido["currency_iso_code"];
        $idterminal = $datos_pedido["idterminal"];
        $paytpv_order_ref = str_pad($cart->id, 8, "0", STR_PAD_LEFT);
        $merchantData = $paytpv->getMerchantData($cart);
        $ssl = Configuration::get('PS_SSL_ENABLED');
        $values = array(
            'id_cart' => $cart->id,
            'key' => Context::getContext()->customer->secure_key
        );
        $URLOK = Context::getContext()->link->getModuleLink($paytpv->name, 'urlok', $values, $ssl);
        $URLKO = Context::getContext()->link->getModuleLink($paytpv->name, 'urlko', $values, $ssl);

        $apiRest = new PaycometApiRest($paytpv->apikey);

        $ssl = Configuration::get('PS_SSL_ENABLED');

        $userInteraction = 1;
        $secure_pay = 1;
        $productDescription = '';

        if (isset(Context::getContext()->customer->email)) $productDescription = Context::getContext()->customer->email;

        $score = $paytpv->transactionScore($cart);
        $scoring = $score["score"];

        $executePurchaseResponse = $apiRest->executePurchase(
            $idterminal,
            $paytpv_order_ref,
            $importe,
            $currency_iso_code,
            $methodId,
            Tools::getRemoteAddr(),
            $secure_pay,
            '',
            '',
            $URLOK,
            $URLKO,
            $scoring,
            $productDescription,
            '',
            $userInteraction,
            [],
            '',
            '',
            $merchantData,
            1
        );

        // Hay challenge
        if (isset($executePurchaseResponse->challengeUrl) &&
            $executePurchaseResponse->challengeUrl != ""
        ) {
            $url = $executePurchaseResponse->challengeUrl;
            // Generar Pedido en los metodos Asincronos
            if ($paytpv->APMAsynchronous($methodId)) {
                if (Configuration::get("PS_CHECKOUT_STATE_WAITING_LOCAL_PAYMENT") == '') {
                    $this->createSatusWaitingLocalPayment();
                }
                $displayName = $paytpv->displayName . " [" . $paytpv->getAPMName($methodId) . "]";
                $message = '';

                if($methodId == 16) {
                    $message = json_encode($executePurchaseResponse->methodData) . '|';
                }

                $paytpv->validateOrder(
                    $id_cart,
                    Configuration::get("PS_CHECKOUT_STATE_WAITING_LOCAL_PAYMENT"),
                    0,
                    $displayName,
                    $message
                );
            }
        } else {
            $url = $URLKO;
        }

        Tools::redirect($url);
        exit;
    }

    public function createSatusWaitingLocalPayment()
    {
        $sql = 'select max(id_order_state) max_id from ' . _DB_PREFIX_ . 'order_state_lang';
        $result = Db::getInstance()->getRow($sql);
        $id = $result["max_id"] + 1;
        $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'order_state_lang
        VALUES(' . $id . ',' . 1 . ',"' . "Esperando el pago con un método de pago local" . '","' . "payment".'")';
        Db::getInstance()->Execute($sql);
        $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'order_state_lang
        VALUES(' . $id . ',' . 2 . ',"' . "Waiting for Local Payment Method Payment" . '","' . "payment".'")';
        Db::getInstance()->Execute($sql);
        $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'order_state
        VALUES(' . $id . ',' . 0 . ',' . 0 . ',"' . "ps_checkout". '","' . "#34209E" . '",' . 1 . ',' . 0 . ',' . 0 . ',
        ' . 0 . ',' . 0 . ',' . 0 . ',' . 0 . ',' . 0 . ',' . 0 .')';
        Db::getInstance()->Execute($sql);
        Configuration::updateValue('PS_CHECKOUT_STATE_WAITING_LOCAL_PAYMENT', $id);
    }
}
