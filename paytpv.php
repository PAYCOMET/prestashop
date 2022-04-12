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

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

if (!defined('_PS_VERSION_')) {
    exit;
}

include_once dirname(__FILE__) . '/classes/PaytpvTerminal.php';
include_once dirname(__FILE__) . '/classes/PaytpvOrder.php';
include_once dirname(__FILE__) . '/classes/PaytpvOrderInfo.php';
include_once dirname(__FILE__) . '/classes/PaytpvCustomer.php';
include_once dirname(__FILE__) . '/classes/PaytpvSuscription.php';
include_once dirname(__FILE__) . '/classes/PaytpvRefund.php';
include_once dirname(__FILE__) . '/classes/PaytpvPaymentsHelperForm.php';
include_once dirname(__FILE__) . '/classes/PaycometApiRest.php';


class Paytpv extends PaymentModule
{

    private $html = '';

    private $postErrors = array();

    public function __construct()
    {
        $this->name = 'paytpv';
        $this->tab = 'payments_gateways';
        $this->author = 'Paycomet';
        $this->version = '7.7.15';
        $this->module_key = 'deef285812f52026197223a4c07221c4';

        $this->is_eu_compatible = 1;
        $this->ps_versions_compliancy = array('min' => '1.7');
        $this->controllers = array('payment', 'validation');

        $this->bootstrap = true;
        // Array config:  configuration values
        $config = $this->getConfigValues();

        $this->url_paytpv       = "https://api.paycomet.com/gateway/ifr-bankstore";
        $this->endpoint_paytpv  = "https://api.paycomet.com/gateway/xml-bankstore";
        $this->jet_paytpv       = "https://api.paycomet.com/gateway/paycomet.jetiframe.js";

        if (isset($config['PAYTPV_INTEGRATION'])) {
            $this->integration = $config['PAYTPV_INTEGRATION'];
        }
        if (isset($config['PAYTPV_CLIENTCODE'])) {
            $this->clientcode = $config['PAYTPV_CLIENTCODE'];
        }
        if (array_key_exists('PAYTPV_APIKEY', $config)) {
            $this->apikey = $config['PAYTPV_APIKEY'];
        }
        if (isset($config['PAYTPV_NEWPAGEPAYMENT'])) {
            $this->newpage_payment = $config['PAYTPV_NEWPAGEPAYMENT'];
        }
        if (array_key_exists('PAYTPV_IFRAME_HEIGHT', $config) && $config['PAYTPV_IFRAME_HEIGHT']>=440) {
            $this->iframe_height = $config['PAYTPV_IFRAME_HEIGHT'];
        } else {
            $this->iframe_height = "440"; // Valor por defecto
        }
        if (isset($config['PAYTPV_SUSCRIPTIONS'])) {
            $this->suscriptions = $config['PAYTPV_SUSCRIPTIONS'];
        }
        if (isset($config['PAYTPV_FIRSTPURCHASE_SCORING'])) {
            $this->firstpurchase_scoring = $config['PAYTPV_FIRSTPURCHASE_SCORING'];
        }
        if (isset($config['PAYTPV_FIRSTPURCHASE_SCORING_SCO'])) {
            $this->firstpurchase_scoring_score = $config['PAYTPV_FIRSTPURCHASE_SCORING_SCO'];
        }
        if (isset($config['PAYTPV_SESSIONTIME_SCORING'])) {
            $this->sessiontime_scoring = $config['PAYTPV_SESSIONTIME_SCORING'];
        }
        if (isset($config['PAYTPV_SESSIONTIME_SCORING_VAL'])) {
            $this->sessiontime_scoring_val = $config['PAYTPV_SESSIONTIME_SCORING_VAL'];
        }
        if (isset($config['PAYTPV_SESSIONTIME_SCORING_SCORE'])) {
            $this->sessiontime_scoring_score = $config['PAYTPV_SESSIONTIME_SCORING_SCORE'];
        }
        if (isset($config['PAYTPV_DCOUNTRY_SCORING'])) {
            $this->dcountry_scoring = $config['PAYTPV_DCOUNTRY_SCORING'];
        }
        if (isset($config['PAYTPV_DCOUNTRY_SCORING_VAL'])) {
            $this->dcountry_scoring_val = $config['PAYTPV_DCOUNTRY_SCORING_VAL'];
        }
        if (isset($config['PAYTPV_DCOUNTRY_SCORING_SCORE'])) {
            $this->dcountry_scoring_score = $config['PAYTPV_DCOUNTRY_SCORING_SCORE'];
        }
        if (isset($config['PAYTPV_IPCHANGE_SCORING'])) {
            $this->ip_change_scoring = $config['PAYTPV_IPCHANGE_SCORING'];
        }
        if (isset($config['PAYTPV_IPCHANGE_SCORING_SCORE'])) {
            $this->ip_change_scoring_score = $config['PAYTPV_IPCHANGE_SCORING_SCORE'];
        }
        if (isset($config['PAYTPV_BROWSER_SCORING'])) {
            $this->browser_scoring = $config['PAYTPV_BROWSER_SCORING'];
        }
        if (isset($config['PAYTPV_BROWSER_SCORING_SCORE'])) {
            $this->browser_scoring_score = $config['PAYTPV_BROWSER_SCORING_SCORE'];
        }
        if (isset($config['PAYTPV_SO_SCORING'])) {
            $this->so_scoring = $config['PAYTPV_SO_SCORING'];
        }
        if (isset($config['PAYTPV_SO_SCORING_SCORE'])) {
            $this->so_scoring_score = $config['PAYTPV_SO_SCORING_SCORE'];
        }
        if (isset($config['PAYTPV_DISABLEOFFERSAVECARD'])) {
            $this->disableoffersavecard = $config['PAYTPV_DISABLEOFFERSAVECARD'];
        }

        if (array_key_exists('PAYTPV_APM_tarjeta', $config)) {
            $this->paytpv_apm_tarjeta = $config['PAYTPV_APM_tarjeta'];
        }
        if (array_key_exists('PAYTPV_APM_bizum', $config)) {
            $this->paytpv_apm_bizum = $config['PAYTPV_APM_bizum'];
        }
        if (array_key_exists('PAYTPV_APM_klarna_paynow', $config)) {
            $this->paytpv_apm_klarna = $config['PAYTPV_APM_klarna_paynow'];
        }
        if (array_key_exists('PAYTPV_APM_ideal', $config)) {
            $this->paytpv_apm_giropay = $config['PAYTPV_APM_ideal'];
        }
        if (array_key_exists('PAYTPV_APM_giropay', $config)) {
            $this->paytpv_apm_giropay = $config['PAYTPV_APM_giropay'];
        }
        if (array_key_exists('PAYTPV_APM_mybank', $config)) {
            $this->paytpv_apm_mybank = $config['PAYTPV_APM_mybank'];
        }
        if (array_key_exists('PAYTPV_APM_multibanco_sibs', $config)) {
            $this->paytpv_apm_multibanco = $config['PAYTPV_APM_multibanco_sibs'];
        }
        if (array_key_exists('PAYTPV_APM_trustly', $config)) {
            $this->paytpv_apm_trustly = $config['PAYTPV_APM_trustly'];
        }
        if (array_key_exists('PAYTPV_APM_przelewy24', $config)) {
            $this->paytpv_apm_przelewy24 = $config['PAYTPV_APM_przelewy24'];
        }
        if (array_key_exists('PAYTPV_APM_bancontact', $config)) {
            $this->paytpv_apm_bancontact = $config['PAYTPV_APM_bancontact'];
        }
        if (array_key_exists('PAYTPV_APM_eps', $config)) {
            $this->paytpv_apm_eps = $config['PAYTPV_APM_eps'];
        }
        if (array_key_exists('PAYTPV_APM_tele2', $config)) {
            $this->paytpv_apm_tele2 = $config['PAYTPV_APM_tele2'];
        }
        if (array_key_exists('PAYTPV_APM_paysera', $config)) {
            $this->paytpv_apm_paysera = $config['PAYTPV_APM_paysera'];
        }
        if (array_key_exists('PAYTPV_APM_postfinance', $config)) {
            $this->paytpv_apm_postfinance = $config['PAYTPV_APM_postfinance'];
        }
        if (array_key_exists('PAYTPV_APM_qiwi_wallet', $config)) {
            $this->paytpv_apm_qiwi = $config['PAYTPV_APM_qiwi_wallet'];
        }
        if (array_key_exists('PAYTPV_APM_yandex_money', $config)) {
            $this->paytpv_apm_yandex = $config['PAYTPV_APM_yandex_money'];
        }
        if (array_key_exists('PAYTPV_APM_mts', $config)) {
            $this->paytpv_apm_mts = $config['PAYTPV_APM_mts'];
        }
        if (array_key_exists('PAYTPV_APM_beeline', $config)) {
            $this->paytpv_apm_beeline = $config['PAYTPV_APM_beeline'];
        }
        if (array_key_exists('PAYTPV_APM_paysafecard', $config)) {
            $this->paytpv_apm_paysafecard = $config['PAYTPV_APM_paysafecard'];
        }
        if (array_key_exists('PAYTPV_APM_skrill', $config)) {
            $this->paytpv_apm_skrill = $config['PAYTPV_APM_skrill'];
        }
        if (array_key_exists('PAYTPV_APM_webmoney', $config)) {
            $this->paytpv_apm_webmoney = $config['PAYTPV_APM_webmoney'];
        }
        if (array_key_exists('PAYTPV_APM_klarna_payments', $config)) {
            $this->paytpv_apm_klarna_payments = $config['PAYTPV_APM_klarna_payments'];
        }
        if (array_key_exists('PAYTPV_APM_paypal', $config)) {
            $this->paytpv_apm_paypal = $config['PAYTPV_APM_paypal'];
        }

        // Instant Credit ---------------------------------------------
        if (array_key_exists('PAYTPV_APM_instant_credit_simuladorCoutas', $config)) {
            $this->paytpv_apm_instant_credit_simulador = $config['PAYTPV_APM_instant_credit_simuladorCoutas'];
        }

        if (array_key_exists('PAYTPV_APM_instant_credit_environment', $config)) {
            $this->paytpv_apm_instant_credit_environment = $config['PAYTPV_APM_instant_credit_environment'];
        }

        if (array_key_exists('PAYTPV_APM_instant_credit', $config)) {
            $this->paytpv_apm_instant_credit = $config['PAYTPV_APM_instant_credit'];
        }

        if (array_key_exists('PAYTPV_APM_instant_credit_hashToken', $config)) {
            $this->paytpv_apm_instant_credit_hashToken = $config['PAYTPV_APM_instant_credit_hashToken'];
        }

        if (!empty($config['PAYTPV_APM_instant_credit_minFin'])) {
            $this->paytpv_apm_instant_credit_minFin = $config['PAYTPV_APM_instant_credit_minFin'];
        } else {
            $this->paytpv_apm_instant_credit_minFin = 0;
        }

        if (!empty($config['PAYTPV_APM_instant_credit_maxFin'])) {
            $this->paytpv_apm_instant_credit_maxFin = $config['PAYTPV_APM_instant_credit_maxFin'];
        } else {
            $this->paytpv_apm_instant_credit_maxFin = 0;
        }
        // Fin Instant Credit ------------------------------------------------------------



        parent::__construct();
        $this->page = basename(__FILE__, '.php');

        $this->displayName = $this->l('Paycomet');
        $this->description = $this->l('This module allows you to accept card payments via www.paycomet.com');

        try {
            if (!isset($this->apikey) || !isset($this->clientcode) or !PaytpvTerminal::existTerminal()) {
                $this->warning = $this->l('Missing data when configuring the module PAYCOMET');
            }
        } catch (exception $e) {
        }
    }

    public function runUpgradeModule()
    {
        parent::runUpgradeModule();
    }


    public function install()
    {

        include_once(_PS_MODULE_DIR_ . '/' . $this->name . '/paytpv_install.php');
        $paytpv_install = new PayTpvInstall();
        $res = $paytpv_install->createTables();
        if (!$res) {
            $this->error = $this->l('Missing data when configuring the module PAYCOMET');
            return false;
        }

        $paytpv_install->updateConfiguration();

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
            !$this->registerHook('header') || 
            !$this->registerHook('displayOrderConfirmation') || 
            !$this->registerHook('displayOrderDetail')
        ) {
            return false;
        }

        return true;
    }



    public function uninstall()
    {
        include_once(_PS_MODULE_DIR_ . '/' . $this->name . '/paytpv_install.php');
        $paytpv_install = new PayTpvInstall();
        $paytpv_install->deleteConfiguration();
        return parent::uninstall();
    }

    public function getPath()
    {
        return $this->_path;
    }

    private function postValidation()
    {

        // Show error when required fields.
        if (Tools::getIsset('btnSubmit')) {
            if (empty(Tools::getValue('apikey'))) {
                $this->postErrors[] = $this->l('API Key required');
            }
            if (!Tools::getIsset('clientcode')) {
                $this->postErrors[] = $this->l('Client Code required');
            }
            if (!Tools::getIsset('pass')) {
                $this->postErrors[] = $this->l('User Password required');
            }

            if (Tools::getValue('newpage_payment') != 2
                && (!filter_var(Tools::getValue('iframe_height'), FILTER_VALIDATE_INT) ||
                Tools::getValue('iframe_height') < 440)
            ) {
                $this->postErrors[] = $this->l('The height of the iframe must be at least 440');
            }

            // Check Terminal empty fields SECURE
            foreach (Tools::getValue('term') as $key => $term) {
                if ($term == "" || !is_numeric($term)) {
                    $this->postErrors[] = $this->l('Terminal') . " " . ($key + 1) . "º " .
                                        $this->l('Terminal number invalid');
                }
                if (Tools::getValue('pass')[$key] == "") {
                    $this->postErrors[] = $this->l('Terminal') . " " . ($key + 1) . "º " .
                                        $this->l('Password invalid');
                }
                if (Tools::getValue('jetid')[$key] == "" && Tools::getValue('integration') == 1) {
                    $this->postErrors[] = $this->l('Terminal') . " " . ($key + 1) . "º " .
                                            $this->l('JET ID number invalid');
                }
            }

            // Check Duplicate Terms
            $arrTerminales = array_unique(Tools::getValue('term'));
            if (sizeof($arrTerminales) != sizeof(Tools::getValue('term'))) {
                $this->postErrors[] = $this->l('Duplicate Terminals');
            }

            // Check Duplicate Currency
            $arrMonedas = array_unique(Tools::getValue('moneda'));
            if (sizeof($arrMonedas) != sizeof(Tools::getValue('moneda'))) {
                $this->postErrors[] = $this->l('Duplicate Currency. Specify a different currency for each terminal');
            }

            // Si no hay errores previos se contrastan los datos
            if (!sizeof($this->postErrors)) {
                $arrValidatePaycomet = $this->validatePaycomet();
                if ($arrValidatePaycomet["error"] != 0) {
                    $this->postErrors[] = $arrValidatePaycomet["error_txt"];
                }
            }
        }
    }

    private function validatePaycomet()
    {
        include_once(_PS_MODULE_DIR_ . '/paytpv/classes/PaytpvApi.php');

        $api = new PaytpvApi();

        $arrDatos = array();
        $arrDatos["error"] = 0;

        // Validación de los datos en Paycomet
        foreach (array_keys(Tools::getValue("term")) as $key) {
            $term = (Tools::getValue('term')[$key] == '') ? "" : Tools::getValue('term')[$key];
            $terminales_txt = $this->l('Secure');
            $resp = $api->validatePaycomet(
                Tools::getValue('clientcode'),
                $term,
                Tools::getValue("pass")[$key],
                "CES"
            );

            if ($resp["DS_RESPONSE"] != 1) {
                $arrDatos["error"] = 1;
                switch ($resp["DS_ERROR_ID"]) {
                    case 1121:  // No se encuentra el cliente
                    case 1130:  // No se encuentra el producto
                    case 1003:  // Credenciales inválidas
                    case 127:   // Parámetro no válido.
                        $arrDatos["error_txt"] = $this->l(
                            'Check that the Client Code, Terminal and Password are correct.'
                        );
                        break;
                    case 1337:  // Ruta de notificación no configurada
                        $arrDatos["error_txt"] = $this->l(
                            'Notification URL is not defined in the product
                             configuration of your account PAYCOMET account.'
                        );
                        break;
                    case 28:    // Curl
                    case 1338:  // Ruta de notificación no responde correctamente
                        $ssl = Configuration::get('PS_SSL_ENABLED');
                        $arrDatos["error_txt"] = $this->l('The notification URL defined in the product configuration
                         of your PAYCOMET account does not respond correctly. Verify that it has been defined as: ')
                        . Context::getContext()->link->getModuleLink($this->name, 'url', array(), $ssl);
                        break;
                    case 1339:  // Configuración de terminales incorrecta
                        $arrDatos["error_txt"] = $this->l(
                            'Your Product in PAYCOMET account is not set up with the Available Terminals option: '
                        ) . $terminales_txt;
                        break;
                }
            }
        }

        return $arrDatos;
    }



    private function postProcess()
    {

        // Update databse configuration
        if (Tools::getIsset('btnSubmit')) {
            Configuration::updateValue('PAYTPV_CLIENTCODE', Tools::getValue('clientcode'));
            Configuration::updateValue('PAYTPV_APIKEY', trim(Tools::getValue('apikey')));
            Configuration::updateValue('PAYTPV_NEWPAGEPAYMENT', Tools::getValue('newpage_payment'));
            Configuration::updateValue('PAYTPV_IFRAME_HEIGHT', Tools::getValue('iframe_height'));
            Configuration::updateValue('PAYTPV_SUSCRIPTIONS', Tools::getValue('suscriptions'));
            Configuration::updateValue('PAYTPV_INTEGRATION', Tools::getValue('integration'));
            // Save Paytpv Terminals
            PaytpvTerminal::removeTerminals();

            foreach (array_keys(Tools::getValue("term")) as $key) {
                $aux_term = (Tools::getValue('term')[$key]=='')?"":Tools::getValue('term')[$key];
                PaytpvTerminal::addTerminal(
                    $key+1,
                    trim($aux_term),
                    trim(Tools::getValue("pass")[$key]),
                    trim(Tools::getValue("jetid")[$key]),
                    trim(Tools::getValue("moneda")[$key]),
                    Tools::getValue("dcc")[$key]
                );
            }

            //APMs
            Configuration::updateValue(
                'PAYTPV_APM_tarjeta',
                Tools::getIsset('apms_tarjeta') ? Tools::getValue('apms_tarjeta') : 0
            );
            Configuration::updateValue('PAYTPV_APM_bizum', Tools::getValue('apms_bizum'));
            Configuration::updateValue('PAYTPV_APM_klarna_paynow', Tools::getValue('apms_klarna_paynow'));
            Configuration::updateValue('PAYTPV_APM_ideal', Tools::getValue('apms_ideal'));
            Configuration::updateValue('PAYTPV_APM_giropay', Tools::getValue('apms_giropay'));
            Configuration::updateValue('PAYTPV_APM_mybank', Tools::getValue('apms_mybank'));
            Configuration::updateValue('PAYTPV_APM_multibanco_sibs', Tools::getValue('apms_multibanco_sibs'));
            Configuration::updateValue('PAYTPV_APM_trustly', Tools::getValue('apms_trustly'));
            Configuration::updateValue('PAYTPV_APM_przelewy24', Tools::getValue('apms_przelewy24'));
            Configuration::updateValue('PAYTPV_APM_bancontact', Tools::getValue('apms_bancontact'));
            Configuration::updateValue('PAYTPV_APM_eps', Tools::getValue('apms_eps'));
            Configuration::updateValue('PAYTPV_APM_tele2', Tools::getValue('apms_tele2'));
            Configuration::updateValue('PAYTPV_APM_paysera', Tools::getValue('apms_paysera'));
            Configuration::updateValue('PAYTPV_APM_postfinance', Tools::getValue('apms_postfinance'));
            Configuration::updateValue('PAYTPV_APM_qiwi_wallet', Tools::getValue('apms_qiwi_wallet'));
            Configuration::updateValue('PAYTPV_APM_yandex_money', Tools::getValue('apms_yandex_money'));
            Configuration::updateValue('PAYTPV_APM_mts', Tools::getValue('apms_mts'));
            Configuration::updateValue('PAYTPV_APM_beeline', Tools::getValue('apms_beeline'));
            Configuration::updateValue('PAYTPV_APM_paysafecard', Tools::getValue('apms_paysafecard'));
            Configuration::updateValue('PAYTPV_APM_skrill', Tools::getValue('apms_skrill'));
            Configuration::updateValue('PAYTPV_APM_webmoney', Tools::getValue('apms_webmoney'));
            Configuration::updateValue('PAYTPV_APM_instant_credit', Tools::getValue('apms_instant_credit'));
            Configuration::updateValue('PAYTPV_APM_klarna_payments', Tools::getValue('apms_klarna_payments'));
            Configuration::updateValue('PAYTPV_APM_paypal', Tools::getValue('apms_paypal'));

            // Instan Credit
            Configuration::updateValue(
                'PAYTPV_APM_instant_credit_simuladorCoutas',
                Tools::getValue('apms_instant_credit_simuladorCoutas')
            );
            Configuration::updateValue(
                'PAYTPV_APM_instant_credit_environment',
                Tools::getValue('apms_instant_credit_environment')
            );
            Configuration::updateValue(
                'PAYTPV_APM_instant_credit_hashToken',
                Tools::getValue('apms_instant_credit_hashToken')
            );
            Configuration::updateValue(
                'PAYTPV_APM_instant_credit_minFin',
                Tools::getValue('apms_instant_credit_minFin')
            );
            Configuration::updateValue(
                'PAYTPV_APM_instant_credit_maxFin',
                Tools::getValue('apms_instant_credit_maxFin')
            );
            

            // Datos Scoring

            Configuration::updateValue('PAYTPV_FIRSTPURCHASE_SCORING', Tools::getValue('firstpurchase_scoring'));
            Configuration::updateValue(
                'PAYTPV_FIRSTPURCHASE_SCORING_SCO',
                Tools::getValue('firstpurchase_scoring_score')
            );
            Configuration::updateValue('PAYTPV_SESSIONTIME_SCORING', Tools::getValue('sessiontime_scoring'));
            Configuration::updateValue('PAYTPV_SESSIONTIME_SCORING_VAL', Tools::getValue('sessiontime_scoring_val'));
            Configuration::updateValue(
                'PAYTPV_SESSIONTIME_SCORING_SCORE',
                Tools::getValue('sessiontime_scoring_score')
            );
            Configuration::updateValue('PAYTPV_DCOUNTRY_SCORING', Tools::getValue('dcountry_scoring'));
            Configuration::updateValue(
                'PAYTPV_DCOUNTRY_SCORING_VAL',
                Tools::getIsset('dcountry_scoring_val') ? implode(",", Tools::getValue('dcountry_scoring_val')) : ''
            );
            Configuration::updateValue('PAYTPV_DCOUNTRY_SCORING_SCORE', Tools::getValue('dcountry_scoring_score'));
            Configuration::updateValue('PAYTPV_IPCHANGE_SCORING', Tools::getValue('ip_change_scoring'));
            Configuration::updateValue('PAYTPV_IPCHANGE_SCORING_SCORE', Tools::getValue('ip_change_scoring_score'));
            Configuration::updateValue('PAYTPV_BROWSER_SCORING', Tools::getValue('browser_scoring'));
            Configuration::updateValue('PAYTPV_BROWSER_SCORING_SCORE', Tools::getValue('browser_scoring_score'));
            Configuration::updateValue('PAYTPV_SO_SCORING', Tools::getValue('so_scoring'));
            Configuration::updateValue('PAYTPV_SO_SCORING_SCORE', Tools::getValue('so_scoring_score'));
            Configuration::updateValue('PAYTPV_DISABLEOFFERSAVECARD', Tools::getValue('disableoffersavecard'));


            return '<div class="bootstrap"><div class="alert alert-success">' . $this->l('Configuration updated') .
                '</div></div>';
        }
    }


    public function transactionScore($cart)
    {
        include_once(_PS_MODULE_DIR_ . '/paytpv/classes/PaytpvApi.php');

        $api = new PaytpvApi();

        $config = $this->getConfigValues();

        // Initialize array Score
        $arrScore = array();
        $arrScore["score"] = null;
        $arrScore["scoreCalc"] = null;

        $shipping_address_country = "";

        $shippingAddressData = new Address($cart->id_address_delivery);
        if ($shippingAddressData) {
            $address_country = new Country($shippingAddressData->id_country);
            $shipping_address_country = $address_country->iso_code;
        }

        // First Purchase
        if ($config["PAYTPV_FIRSTPURCHASE_SCORING"]) {
            $firstpurchase_scoring_score = $config["PAYTPV_FIRSTPURCHASE_SCORING_SCO"];
            if (PaytpvOrder::isFirstPurchaseCustomer($this->context->customer->id)) {
                $arrScore["scoreCalc"]["firstpurchase"] = $firstpurchase_scoring_score;
            }
        }

        // Complete Session Time
        if ($config["PAYTPV_SESSIONTIME_SCORING"]) {
            $sessiontime_scoring_val = $config["PAYTPV_SESSIONTIME_SCORING_VAL"];
            $sessiontime_scoring_score = $config["PAYTPV_SESSIONTIME_SCORING_SCORE"];

            $cookie = $this->context->cookie;
            if ($cookie && $cookie->id_connections) {
                $connection = new Connection($cookie->id_connections);
                $first_visit_at = $connection->date_add;

                $now = date('Y-m-d H:i:s');

                $time_ss = strtotime($now) - strtotime($first_visit_at);
                $time_mm = floor($time_ss / 60);

                if ($time_mm > $sessiontime_scoring_val) {
                    $arrScore["scoreCalc"]["completesessiontime"] = $sessiontime_scoring_score;
                }
            }
        }


        // Destination
        if ($config["PAYTPV_DCOUNTRY_SCORING"]) {
            $dcountry_scoring_val = explode(",", $config["PAYTPV_DCOUNTRY_SCORING_VAL"]);
            $dcountry_scoring_score = $config["PAYTPV_DCOUNTRY_SCORING_SCORE"];

            if (in_array($shipping_address_country, $dcountry_scoring_val)) {
                $arrScore["scoreCalc"]["destination"] = $dcountry_scoring_score;
            }
        }

        // Ip Change
        if ($config["PAYTPV_IPCHANGE_SCORING"]) {
            $connection = new Connection($cookie->id_connections);
            $ip_change_scoring = $config["PAYTPV_IPCHANGE_SCORING_SCORE"];
            $ip = Tools::getRemoteAddr() ? (int) ip2long(Tools::getRemoteAddr()) : '';
            $ip_session = $connection->ip_address ? (int) ip2long($connection->ip_address) : '';

            if ($ip != $ip_session) {
                $arrScore["scoreCalc"]["ipchange"] = $ip_change_scoring;
            }
        }

        // Browser Unidentified
        if ($config["PAYTPV_BROWSER_SCORING"]) {
            $browser_scoring_score = $config["PAYTPV_BROWSER_SCORING_SCORE"];
            if ($api->browserDetection('browser_name') == "") {
                $arrScore["scoreCalc"]["browser_unidentified"] = $browser_scoring_score;
            }
        }

        // Operating System Unidentified
        if ($config["PAYTPV_SO_SCORING"]) {
            $so_scoring_score = $config["PAYTPV_SO_SCORING_SCORE"];
            if ($api->browserDetection('os') == "") {
                $arrScore["scoreCalc"]["operating_system_unidentified"] = $so_scoring_score;
            }
        }

        // CALC ORDER SCORE
        if (isset($arrScore["scoreCalc"]) && sizeof($arrScore["scoreCalc"]) > 0) {
            //$score = floor(array_sum($arrScore["scoreCalc"]) / sizeof($arrScore["scoreCalc"]));   // Media
            $score = floor(array_sum($arrScore["scoreCalc"])); // Suma de valores. Si es superior a 100 asignamos 100
            if ($score > 100) {
                $score = 100;
            }
            $arrScore["score"] = $score;
        }

        return $arrScore;
    }

    public function threeDSRequestorAuthenticationInfo()
    {

        $customerStats = $this->context->customer->getStats();

        $threeDSReqAuthTimestamp = strftime('%Y%m%d%H%M', strtotime($customerStats['last_visit']));

        $threeDSRequestorAuthenticationInfo = array();
        $threeDSRequestorAuthenticationInfo["threeDSReqAuthData"] = "";
        $logged = $this->context->customer->isLogged();
        $threeDSRequestorAuthenticationInfo["threeDSReqAuthMethod"] = ($logged) ? "02" : "01";
        $threeDSRequestorAuthenticationInfo["threeDSReqAuthTimestamp"] = $threeDSReqAuthTimestamp;

        return $threeDSRequestorAuthenticationInfo;
    }


    public function acctInfo($cart)
    {

        $acctInfoData = array();
        $date_now = new DateTime("now");

        $isGuest = $this->context->customer->isGuest();
        if ($isGuest) {
            $acctInfoData["chAccAgeInd"] = "01";
        } else {
            $date_customer = new DateTime(strftime('%Y%m%d', strtotime($this->context->customer->date_add)));

            $diff = $date_now->diff($date_customer);
            $dias = $diff->days;

            if ($dias == 0) {
                $acctInfoData["chAccAgeInd"] = "02";
            } elseif ($dias < 30) {
                $acctInfoData["chAccAgeInd"] = "03";
            } elseif ($dias < 60) {
                $acctInfoData["chAccAgeInd"] = "04";
            } else {
                $acctInfoData["chAccAgeInd"] = "05";
            }
        }
        $acctInfoData["chAccChange"] = strftime('%Y%m%d', strtotime($this->context->customer->date_upd));

        $date_customer_upd = new DateTime(strftime('%Y%m%d', strtotime($this->context->customer->date_upd)));
        $diff = $date_now->diff($date_customer_upd);
        $dias_upd = $diff->days;

        if ($dias_upd == 0) {
            $acctInfoData["chAccChangeInd"] = "01";
        } elseif ($dias_upd < 30) {
            $acctInfoData["chAccChangeInd"] = "02";
        } elseif ($dias_upd < 60) {
            $acctInfoData["chAccChangeInd"] = "03";
        } else {
            $acctInfoData["chAccChangeInd"] = "04";
        }

        $acctInfoData["chAccDate"] = strftime('%Y%m%d', strtotime($this->context->customer->date_upd));
        //$acctInfoData["chAccPwChange"] = "";
        //$acctInfoData["chAccPwChangeInd"] = "";

        $acctInfoData["nbPurchaseAccount"] = PaytpvOrder::numPurchaseCustomer(
            $this->context->customer->id,
            1,
            6,
            "MONTH"
        );
        //$acctInfoData["provisionAttemptsDay"] = "";

        $acctInfoData["txnActivityDay"] = PaytpvOrder::numPurchaseCustomer(
            $this->context->customer->id,
            0,
            1,
            "DAY"
        );
        $acctInfoData["txnActivityYear"] = PaytpvOrder::numPurchaseCustomer(
            $this->context->customer->id,
            0,
            1,
            "YEAR"
        );


        $firstAddressDelivery = PaytpvOrder::firstAddressDelivery(
            $this->context->customer->id,
            $cart->id_address_delivery
        );
        if ($firstAddressDelivery != "") {
            $acctInfoData["shipAddressUsage"] = date("Ymd", strtotime($firstAddressDelivery));

            $date_firstAddressDelivery = new DateTime(strftime('%Y%m%d', strtotime($firstAddressDelivery)));
            $diff = $date_now->diff($date_firstAddressDelivery);
            $dias_firstAddressDelivery = $diff->days;
            if ($dias_firstAddressDelivery == 0) {
                $acctInfoData["shipAddressUsageInd"] = "01";
            } elseif ($dias_upd < 30) {
                $acctInfoData["shipAddressUsageInd"] = "02";
            } elseif ($dias_upd < 60) {
                $acctInfoData["shipAddressUsageInd"] = "03";
            } else {
                $acctInfoData["shipAddressUsageInd"] = "04";
            }
        }

        // Shiping info
        $shipping = new Address($cart->id_address_delivery);

        if (($this->context->customer->firstname != $shipping->firstname) ||
            ($this->context->customer->lastname != $shipping->lastname)
        ) {
            $acctInfoData["shipNameIndicator"] = "02";
        } else {
            $acctInfoData["shipNameIndicator"] = "01";
        }

        $acctInfoData["suspiciousAccActivity"] = "01";


        return $acctInfoData;
    }

    public function getShoppingCart($cart)
    {

        $shoppingCartData = array();


        $i = 0;
        $amount = 0;
        foreach ($cart->getProducts() as $key => $product) {

            if (is_int($product["quantity"])) {
                $shoppingCartData[$key + $i]["sku"] = "1";
                $shoppingCartData[$key + $i]["quantity"] = (int) $product["quantity"];
                $shoppingCartData[$key + $i]["unitPrice"] = number_format($product["price_without_reduction_without_tax"] * 100, 0, '.', ''); 
                $shoppingCartData[$key + $i]["name"] = $product["name"];
                $shoppingCartData[$key + $i]["category"] = $product["category"];
                $shoppingCartData[$key + $i]["articleType"] = ($product["is_virtual"] == 1)?8 : 5;
                $amount += $shoppingCartData[$key + $i]["unitPrice"] * $product["quantity"];
            } else {
                $shoppingCartData[$key + $i]["sku"] = "1";
                $shoppingCartData[$key + $i]["quantity"] = 1;
                $shoppingCartData[$key + $i]["unitPrice"] = number_format(($product["price_without_reduction_without_tax"] * $product["quantity"]) * 100, 0, '.', '');
                $shoppingCartData[$key + $i]["name"] = $product["name"];
                $shoppingCartData[$key + $i]["category"] = $product["category"];
                $shoppingCartData[$key + $i]["articleType"] = ($product["is_virtual"] == 1)?8 : 5;
                $amount += $shoppingCartData[$key + $i]["unitPrice"] * $product["quantity"];
            }

            // Se añade el descuento
            if ($product["reduction"] > 0) {
                $i++;
                $shoppingCartData[$key + $i]["sku"] = "1";
                $shoppingCartData[$key + $i]["quantity"] = 1;
                $shoppingCartData[$key + $i]["unitPrice"] = '-' . number_format($product["reduction"] * 100, 0, '.', '') * $product["quantity"];
                $shoppingCartData[$key + $i]["name"] = $product["name"];
                $shoppingCartData[$key + $i]["category"] = $product["category"];
                $shoppingCartData[$key + $i]["articleType"] = "4";
                $amount += $shoppingCartData[$key + $i]["unitPrice"];
            }
        }
        // Se calculan gastos de envio
        if ($cart->getPackageShippingCost() > 0) {
            $i++;
            $shoppingCartData[$key + $i]["sku"] = "1";
            $shoppingCartData[$key + $i]["quantity"] = 1;
            $shoppingCartData[$key + $i]["unitPrice"] = number_format($cart->getPackageShippingCost() * 100, 0, '.', '');
            $shoppingCartData[$key + $i]["name"] = "Package Shipping Cost";
            $shoppingCartData[$key + $i]["articleType"] = "6";
            $amount += $shoppingCartData[$key + $i]["unitPrice"];
        }

        // Se calculan los impuestos
        $tax = number_format($cart->getOrderTotal(true, Cart::BOTH) * 100, 0, '.', '') - $amount;
        $i++;
        $shoppingCartData[$key + $i]["sku"] = "1";
        $shoppingCartData[$key + $i]["quantity"] = 1;
        $shoppingCartData[$key + $i]["unitPrice"] = $tax;
        $shoppingCartData[$key + $i]["name"] = "Tax";
        $shoppingCartData[$key + $i]["articleType"] = "11";

        return array("shoppingCart" => array_values($shoppingCartData));
    }


    public function isoCodeToNumber($code)
    {
        $isoCodeNumber = 724; // Default value;

        $arrCode = array(
            "AF" => "004", "AX" => "248", "AL" => "008", "DE" => "276", "AD" => "020", "AO" => "024",
            "AI" => "660", "AQ" => "010", "AG" => "028", "SA" => "682", "DZ" => "012", "AR" => "032", "AM" => "051",
            "AW" => "533", "AU" => "036", "AT" => "040", "AZ" => "031", "BS" => "044", "BD" => "050", "BB" => "052",
            "BH" => "048", "BE" => "056", "BZ" => "084", "BJ" => "204", "BM" => "060", "BY" => "112", "BO" => "068",
            "BQ" => "535", "BA" => "070", "BW" => "072", "BR" => "076", "BN" => "096", "BG" => "100", "BF" => "854",
            "BI" => "108", "BT" => "064", "CV" => "132", "KH" => "116", "CM" => "120", "CA" => "124", "QA" => "634",
            "TD" => "148", "CL" => "52", "CN" => "156", "CY" => "196", "CO" => "170", "KM" => "174", "KP" => "408",
            "KR" => "410", "CI" => "384", "CR" => "188", "HR" => "191", "CU" => "192", "CW" => "531", "DK" => "208",
            "DM" => "212", "EC" => "218", "EG" => "818", "SV" => "222", "AE" => "784", "ER" => "232", "SK" => "703",
            "SI" => "705", "ES" => "724", "US" => "840", "EE" => "233", "ET" => "231", "PH" => "608", "FI" => "246",
            "FJ" => "242", "FR" => "250", "GA" => "266", "GM" => "270", "GE" => "268", "GH" => "288", "GI" => "292",
            "GD" => "308", "GR" => "300", "GL" => "304", "GP" => "312", "GU" => "316", "GT" => "320", "GF" => "254",
            "GG" => "831", "GN" => "324", "GW" => "624", "GQ" => "226", "GY" => "328", "HT" => "332", "HN" => "340",
            "HK" => "344", "HU" => "348", "IN" => "356", "ID" => "360", "IQ" => "368", "IR" => "364", "IE" => "372",
            "BV" => "074", "IM" => "833", "CX" => "162", "IS" => "352", "KY" => "136", "CC" => "166", "CK" => "184",
            "FO" => "234", "GS" => "239", "HM" => "334", "FK" => "238", "MP" => "580", "MH" => "584", "PN" => "612",
            "SB" => "090", "TC" => "796", "UM" => "581", "VG" => "092", "VI" => "850", "IL" => "376", "IT" => "380",
            "JM" => "388", "JP" => "392", "JE" => "832", "JO" => "400", "KZ" => "398", "KE" => "404", "KG" => "417",
            "KI" => "296", "KW" => "414", "LA" => "418", "LS" => "426", "LV" => "428", "LB" => "422", "LR" => "430",
            "LY" => "434", "LI" => "438", "LT" => "440", "LU" => "442", "MO" => "446", "MK" => "807", "MG" => "450",
            "MY" => "458", "MW" => "454", "MV" => "462", "ML" => "466", "MT" => "470", "MA" => "504", "MQ" => "474",
            "MU" => "480", "MR" => "478", "YT" => "175", "MX" => "484", "FM" => "583", "MD" => "498", "MC" => "492",
            "MN" => "496", "ME" => "499", "MS" => "500", "MZ" => "508", "MM" => "104", "NA" => "516", "NR" => "520",
            "NP" => "524", "NI" => "558", "NE" => "562", "NG" => "566", "NU" => "570", "NF" => "574", "NO" => "578",
            "NC" => "540", "NZ" => "554", "OM" => "512", "NL" => "528", "PK" => "586", "PW" => "585", "PS" => "275",
            "PA" => "591", "PG" => "598", "PY" => "600", "PE" => "604", "PF" => "258", "PL" => "616", "PT" => "620",
            "PR" => "630", "GB" => "826", "EH" => "732", "CF" => "140", "CZ" => "203", "CG" => "178", "CD" => "180",
            "DO" => "214", "RE" => "638", "RW" => "646", "RO" => "642", "RU" => "643", "WS" => "882", "AS" => "016",
            "BL" => "652", "KN" => "659", "SM" => "674", "MF" => "663", "PM" => "666", "VC" => "670", "SH" => "654",
            "LC" => "662", "ST" => "678", "SN" => "686", "RS" => "688", "SC" => "690", "SL" => "694", "SG" => "702",
            "SX" => "534", "SY" => "760", "SO" => "706", "LK" => "144", "SZ" => "748", "ZA" => "710", "SD" => "729",
            "SS" => "728", "SE" => "752", "CH" => "756", "SR" => "740", "SJ" => "744", "TH" => "764", "TW" => "158",
            "TZ" => "834", "TJ" => "762", "IO" => "086", "TF" => "260", "TL" => "626", "TG" => "768", "TK" => "772",
            "TO" => "776", "TT" => "780", "TN" => "788", "TM" => "795", "TR" => "792", "TV" => "798", "UA" => "804",
            "UG" => "800", "UY" => "858", "UZ" => "860", "VU" => "548", "VA" => "336", "VE" => "862", "VN" => "704",
            "WF" => "876", "YE" => "887", "DJ" => "262", "ZM" => "894", "ZW" => "716"
        );

        if (isset($arrCode[$code])) {
            $isoCodeNumber = $arrCode[$code];
        }
        return $isoCodeNumber;
    }


    public function getEMV3DS($cart)
    {

        $Merchant_EMV3DS = array();

        if (isset($this->context->customer->id) && $this->context->customer->id > 0) {
            $Merchant_EMV3DS["customer"]["id"] = $this->context->customer->id;
        }

        if (isset($this->context->customer->firstname) && $this->context->customer->firstname != "") {
            $Merchant_EMV3DS["customer"]["name"] = $this->context->customer->firstname;
        }

        if (isset($this->context->customer->lastname) && $this->context->customer->lastname != "") {
            $Merchant_EMV3DS["customer"]["surname"] = $this->context->customer->lastname;
        }

        if (isset($this->context->customer->email) && $this->context->customer->email != "") {
            $Merchant_EMV3DS["customer"]["email"] = $this->context->customer->email;
        }

        // Billing info
        $billing = new Address((int) $cart->id_address_invoice);

        if ($billing) {
            $billing_address_country = new Country($billing->id_country);
            $billing_address_state = new State($billing->id_state);

            $Merchant_EMV3DS["billing"]["billAddrCity"] = ($billing) ? $billing->city : '';
            $Merchant_EMV3DS["billing"]["billAddrCountry"] = ($billing) ? $billing_address_country->iso_code : '';
            if ($Merchant_EMV3DS["billing"]["billAddrCountry"] != "") {
                $Merchant_EMV3DS["billing"]["billAddrCountry"] =
                    $this->isoCodeToNumber($Merchant_EMV3DS["billing"]["billAddrCountry"]);
                // billAddrState -> Only if defined billAddrCountry
                if ($billing_address_state->iso_code != "") {
                    $billAddState = explode("-", $billing_address_state->iso_code);
                    $billAddState = end($billAddState);
                    $Merchant_EMV3DS["billing"]["billAddrState"] = $billAddState;
                }
            }
            $Merchant_EMV3DS["billing"]["billAddrLine1"] = ($billing) ? $billing->address1 : '';
            $Merchant_EMV3DS["billing"]["billAddrLine2"] = ($billing) ? $billing->address2 : '';
            //$Merchant_EMV3DS["billing"]["billAddrLine3"] = "";
            $Merchant_EMV3DS["billing"]["billAddrPostCode"] = ($billing) ? $billing->postcode : '';

            if ($billing->phone) {
                $arrDatosHomePhone = array();

                $arrDatosHomePhone["cc"] =
                    Tools::substr(preg_replace('/[^0-9]/', '', $billing_address_country->call_prefix), 0, 3);
                $arrDatosHomePhone["subscriber"] =
                    Tools::substr(preg_replace('/[^0-9]/', '', $billing->phone), 0, 15);

                $Merchant_EMV3DS["customer"]["homePhone"] = $arrDatosHomePhone;
                $Merchant_EMV3DS["customer"]["mobilePhone"] = $arrDatosHomePhone;
            }

            if ($billing->phone_mobile) {
                $arrDatosMobilePhone = array();

                $arrDatosMobilePhone["cc"] =
                    Tools::substr(preg_replace('/[^0-9]/', '', $billing_address_country->call_prefix), 0, 3);
                $arrDatosMobilePhone["subscriber"] =
                    Tools::substr(preg_replace('/[^0-9]/', '', $billing->phone_mobile), 0, 15);

                $Merchant_EMV3DS["customer"]["mobilePhone"] = $arrDatosMobilePhone;
            }
        }


        // Shiping info
        $shipping = new Address($cart->id_address_delivery);

        if ($shipping) {
            $shipping_address_country = new Country($shipping->id_country);
            $shipping_address_state = new State($shipping->id_state);

            $Merchant_EMV3DS["shipping"]["shipAddrCity"] = ($shipping) ? $shipping->city : '';
            $Merchant_EMV3DS["shipping"]["shipAddrCountry"] = ($shipping) ? $shipping_address_country->iso_code : '';
            if ($Merchant_EMV3DS["shipping"]["shipAddrCountry"] != "") {
                $Merchant_EMV3DS["shipping"]["shipAddrCountry"] =
                    $this->isoCodeToNumber($Merchant_EMV3DS["shipping"]["shipAddrCountry"]);
                // shipAddrState -> Only if defined shipAddrCountry
                if ($shipping_address_state->iso_code != "") {
                    $shipAddrState = explode("-", $shipping_address_state->iso_code);
                    $shipAddrState = end($shipAddrState);
                    $Merchant_EMV3DS["shipping"]["shipAddrState"] = $shipAddrState;
                }
            }
            $Merchant_EMV3DS["shipping"]["shipAddrLine1"] = ($shipping) ? $shipping->address1 : '';
            $Merchant_EMV3DS["shipping"]["shipAddrLine2"] = ($shipping) ? $shipping->address2 : '';
            //$Merchant_EMV3DS["shipping"]["shipAddrLine3"] = "";
            $Merchant_EMV3DS["shipping"]["shipAddrPostCode"] = ($shipping) ? $shipping->postcode : '';

            if ($shipping->phone) {
                $arrDatosWorkPhone = array();

                $arrDatosWorkPhone["cc"] =
                    Tools::substr(preg_replace('/[^0-9]/', '', $shipping_address_country->call_prefix), 0, 3);
                $arrDatosWorkPhone["subscriber"] =
                    Tools::substr(preg_replace('/[^0-9]/', '', $shipping->phone), 0, 15);

                $Merchant_EMV3DS["customer"]["workPhone"] = $arrDatosWorkPhone;
            }
        }

        // acctInfo
        $Merchant_EMV3DS["acctInfo"] = $this->acctInfo($cart);

        // threeDSRequestorAuthenticationInfo
        $Merchant_EMV3DS["threeDSRequestorAuthenticationInfo"] = $this->threeDSRequestorAuthenticationInfo();

        // AddrMatch
        $Merchant_EMV3DS["addrMatch"] = ($cart->id_address_invoice == $cart->id_address_delivery) ? "Y" : "N";

        $Merchant_EMV3DS["challengeWindowSize"] = 05;
        return $Merchant_EMV3DS;
    }




    public function getContent()
    {

        $errorMessage = '';
        if (!empty($_POST)) {
            $this->postValidation();
            if (!sizeof($this->postErrors)) {
                $errorMessage = $this->postProcess();
            } else {
                $errorMessage .= '<div class="bootstrap"><div class="alert alert-danger"><strong>' .
                    $this->l('Error') . '</strong><ol>';
                foreach ($this->postErrors as $err) {
                    $errorMessage .= '<li>' . $err . '</li>';
                }
                $errorMessage .= '</ol></div></div>';
            }
        } else {
            $errorMessage = '';
        }


        if (Tools::isSubmit('id_cart')) {
            $this->validateOrder(
                Tools::getValue('id_cart'),
                _PS_OS_PAYMENT_,
                Tools::getValue('amount'),
                $this->displayName,
                null
            );
        }

        $this->currency_array = Currency::getCurrenciesByIdShop(Context::getContext()->shop->id);

        if (Configuration::get('PS_RESTRICT_DELIVERED_COUNTRIES')) {
            $this->countries = Carrier::getDeliveredCountries($this->context->language->id, true, true);
        } else {
            $this->countries = Country::getCountries($this->context->language->id, true);
        }

        $this->terminales_paytpv = $this->obtenerTerminalesConfigurados();

        $ssl = Configuration::get('PS_SSL_ENABLED');

        $this->context->smarty->assign(
            'NOTIFICACION',
            Context::getContext()->link->getModuleLink($this->name, 'url', array(), $ssl)
        );

        $this->context->smarty->assign('displayName', Tools::safeOutput($this->displayName));
        $this->context->smarty->assign('description', Tools::safeOutput($this->description));
        $this->context->smarty->assign('errorMessage', $errorMessage);

        $this->context->controller->addJS($this->_path . 'views/js/admin.js', 'all');
        $this->context->controller->addCSS($this->_path . 'views/css/admin.css', 'all');

        $this->context->smarty->assign('configform', str_replace('</form>', '', $this->displayForm()));
        $output = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/admin.tpl');

        return $output;
    }

    private function displayForm()
    {
        $helper = new PaytpvPaymentsHelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitPaytpvpaymentsModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
        . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        return $helper->generatePaytpvForm($this->context->smarty, $this->getConfigForm());
    }


    public function getConfigFormValues()
    {
        $config = $this->getConfigValues();
        $arrValues = array();

        $arrValues["clientcode"] = trim($config["PAYTPV_CLIENTCODE"]);
        $arrValues["apikey"] = trim($config["PAYTPV_APIKEY"]);
        $arrValues["integration"] = $config["PAYTPV_INTEGRATION"];
        $arrValues["newpage_payment"] = $config["PAYTPV_NEWPAGEPAYMENT"];
        $arrValues["iframe_height"] = ($config["PAYTPV_IFRAME_HEIGHT"]!="")?$config["PAYTPV_IFRAME_HEIGHT"] : 440;
        $arrValues["suscriptions"] = $config["PAYTPV_SUSCRIPTIONS"];

        $arrValues["firstpurchase_scoring"] = $config["PAYTPV_FIRSTPURCHASE_SCORING"];
        $arrValues["firstpurchase_scoring_score"] = $config["PAYTPV_FIRSTPURCHASE_SCORING_SCO"];
        $arrValues["sessiontime_scoring"] = $config["PAYTPV_SESSIONTIME_SCORING"];
        $arrValues["sessiontime_scoring_val"] = $config["PAYTPV_SESSIONTIME_SCORING_VAL"];
        $arrValues["sessiontime_scoring_score"] = $config["PAYTPV_SESSIONTIME_SCORING_SCORE"];
        $arrValues["dcountry_scoring"] = $config["PAYTPV_DCOUNTRY_SCORING"];
        $arrValues["dcountry_scoring_val[]"] = explode(",", $config["PAYTPV_DCOUNTRY_SCORING_VAL"]);
        $arrValues["dcountry_scoring_score"] = $config["PAYTPV_DCOUNTRY_SCORING_SCORE"];
        $arrValues["ip_change_scoring"] = $config["PAYTPV_IPCHANGE_SCORING"];

        //APMs
        // Tarjeta activa siempre que no la haya deshabilitado el cliente
        $arrValues["apms_tarjeta"] = ((string)$config["PAYTPV_APM_tarjeta"] == "0")?0:1;
        $arrValues["apms_bizum"] = $config["PAYTPV_APM_bizum"];
        $arrValues["apms_klarna_paynow"] = $config["PAYTPV_APM_klarna_paynow"];
        $arrValues["apms_ideal"] = $config["PAYTPV_APM_ideal"];
        $arrValues["apms_giropay"] = $config["PAYTPV_APM_giropay"];
        $arrValues["apms_mybank"] = $config["PAYTPV_APM_mybank"];
        $arrValues["apms_multibanco_sibs"] = $config["PAYTPV_APM_multibanco_sibs"];
        $arrValues["apms_trustly"] = $config["PAYTPV_APM_trustly"];
        $arrValues["apms_przelewy24"] = $config["PAYTPV_APM_przelewy24"];
        $arrValues["apms_bancontact"] = $config["PAYTPV_APM_bancontact"];
        $arrValues["apms_eps"] = $config["PAYTPV_APM_eps"];
        $arrValues["apms_tele2"] = $config["PAYTPV_APM_tele2"];
        $arrValues["apms_paysera"] = $config["PAYTPV_APM_paysera"];
        $arrValues["apms_postfinance"] = $config["PAYTPV_APM_postfinance"];
        $arrValues["apms_qiwi_wallet"] = $config["PAYTPV_APM_qiwi_wallet"];
        $arrValues["apms_yandex_money"] = $config["PAYTPV_APM_yandex_money"];
        $arrValues["apms_mts"] = $config["PAYTPV_APM_mts"];
        $arrValues["apms_beeline"] = $config["PAYTPV_APM_beeline"];
        $arrValues["apms_paysafecard"] = $config["PAYTPV_APM_paysafecard"];
        $arrValues["apms_skrill"] = $config["PAYTPV_APM_skrill"];
        $arrValues["apms_webmoney"] = $config["PAYTPV_APM_webmoney"];
        $arrValues["apms_instant_credit"] = $config["PAYTPV_APM_instant_credit"];
        $arrValues["apms_klarna_payments"] = $config["PAYTPV_APM_klarna_payments"];
        $arrValues["apms_paypal"] = $config["PAYTPV_APM_paypal"];

        // Instant Credit
        $arrValues["apms_instant_credit_simuladorCoutas"] = $config["PAYTPV_APM_instant_credit_simuladorCoutas"];
        $arrValues["apms_instant_credit_environment"] = $config["PAYTPV_APM_instant_credit_environment"];
        $arrValues["apms_instant_credit_hashToken"] = $config["PAYTPV_APM_instant_credit_hashToken"];
        $arrValues["apms_instant_credit_minFin"] = $config["PAYTPV_APM_instant_credit_minFin"];
        $arrValues["apms_instant_credit_maxFin"] = $config["PAYTPV_APM_instant_credit_maxFin"];

        $arrValues["ip_change_scoring_score"] = $config["PAYTPV_IPCHANGE_SCORING_SCORE"];
        $arrValues["browser_scoring"] = $config["PAYTPV_BROWSER_SCORING"];
        $arrValues["browser_scoring_score"] = $config["PAYTPV_BROWSER_SCORING_SCORE"];

        $arrValues["so_scoring"] = $config["PAYTPV_SO_SCORING"];
        $arrValues["so_scoring_score"] = $config["PAYTPV_SO_SCORING_SCORE"];
        $arrValues["disableoffersavecard"] = $config["PAYTPV_DISABLEOFFERSAVECARD"];


        foreach ($this->terminales_paytpv as $key => $term) {
            $arrValues["term[".$key."]"] = trim($term["idterminal"]);
            $arrValues["pass[".$key."]"] = trim($term["password"]);
            $arrValues["jetid[".$key."]"] = trim($term["jetid"]);
            $arrValues["moneda[".$key."]"] = $term["currency_iso_code"];
            $arrValues["dcc[".$key."]"] = $term["dcc"];
        }
        return $arrValues;
    }

    public function getConfigForm()
    {

        $arrCurrency = array();
        foreach ($this->currency_array as $key => $datos) {
            $arrCurrency[$key]["id"] = $datos["iso_code"];
            $arrCurrency[$key]["name"] = $datos["name"];
        }
        $arrFields = array();
        $general_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('General'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'col' => 3,
                        'type' => 'select',
                        'label' => $this->l('Integration'),
                        'name' => 'integration',
                        'class' => 'integration',
                        'options' => array(
                            'query' => array(
                                array(
                                    'id' => 0,
                                    'name' => $this->l('Bankstore IFRAME/XML')
                                ),
                                array(
                                    'id' => 1,
                                    'name' => $this->l('Bankstore JET-IFRAME')
                                )
                            ),
                            'id' => 'id',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'col' => 1,
                        'type' => 'text',
                        'label' => $this->l('Client Code'),
                        'name' => 'clientcode',
                        'hint' => $this->l('Client Code. Available in the PAYCOMET product configuration'),
                        'required' => true
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'label' => $this->l('API KEY'),
                        'name' => 'apikey',
                        'hint' => $this->l('API KEY. You can create an API KEY in your PAYCOMET client area'),
                        'required' => false
                    ),
                )
            )
        );
        $arrFields[] = $general_form;
        $arrTerminal = array();
        foreach (array_keys($this->terminales_paytpv) as $key) {
            $arrTerminal[$key] = array(
                array(
                    'col' => 1,
                    'type' => 'text',
                    'label' => $this->l('Terminal Number'),
                    'name' => 'term['.$key.']',
                    'id' => 'term_' . $key,
                    'class' => 'term term_s_container_'.$key,
                    'hint' => $this->l(
                        'Terminal Number. Available in the PAYCOMET product configuration'
                    ),
                    'required' => true
                ),
                array(
                    'col' => 2,
                    'type' => 'text',
                    'label' => $this->l('Password'),
                    'name' => 'pass['.$key.']',
                    'id' => 'pass_' . $key,
                    'class' => 'term_s_container_'.$key,
                    'hint' => $this->l(
                        'Password. Available in the PAYCOMET product configuration'
                    ),
                    'required' => true
                ),
                array(
                    'col' => 2,
                    'type' => 'text',
                    'label' => $this->l('JET ID'),
                    'name' => 'jetid['.$key.']',
                    'id' => 'jetid_' . $key,
                    'class' => 'class_jetid term_s_container_'.$key,
                    'hint' => $this->l(
                        'JET ID. Available in the PAYCOMET product configuration'
                    ),
                    'required' => true
                ),
                array(
                    'type' => 'select',
                    'label' => $this->l('Currency'),
                    'name' => 'moneda['.$key.']',
                    'id' => 'moneda_' . $key,
                    'desc' => '',
                    'options' => array(
                        'query' => $arrCurrency,
                        'id' => 'id',
                        'name' => 'name'
                    ),
                    'desc' => $this->l('PAYCOMET Terminal Currency.'),
                ),
                array(
                    'type' => 'switch',
                    'label' => $this->l('DCC'),
                    'name' => 'dcc['.$key.']',
                    'id' => 'dcc_' . $key,
                    'values' => array(
                        array(
                            'id' => 'active_on',
                            'value' => 0,
                            'label' => $this->l('No')
                        ),
                        array(
                            'id' => 'active_off',
                            'value' => 1,
                            'label' => $this->l('Yes')
                        )
                    ),
                )
            );

            $terminal_form = array(
                'form' => array(
                    'legend' => array(
                        'title' => $this->l('Terminal'),
                        'icon' => 'icon-cogs terminal',
                    ),
                    'input' => $arrTerminal[$key]
                )
            );

            if ($key==0) {
                $terminal_form['form']['buttons'] = array(
                    array(
                        'title' => $this->l('Add Terminal'),
                        'icon' => 'process-icon-new',
                        'id' => 'addterminal',
                        'class' => 'addTerminal'
                    ),
                    array(
                        'title' => $this->l('Remove Terminal'),
                        'icon' => 'process-icon-close',
                        'id' => 'removeterminal',
                        'class' => 'hidden removeTerminal'
                    )
                );
            } else {
                $terminal_form['form']['buttons'] = array(
                    array(
                        'title' => $this->l('Remove Terminal'),
                        'icon' => 'process-icon-cancel',
                        'id' => 'removeTerminal',
                        'class' => 'removeTerminal'
                    )
                );
            }

            $arrFields[] = $terminal_form;
        }


        $options_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Options'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(

                    array(
                        'type' => 'select',
                        'label' => $this->l('Payment in new Page'),
                        'name' => 'newpage_payment',
                        'options' => array(
                            'query' => array(
                                array(
                                    'id' => 0,
                                    'name' => $this->l('No')
                                ),
                                array(
                                    'id' => 1,
                                    'name' => $this->l('Yes')
                                ),
                                array(
                                    'id' => 2,
                                    'name' => $this->l('Yes. PAYCOMET page')
                                )
                            ),
                            'id' => 'id',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'col' => 1,
                        'type' => 'text',
                        'label' => $this->l('Iframe Height (px)'),
                        'hint' => $this->l('Iframe height in pixels (Min 440)'),
                        'name' => 'iframe_height',
                        'id' => 'iframe_height',
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Disable Offer to save card'),
                        'name' => 'disableoffersavecard',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 0,
                                'label' => $this->l('No')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            )
                        ),
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Activate Subscriptions'),
                        'name' => 'suscriptions',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 0,
                                'label' => $this->l('No')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            )
                        ),
                    ),
                )
            ),
        );

        $arrFields[] = $options_form;

        //APMs
        $arrAPMs = $this->getPaymentMethods();
        $apms_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('payment methods'),
                    'icon' => 'icon-credit-card',
                ),
                'input' => array(
                    array(
                        'type' => 'checkbox',
                        'name' => 'apms',
                        'values' => array(
                            'query' => $arrAPMs,
                            'id' => 'id',
                            'name' => 'name'
                        )
                    ),
                )
            )
        );

        $arrFields[] = $apms_form;


        $arrMethods = array();
        foreach ($arrAPMs as $key => $apm_data) {
            $arrMethods[] = $apm_data["val"];
        }

        // Instant Credit
        if (in_array(33, $arrMethods)) {
            $instantCredit_form = array(
                'form' => array(
                    'legend' => array(
                        'title' => $this->l('Instant Credit'),
                        'icon' => 'icon-cogs'
                    ),
                    'input' => array(
                        array(
                            'type' => 'switch',
                            'label' => $this->l('Simulator'),
                            'name' => 'apms_instant_credit_simuladorCoutas',
                            'is_bool' => true,
                            'hint' => $this->l('Show simulator'),
                            'values' => array(
                                array(
                                    'id' => 'active_on',
                                    'value' => true,
                                    'label' => 'Activado',
                                ),
                                array(
                                    'id' => 'active_off',
                                    'value' => false,
                                    'label' => 'Desactivado',
                                )
                            ),
                        ),
                        array(
                            'type' => 'select',
                            'label' => $this->l('Simulator Environment'),
                            'name' => 'apms_instant_credit_environment',
                            'hint' => $this->l('Simulator Environment'),
                            'options' => array(
                                'query' => array(
                                    array(
                                        'id' => 0,
                                        'name' => $this->l('Production')
                                    ),
                                    array(
                                        'id' => 1,
                                        'name' => $this->l('Test')
                                    )
                                ),
                                'id' => 'id',
                                'name' => 'name'
                            )
                        ),
                        array(
                            'type' => 'text',
                            'label' => $this->l('Hash Simulator'),
                            'name' => 'apms_instant_credit_hashToken',
                            'required' => false
                        ),
                        array(
                            'type' => 'text',
                            'label' => $this->l('Minimum financing'),
                            'name' => 'apms_instant_credit_minFin',
                            'required' => true
                        ),
                        array(
                            'type' => 'text',
                            'label' => $this->l('Maximum financing'),
                            'name' => 'apms_instant_credit_maxFin',
                            'required' => true
                        ),
                    )
                ),
            );

            $arrFields[] = $instantCredit_form;
        }

        // Array Score

        $arrScore = array();
        for ($i=0; $i <= 100; $i++) {
            $arrScore[$i]["id"] = $i;
            $arrScore[$i]["name"] = $i;
        }

        $arrSessionTime = array(
            array("id"=>0,"name"=>'00:00'),
            array("id"=>15,"name"=>'00:15'),
            array("id"=>30,"name"=>'00:30'),
            array("id"=>45,"name"=>'00:45'),
            array("id"=>60,"name"=>'01:00'),
            array("id"=>90,"name"=>'01:30'),
            array("id"=>120,"name"=>'02:00'),
            array("id"=>180,"name"=>'03:00'),
            array("id"=>240,"name"=>'04:00'),
            array("id"=>300,"name"=>'05:00'),
            array("id"=>360,"name"=>'06:00')
        );


        $arrDestination = array();
        $id = 0;
        foreach ($this->countries as $key => $country) {
            $arrDestination[$id]["id"] = $country["id_country"];
            $arrDestination[$id]["name"] = $country["name"];
            $id++;
        };

        $scoring_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Scoring'),
                    'icon' => 'icon-cogs'
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('First Purchase'),
                        'name' => 'firstpurchase_scoring',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 0,
                                'label' => $this->l('No')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            )
                        ),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Score'),
                        'name' => 'firstpurchase_scoring_score',
                        'options' => array(
                            'query' => $arrScore,
                            'id' => 'id',
                            'name' => 'name'
                        )
                    ),

                    array(
                        'type' => 'switch',
                        'label' => $this->l('Complete Session Time'),
                        'name' => 'sessiontime_scoring',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 0,
                                'label' => $this->l('No')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            )
                        ),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Score'),
                        'name' => 'sessiontime_scoring_score',
                        'options' => array(
                            'query' => $arrScore,
                            'id' => 'id',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Time (hh:mm)'),
                        'name' => 'sessiontime_scoring_val',
                        'options' => array(
                            'query' => $arrSessionTime,
                            'id' => 'id',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Destination Country'),
                        'name' => 'dcountry_scoring',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 0,
                                'label' => $this->l('No')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            )
                        ),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Score'),
                        'name' => 'dcountry_scoring_score',
                        'options' => array(
                            'query' => $arrScore,
                            'id' => 'id',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Countries'),
                        'name' => 'dcountry_scoring_val[]',
                        'multiple' => true,
                        'options' => array(
                            'query' => $arrDestination,
                            'id' => 'id',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('IP Change'),
                        'name' => 'ip_change_scoring',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 0,
                                'label' => $this->l('No')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            )
                        ),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Score'),
                        'name' => 'ip_change_scoring_score',
                        'options' => array(
                            'query' => $arrScore,
                            'id' => 'id',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Browser Unidentified'),
                        'name' => 'browser_scoring',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 0,
                                'label' => $this->l('No')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            )
                        ),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Score'),
                        'name' => 'browser_scoring_score',
                        'options' => array(
                            'query' => $arrScore,
                            'id' => 'id',
                            'name' => 'name'
                        )
                    ),
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Operating System Unidentified'),
                        'name' => 'so_scoring',
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => 0,
                                'label' => $this->l('No')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => 1,
                                'label' => $this->l('Yes')
                            )
                        ),
                    ),
                    array(
                        'type' => 'select',
                        'label' => $this->l('Score'),
                        'name' => 'so_scoring_score',
                        'options' => array(
                            'query' => $arrScore,
                            'id' => 'id',
                            'name' => 'name'
                        )
                    ),
                )
            ),
        );

        $arrFields[] = $scoring_form;

        return $arrFields;
    }

    public function getPaymentMethods()
    {

        if (Configuration::get("PAYTPV_APIKEY") != "") {
            $apiRest = new PaycometApiRest(Configuration::get("PAYTPV_APIKEY"));

            $terminalId = 0;
            if (isset(Tools::getValue("term")[0])) {
                $terminalId = Tools::getValue("term")[0];
            } elseif (PaytpvTerminal::getTerminals()) {
                $terminalId = PaytpvTerminal::getTerminals()[0]['idterminal'];
            }
            $paymentMethods = $apiRest->getUserPaymentMethods($terminalId);
            $apms = [];

            // Si no hay error en la consulta a los metodos
            if (!isset($paymentMethods->errorCode)) {
                foreach ($paymentMethods as $apm) {
                    $apms[] = [
                        'id' => preg_replace('/\s+/', '_', Tools::strtolower($apm->name)),
                        'name' => $apm->name,
                        'val' => $apm->id,
                        'logo_square' => $apm->logo_square,
                        'logo_landscape' => $apm->logo_landscape,
                    ];
                }
            }
        } else {
            $apms[] = [
                "id"=>"tarjeta",
                "name" => "Tarjeta",
                "val"=> 1
            ];
        }

        return $apms;
    }

    public function obtenerTerminalesConfigurados()
    {
        $terminales = PaytpvTerminal::getTerminals();
        if (sizeof($terminales) == 0) {
            $id_currency = (int) (Configuration::get('PS_CURRENCY_DEFAULT'));
            $currency = new Currency((int) ($id_currency));

            $terminales[0]["idterminal"] = "";
            $terminales[0]["password"] = "";
            $terminales[0]["jetid"] = "";
            $terminales[0]["currency_iso_code"] = $currency->iso_code;
            $terminales[0]["dcc"] = 0;
        }

        return $terminales;
    }

    public function hookHeader()
    {
        // call your media file like this
        $this->context->controller->addJqueryPlugin('fancybox');
        $this->context->controller->registerStylesheet(
            'paytpv-payment',
            'modules/paytpv/views/css/payment.css'
        );
        $this->context->controller->registerStylesheet(
            'paytpv-fullscreen',
            'modules/paytpv/views/css/fullscreen.css'
        );
        $this->context->controller->registerJavascript(
            'paytpv-js',
            'modules/paytpv/views/js/paytpv.js'
        );

        $paytpv_integration = (int) Configuration::get('PAYTPV_INTEGRATION');

        // Bankstore JET
        if ($paytpv_integration == 1) {
            $this->context->controller->registerJavascript(
                'paytpv-jet',
                'modules/paytpv/views/js/paytpv_jet.js'
            );
        }
        $this->context->controller->registerJavascript(
            'paytpv-fancybox',
            'modules/paytpv/views/js/jquery.fancybox.pack.js'
        );
    }

    public function hookActionFrontControllerSetMedia($params)
    {
    }


    public function hookDisplayShoppingCart()
    {
        $this->context->controller->registerJavascript($this->name . '_js', $this->_path . 'views/js/paytpv.js');

        $this->context->controller->addCSS($this->_path . 'views/css/payment.css', 'all');
        $this->context->controller->addCSS($this->_path . 'views/css/fullscreen.css', 'all');
        $this->context->controller->addJS($this->_path . 'views/js/paytpv.js');
    }



    public function hookDisplayPaymentTop($params)
    {
        $this->context->controller->addCSS($this->_path . 'views/css/payment.css', 'all');
        $this->context->controller->addCSS($this->_path . 'views/css/fullscreen.css', 'all');
        $this->context->controller->addJS($this->_path . 'views/js/paytpv.js');
    }

    public function getPaycometLang($language_code)
    {
        $language_data = explode("-", $language_code);
        switch ($language_data[0]) {
            default:
                $language = $language_data[0];
                break;
            case "da":
                $language = "dk";
                break;
        }
        return $language;
    }

    public function getTemplateVarInfos()
    {
        $cart = $this->context->cart;
        $datos_pedido = $this->terminalCurrency($cart);
        $jetid = $datos_pedido["jetid"];

        $ssl = Configuration::get('PS_SSL_ENABLED');
        $values = array(
            'id_cart' => (int) $cart->id,
            'key' => Context::getContext()->customer->secure_key
        );

        $active_suscriptions = (int) Configuration::get('PAYTPV_SUSCRIPTIONS');

        $saved_card = PaytpvCustomer::getCardsCustomer((int) $this->context->customer->id);
        $index = 0;
        foreach ($saved_card as $key => $val) {
            $values_aux = array_merge($values, array("TOKEN_USER" => $val["TOKEN_USER"]));
            $saved_card[$key]['url'] = Context::getContext()->link->getModuleLink(
                $this->name,
                'capture',
                $values_aux,
                $ssl
            );
            $index++;
        }
        $saved_card[$index]['url'] = 0;

        $paytpv_integration = (int) Configuration::get('PAYTPV_INTEGRATION');
        $newpage_payment = (int) Configuration::get('PAYTPV_NEWPAGEPAYMENT');
        $iframe_height = (int)$this->iframe_height;

        $disableoffersavecard = Configuration::get('PAYTPV_DISABLEOFFERSAVECARD');

        $language = $this->getPaycometLang($this->context->language->language_code);

        $paytpv_error = 0;
        $iframeURL = $this->paytpvIframeURL();
        if (filter_var($iframeURL, FILTER_VALIDATE_URL) === false) {
            $paytpv_error = $iframeURL;
            $iframeURL = "";
        }

        return array(
            'msg_paytpv' => '',
            'active_suscriptions' => $active_suscriptions,
            'saved_card' => $saved_card,
            'id_cart' => $cart->id,
            'paytpv_iframe' => $iframeURL,
            'paytpv_error' => $paytpv_error,
            'paytpv_integration' => $paytpv_integration,
            'account' => 0,
            'jet_id' => $jetid,
            'jet_lang' => $language,
            'jet_paytpv' => $this->jet_paytpv,
            'paytpv_module' => Context::getContext()->link->getModuleLink($this->name, 'actions', array(), $ssl),
            'paytpv_jetid_url' => Context::getContext()->link->getModuleLink($this->name, 'capture', array(), $ssl),
            'base_dir' => __PS_BASE_URI__,
            'capture_url' => Context::getContext()->link->getModuleLink($this->name, 'capture', $values, $ssl),
            'this_path' => $this->_path,
            'hookpayment' => 1,
            'newpage_payment' => $newpage_payment,
            'iframe_height' => $iframe_height,
            'disableoffersavecard' => $disableoffersavecard
        );
    }

    public function getTemplateVars()
    {
        $cart = $this->context->cart;
        $total = $cart->getOrderTotal(true, Cart::BOTH);

        return array(
            'this_path' => $this->_path,
            'this_path_instantcredit' => $this->_path,
            'this_path_ssl' => Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'modules/' . $this->name . '/',
            'simuladorCuotas' => $this->simuladorCuotas,
            'urlSimulador' => $this->context->link->getModuleLink(
                $this->name,
                'simulador',
                array('importe_financiar' => $total)
            ),
        );
    }

    public function hookPaymentOptions()
    {
        $payment_options = [];
        // Check New Page payment
        $newpage_payment = (int) Configuration::get('PAYTPV_NEWPAGEPAYMENT');
        // Pago en nueva página
        if ($newpage_payment == 1) {
            $urltpv = Context::getContext()->link->getModuleLink($this->name, 'payment');
            $urltpv = htmlspecialchars($urltpv);

            $form_paytpv = '<form id="payment-form"  method="POST" action="' . $urltpv . '"></form>';
            $this->context->smarty->assign('this_path', $this->_path);

            $newOption = new PaymentOption();
            $newOption->setCallToActionText($this->trans($this->l('Pay with card'), array(), 'Modules.Paytpv.Shop'))
            ->setLogo(_MODULE_DIR_ . 'paytpv/views/img/paycomet.svg')
            ->setForm($form_paytpv);
        // Pago en página de PAYCOMET
        } elseif ($newpage_payment == 2) {
            $arrTemplateVarInfos = $this->getTemplateVarInfos();
            $this->context->smarty->assign(
                $arrTemplateVarInfos
            );
            if ($arrTemplateVarInfos['paytpv_error']!=0) {
                $newOption = new PaymentOption();
                $newOption->setCallToActionText(
                    $this->trans($this->l('Pay with card'), array(), 'Modules.Paytpv.Shop')
                );
                $newOption->setAdditionalInformation(
                    $this->fetch('module:paytpv/views/templates/hook/payment_error_hook.tpl')
                );
                $newOption->setLogo(_MODULE_DIR_ . 'paytpv/views/img/paycomet.svg');
            } else {
                $newOption = new PaymentOption();
                $newOption->setCallToActionText($this->trans($this->l('Pay with card'), array(), 'Modules.Paytpv.Shop'))
                            ->setForm($this->paycometPageForm());
                $newOption->setLogo(_MODULE_DIR_ . 'paytpv/views/img/paycomet.svg');
            }
        // Pago integrado
        } else {
            $arrTemplateVarInfos = $this->getTemplateVarInfos();
            $this->context->smarty->assign(
                $arrTemplateVarInfos
            );

            if ($arrTemplateVarInfos['paytpv_error']!=0) {
                $newOption = new PaymentOption();
                $newOption->setCallToActionText(
                    $this->trans($this->l('Pay with card'), array(), 'Modules.Paytpv.Shop')
                );
                $newOption->setAdditionalInformation(
                    $this->fetch('module:paytpv/views/templates/hook/payment_error_hook.tpl')
                );
            } else {
                switch ($this->integration) {
                    // Iframe
                    case 0:
                        $newOption = new PaymentOption();
                        $newOption->setBinary(true);
                        $newOption->setCallToActionText(
                            $this->trans($this->l('Pay with card'), array(), 'Modules.Paytpv.Shop')
                        );
                        $newOption->setAdditionalInformation(
                            $this->fetch('module:paytpv/views/templates/hook/payment_bsiframe_hook.tpl')
                        );
                        $newOption->setLogo(_MODULE_DIR_ . 'paytpv/views/img/paycomet.svg');
                        break;

                    // JetIframe
                    case 1:
                        $newOption = new PaymentOption();
                        $newOption->setCallToActionText(
                            $this->trans($this->l('Pay with card'), array(), 'Modules.Paytpv.Shop')
                        );
                        $newOption->setLogo(_MODULE_DIR_ . 'paytpv/views/img/paycomet.svg');
                        $newOption->setForm($this->jetIframeForm());
                        break;
                }
            }
        }

        $alternativePaymentMethods = $this->getUserApmsForPayment();
        foreach ($alternativePaymentMethods as $methodId => $apm) {
            if ($methodId == 1) {
                continue; // El pago con tarjeta va aparte
            }

            $apmOption = new PaymentOption();
            $apmOption->setCallToActionText(
                $this->trans(
                    $this->l('Pay with ') . $apm['method_name'],
                    array(),
                    'Modules.MyModule.Shop'
                )
            )
            ->setLogo(_MODULE_DIR_ . 'paytpv/views/img/apms/' . $apm['img_name'] . '.svg')
            ->setAction($apm['url']);

            // PESRONALIZACIONES APMS
            switch ($methodId) {
                case 33: // Instant Credti
                    $apmOption->setCallToActionText(
                        $this->trans(
                            $this->l('Instant installment payment'),
                            array(),
                            'Modules.MyModule.Shop'
                        )
                    );
                    $this->context->smarty->assign(
                        $apm["templateVars"]
                    );
                    $apmOption->setAdditionalInformation(
                        $this->fetch('module:paytpv/views/templates/hook/apms/payment_instantcredit.tpl')
                    );
                    break;
                default:
                    break;
            }
            array_push($payment_options, $apmOption);
        }

        // Si no hay pago con tarjeta mostramos solo Apms
        if (isset($alternativePaymentMethods[1])) {
            array_unshift($payment_options, $newOption);
        }

        return $payment_options;
    }

    public function getUserApmsForPayment()
    {
        if ($this->apikey != '') {
            $apms = [];

            if (Configuration::get('PAYTPV_APM_tarjeta') !== "0") {
                array_push($apms, 1);
            }
            if (Configuration::get('PAYTPV_APM_klarna_paynow') != null) {
                array_push($apms, Configuration::get('PAYTPV_APM_klarna_paynow'));
            }
            if (Configuration::get('PAYTPV_APM_bizum') != null) {
                array_push($apms, Configuration::get('PAYTPV_APM_bizum'));
            }
            if (Configuration::get('PAYTPV_APM_ideal') != null) {
                array_push($apms, Configuration::get('PAYTPV_APM_ideal'));
            }
            if (Configuration::get('PAYTPV_APM_giropay') != null) {
                array_push($apms, Configuration::get('PAYTPV_APM_giropay'));
            }
            if (Configuration::get('PAYTPV_APM_mybank') != null) {
                array_push($apms, Configuration::get('PAYTPV_APM_mybank'));
            }
            if (Configuration::get('PAYTPV_APM_multibanco_sibs') != null) {
                array_push($apms, Configuration::get('PAYTPV_APM_multibanco_sibs'));
            }
            if (Configuration::get('PAYTPV_APM_trustly') != null) {
                array_push($apms, Configuration::get('PAYTPV_APM_trustly'));
            }
            if (Configuration::get('PAYTPV_APM_przelewy24') != null) {
                array_push($apms, Configuration::get('PAYTPV_APM_przelewy24'));
            }
            if (Configuration::get('PAYTPV_APM_bancontact') != null) {
                array_push($apms, Configuration::get('PAYTPV_APM_bancontact'));
            }
            if (Configuration::get('PAYTPV_APM_eps') != null) {
                array_push($apms, Configuration::get('PAYTPV_APM_eps'));
            }
            if (Configuration::get('PAYTPV_APM_tele2') != null) {
                array_push($apms, Configuration::get('PAYTPV_APM_tele2'));
            }
            if (Configuration::get('PAYTPV_APM_paysera') != null) {
                array_push($apms, Configuration::get('PAYTPV_APM_paysera'));
            }
            if (Configuration::get('PAYTPV_APM_postfinance') != null) {
                array_push($apms, Configuration::get('PAYTPV_APM_postfinance'));
            }
            if (Configuration::get('PAYTPV_APM_qiwi_wallet') != null) {
                array_push($apms, Configuration::get('PAYTPV_APM_qiwi_wallet'));
            }
            if (Configuration::get('PAYTPV_APM_yandex_money') != null) {
                array_push($apms, Configuration::get('PAYTPV_APM_yandex_money'));
            }
            if (Configuration::get('PAYTPV_APM_mts') != null) {
                array_push($apms, Configuration::get('PAYTPV_APM_mts'));
            }
            if (Configuration::get('PAYTPV_APM_beeline') != null) {
                array_push($apms, Configuration::get('PAYTPV_APM_beeline'));
            }
            if (Configuration::get('PAYTPV_APM_paysafecard') != null) {
                array_push($apms, Configuration::get('PAYTPV_APM_paysafecard'));
            }
            if (Configuration::get('PAYTPV_APM_skrill') != null) {
                array_push($apms, Configuration::get('PAYTPV_APM_skrill'));
            }
            if (Configuration::get('PAYTPV_APM_webmoney') != null) {
                array_push($apms, Configuration::get('PAYTPV_APM_webmoney'));
            }
            if (Configuration::get('PAYTPV_APM_instant_credit') != null) {
                array_push($apms, Configuration::get('PAYTPV_APM_instant_credit'));
            }
            if (Configuration::get('PAYTPV_APM_klarna_payments') != null) {
                array_push($apms, Configuration::get('PAYTPV_APM_klarna_payments'));
            }
            if (Configuration::get('PAYTPV_APM_paypal') != null) {
                array_push($apms, Configuration::get('PAYTPV_APM_paypal'));
            }
            
            if (empty($apms)) {
                return $apms;
            }

            $cart = Context::getContext()->cart;
            $datos_pedido = $this->terminalCurrency($cart);
            $importe = $datos_pedido["importe"];
            $currency_iso_code = $datos_pedido["currency_iso_code"];
            $idterminal = $datos_pedido["idterminal"];
            $paytpv_order_ref = str_pad($cart->id, 8, "0", STR_PAD_LEFT);
            $merchantData = $this->getMerchantData($cart);
            $ssl = Configuration::get('PS_SSL_ENABLED');
            $values = array(
                'id_cart' => $cart->id,
                'key' => Context::getContext()->customer->secure_key
            );
            $URLOK = Context::getContext()->link->getModuleLink($this->name, 'urlok', $values, $ssl);
            $URLKO = Context::getContext()->link->getModuleLink($this->name, 'urlko', $values, $ssl);

            $apiRest = new PaycometApiRest($this->apikey);
            $url_paytpv = array();

            $ssl = Configuration::get('PS_SSL_ENABLED');

            $OPERATION = 1;
            $userInteraction = 1;
            $secure_pay = 1;
            $language = $this->getPaycometLang($this->context->language->language_code);
            $productDescription = '';
        
            if (isset($this->context->customer->email)) $productDescription = $this->context->customer->email;

            $score = $this->transactionScore($cart);
            $scoring = $score["score"];

            foreach ($apms as $methodId) {
                try {
                    if (!$this->validateMethod($methodId, $cart)) {
                        continue;
                    }

                    $values = array(
                        'url' => '',
                        'id_cart' => $cart->id,
                        'methodId' => $methodId,
                        'key' => Context::getContext()->customer->secure_key
                    );

                    $url_apm = Context::getContext()->link->getModuleLink($this->name, 'apm', $values, $ssl);

                    $url_paytpv[$methodId]['url'] = $url_apm;
                    $method_name = $this->getAPMName($methodId);
                    $method_img = str_replace(" ", "", Tools::strtolower($method_name));
                    $url_paytpv[$methodId]['method_name'] = $method_name;
                    $url_paytpv[$methodId]['img_name'] = $method_img;
                    $url_paytpv[$methodId]['templateVars'] = $this->getAPMTemplateVars(
                        $methodId,
                        $cart->getOrderTotal(true, Cart::BOTH)
                    );

                } catch (exception $e) {
                    $url_paytpv = $e->getCode();
                }
            }
            return $url_paytpv;
        }
    }

    public function validateMethod($methodId, $cart)
    {
        $valid = false;
        switch ($methodId) {
            case 33: // Instant Credit
                if (($this->paytpv_apm_instant_credit_minFin == 0 ||
                    $cart->getOrderTotal(true, Cart::BOTH) >= $this->paytpv_apm_instant_credit_minFin ) &&
                    ($this->paytpv_apm_instant_credit_maxFin == 0 ||
                    $cart->getOrderTotal(true, Cart::BOTH) <= $this->paytpv_apm_instant_credit_maxFin )) {
                    $valid = true;
                } else {
                    $valid = false;
                }
                break;
            default:
                $valid = true;
                break;
        }
        return $valid;
    }

    public function getICSimulatorUrl()
    {
        if ($this->paytpv_apm_instant_credit_environment == 1) {
            $url = "https://instantcredit.net/simulator/test/ic-simulator.js";
        } else {
            $url = "https://instantcredit.net/simulator/ic-simulator.js";
        }
        return $url;
    }


    public function getAPMTemplateVars($methodId, $total)
    {
        $arrTemplateVars = array();
        switch ($methodId) {
            case 33: // Instant Credit
                $arrTemplateVars["urlSimulador"] = $this->context->link->getModuleLink(
                    $this->name,
                    'simulador',
                    array('importe_financiar' => $total)
                );
                $arrTemplateVars["simuladorCuotas"] = $this->paytpv_apm_instant_credit_simulador;
                $arrTemplateVars["importe_financiar"] = $total;
                $arrTemplateVars["hashToken"] = $this->paytpv_apm_instant_credit_hashToken;
                $arrTemplateVars["urlSimulatorJs"] = $this->getICSimulatorUrl();

                $method_name = $this->getAPMName($methodId);
                $method_img = str_replace(" ", "", Tools::strtolower($method_name));

                $arrTemplateVars["logo"] = _MODULE_DIR_ . 'paytpv/views/img/apms/' . $method_img . '.svg';

                break;
            default:
                break;
        }
        return $arrTemplateVars;
    }


    public function getAPMName($methodId)
    {
        return [
            1 => "Tarjeta",
            10 => "Paypal",
            11 => "Bizum",
            12 => "iDEAL",
            13 => "Klarna Paynow",
            14 => "Giropay",
            15 => "MyBank",
            16 => "Multibanco",
            17 => "Trustly",
            18 => "Przelewy24",
            19 => "Bancontact",
            20 => "EPS",
            21 => "Tele2",
            22 => "Paysera",
            23 => "Post Finance",
            24 => "QIWI",
            25 => "Yandex",
            26 => "MTS",
            27 => "Beeline",
            28 => "Paysafecard",
            29 => "Skrill",
            30 => "WebMoney",
            33 => "Instant Credit",
            34 => "Klarna Payments"
        ][$methodId];
    }

    public function APMAsynchronous($methodId)
    {
        $arr = [
            16 // Multibanco
        ];
        return (in_array($methodId, $arr));
    }

    public function jetIframeForm()
    {
        return $this->context->smarty->fetch('module:paytpv/views/templates/hook/payment_jetIframe.tpl');
    }

    public function paycometPageForm()
    {
        return $this->context->smarty->fetch('module:paytpv/views/templates/hook/payment_paycomet.tpl');
    }

    public function getMerchantData($cart)
    {

        $MERCHANT_EMV3DS = $this->getEMV3DS($cart);
        $SHOPPING_CART = $this->getShoppingCart($cart);

        $datos = array_merge($MERCHANT_EMV3DS, $SHOPPING_CART);

        return $datos;
    }


    public function paytpvIframeUrl()
    {
        $cart = Context::getContext()->cart;

        // if not exist Cart -> Redirect to home
        if (!isset($cart->id)) {
            Tools::redirect('index');
        }

        $datos_pedido = $this->terminalCurrency($cart);
        $importe = $datos_pedido["importe"];
        $dcc = $datos_pedido["dcc"];
        $currency_iso_code = $datos_pedido["currency_iso_code"];
        $idterminal = $datos_pedido["idterminal"];

        $secure_pay = true;

        $values = array(
            'id_cart' => $cart->id,
            'key' => Context::getContext()->customer->secure_key
        );


        $ssl = Configuration::get('PS_SSL_ENABLED');

        $URLOK = Context::getContext()->link->getModuleLink($this->name, 'urlok', $values, $ssl);
        $URLKO = Context::getContext()->link->getModuleLink($this->name, 'urlko', $values, $ssl);

        $paytpv_order_ref = str_pad($cart->id, 8, "0", STR_PAD_LEFT);

        $language = $this->getPaycometLang($this->context->language->language_code);
        $productDescription = '';
        
        if (isset($this->context->customer->email)) $productDescription = $this->context->customer->email;

        $score = $this->transactionScore($cart);
        $scoring = $score["score"];

        $OPERATION = ($dcc == 1)?116 : 1;
        if ($this->apikey != '') {
            $userInteraction = 1;
            $merchantData = $this->getMerchantData($cart);

            try {
                $apiRest = new PaycometApiRest($this->apikey);

                $payment =  [
                    'terminal' => (int) $idterminal,
                    'order' => (string) $paytpv_order_ref,
                    'amount' => (string) $importe,
                    'methods' => [1],
                    'currency' => (string) $currency_iso_code,
                    'userInteraction' => (int) $userInteraction,
                    'secure' => (int) $secure_pay,
                    'productDescription' => $productDescription,
                    'merchantData' => $merchantData,
                    'urlOk' => $URLOK,
                    'urlKo' => $URLKO
                ];

                if ($scoring != null) {
                    $payment['scoring'] = (int) $scoring;
                }

                $formResponse = $apiRest->form(
                    $OPERATION,
                    $language,
                    $idterminal,
                    '',
                    $payment
                );

                $url_paytpv = "";
                if ($formResponse->errorCode == 0) {
                    $url_paytpv = $formResponse->challengeUrl;
                } else {
                    $url_paytpv = $formResponse->errorCode;
                }
            } catch (exception $e) {
                $url_paytpv = $e->getCode();
            }
        } else {
            $url_paytpv = "1004"; // Acceso no permitido
        }

        return $url_paytpv;
    }

    /**
     * return array Term,Currency,amount
     */
    public function terminalCurrency($cart)
    {

        // Si hay un terminal definido para la moneda del usuario devolvemos ese.
        $result = PaytpvTerminal::getTerminalCurrency($this->context->currency->iso_code, $cart->id_shop);
        // Not exists terminal in user currency
        if (empty($result) === true) {
            // Search for terminal in merchant default currency
            $id_currency = (int) Configuration::get('PS_CURRENCY_DEFAULT');
            $currency = new Currency($id_currency);
            $result = PaytpvTerminal::getTerminalCurrency($currency->iso_code, $cart->id_shop);

            // If not exists terminal in default currency. Select first terminal defined
            if (empty($result) === true) {
                $result = PaytpvTerminal::getFirstTerminal();
            }
        }

        $arrDatos = array();

        $arrDatos["idterminal"] = $result["idterminal"];
        $arrDatos["password"] = $result["password"];
        $arrDatos["jetid"] = $result["jetid"];
        $arrDatos["dcc"] = $result["dcc"];
        $arrDatos["currency_iso_code"] = $this->context->currency->iso_code;
        $arrDatos["importe"] = number_format($cart->getOrderTotal(true, Cart::BOTH) * 100, 0, '.', '');

        return $arrDatos;
    }


    public function isSecureTransaction($idterminal, $importe, $card)
    {
        $arrTerminal = PaytpvTerminal::getTerminalByIdTerminal($idterminal);

        $terminales = $arrTerminal["terminales"];
        $tdfirst = $arrTerminal["tdfirst"];
        $tdmin = $arrTerminal["tdmin"];
        // Transaccion Segura:

        // Si solo tiene Terminal Seguro
        if ($terminales == 0) {
            return true;
        }

        // Si esta definido que el pago es 3d secure y no estamos usando una tarjeta tokenizada
        if ($tdfirst && $card == 0) {
            return true;
        }

        // Si se supera el importe maximo para compra segura
        if ($terminales == 2 && ($tdmin > 0 && $tdmin < $importe)) {
            return true;
        }

        // Si esta definido como que la primera compra es Segura y es la primera compra aunque este tokenizada
        if ($terminales == 2 &&
            $tdfirst &&
            $card > 0 &&
            PaytpvOrder::isFirstPurchaseToken($this->context->customer->id, $card)
        ) {
                return true;
        }

        return false;
    }


    public function isSecurePay($importe)
    {
        // Terminal NO Seguro
        if ($this->terminales == 1) {
            return false;
        }
        // Ambos Terminales, Usar 3D False e Importe < Importe Min 3d secure
        if ($this->terminales == 2 && $this->tdfirst == 0 && ($this->tdmin == 0 || $importe <= $this->tdmin)) {
            return false;
        }
        return true;
    }

    public function hookDisplayOrderConfirmation($params)
    {
    }

    public function hookDisplayOrderDetail($params)
    {
        if (!$this->active) {
            return;
        }
        $this->context->smarty->assign(array(
            'this_path' => Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'modules/' . $this->name .
            '/'
        ));

        $id_order = Order::getOrderByCartId((int) $params["order"]->id_cart);
        $order = new Order($id_order);
        $message = Message::getMessagesByOrderId($order->id, true)[0]["message"];
        $methodData = json_decode(explode('|', $message, 2)[0]);

        if (strstr(Tools::strtolower($order->payment), "multibanco")) {
            // Multibanco
            $mbentity = $methodData->entityNumber; // Entidad
            $mbreference = $methodData->referenceNumber; // Referencia
            $this->context->smarty->assign('mbentity', $mbentity);
            $this->context->smarty->assign('mbreference', $mbreference);
        } else {
            $this->context->smarty->assign('display', "none");
        }

        $this->context->smarty->assign('result_txt', $result_txt);
        $this->context->smarty->assign('base_dir', __PS_BASE_URI__);

        return $this->display(__FILE__, 'order_detail.tpl');
    }


    public function hookDisplayPaymentReturn($params)
    {

        if (!$this->active) {
            return;
        }
        $this->context->smarty->assign(array(
            'this_path' => Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'modules/' . $this->name .
            '/'
        ));

        $id_order = Order::getOrderByCartId((int) $params["order"]->id_cart);
        $order = new Order($id_order);
        $message = Message::getMessagesByOrderId($order->id, true)[0]["message"];
        $methodData = json_decode(explode('|', $message, 2)[0]);

        if (strstr(Tools::strtolower($order->payment), "multibanco")) {
            $result_txt = $this->l('Your order will be sent as soon as we receive your payment.');
            $mbentity = $methodData->entityNumber; // Entidad
            $mbreference = $methodData->referenceNumber; // Referencia
            $this->context->smarty->assign('mbentity', $mbentity);
            $this->context->smarty->assign('mbreference', $mbreference);
        } else {
            $result_txt = $this->l(
                'Thank you for trusting us.
                 Your purchase has been formalized correctly and we will process your order soon.'
            );
            $this->context->smarty->assign('display', "none");
        }

        $this->context->smarty->assign('shop_name', $this->context->shop->name);
        $this->context->smarty->assign('reference', $order->reference);
        $this->context->smarty->assign('result_txt', $result_txt);
        $this->context->smarty->assign('base_dir', __PS_BASE_URI__);


        $this->html .= $this->display(__FILE__, 'payment_return.tpl');

        $result = PaytpvSuscription::getSuscriptionOrderPayments($id_order);
        if ($order->module == $this->name && !empty($result)) {
            $id_currency = $order->id_currency;
            $currency = new Currency((int) $id_currency);

            $suscription_type = $this->l('This order is a Subscription');

            $id_customer = $result["id_customer"];
            $periodicity = $result["periodicity"];
            $cycles = ($result['cycles'] != 0) ? $result['cycles'] : $this->l('N');
            $status = $result["status"];
            $price = number_format($result['price'], 2, '.', '') . " " . $currency->sign;
            $num_pagos = $result['pagos'];

            if ($status == 0) {
                $status = $this->l('ACTIVE');
            } elseif ($status == 1) {
                $status = $this->l('CANCELLED');
            } elseif ($num_pagos == $result['cycles'] && $result['cycles'] > 0) {
                $status = $this->l('ENDED');
            }

            $language = $this->getPaycometLang($this->context->language->language_code);


            $date_YYYYMMDD = ($language == "es") ?
                        date("d-m-Y", strtotime($result['date'])) : date("Y-m-d", strtotime($result['date']));


            $this->context->smarty->assign('suscription_type', $suscription_type);
            $this->context->smarty->assign('id_customer', $id_customer);
            $this->context->smarty->assign('periodicity', $periodicity);
            $this->context->smarty->assign('cycles', $cycles);
            $this->context->smarty->assign('status', $status);
            $this->context->smarty->assign('date_yyyymmdd', $date_YYYYMMDD);
            $this->context->smarty->assign('price', $price);

            $this->html .= $this->display(__FILE__, 'order_suscription_customer_info.tpl');
        }

        return $this->html;
    }

    private function getConfigValues()
    {
        $arrPaycomet = array('PAYTPV_CLIENTCODE', 'PAYTPV_INTEGRATION', 'PAYTPV_APIKEY', 'PAYTPV_NEWPAGEPAYMENT',
        'PAYTPV_IFRAME_HEIGHT', 'PAYTPV_SUSCRIPTIONS', 'PAYTPV_FIRSTPURCHASE_SCORING',
        'PAYTPV_FIRSTPURCHASE_SCORING_SCO', 'PAYTPV_SESSIONTIME_SCORING', 'PAYTPV_SESSIONTIME_SCORING_VAL',
        'PAYTPV_SESSIONTIME_SCORING_SCORE', 'PAYTPV_DCOUNTRY_SCORING', 'PAYTPV_DCOUNTRY_SCORING_VAL',
        'PAYTPV_DCOUNTRY_SCORING_SCORE', 'PAYTPV_IPCHANGE_SCORING', 'PAYTPV_IPCHANGE_SCORING_SCORE',
        'PAYTPV_BROWSER_SCORING', 'PAYTPV_BROWSER_SCORING_SCORE', 'PAYTPV_SO_SCORING',
        'PAYTPV_SO_SCORING_SCORE', 'PAYTPV_DISABLEOFFERSAVECARD');

        $arrApms = array('PAYTPV_APM_tarjeta', 'PAYTPV_APM_bizum', 'PAYTPV_APM_ideal', 'PAYTPV_APM_klarna_paynow',
        'PAYTPV_APM_giropay', 'PAYTPV_APM_mybank', 'PAYTPV_APM_multibanco_sibs', 'PAYTPV_APM_trustly',
        'PAYTPV_APM_przelewy24', 'PAYTPV_APM_bancontact', 'PAYTPV_APM_eps', 'PAYTPV_APM_tele2',
        'PAYTPV_APM_paysera', 'PAYTPV_APM_postfinance', 'PAYTPV_APM_qiwi_wallet', 'PAYTPV_APM_yandex_money',
        'PAYTPV_APM_mts', 'PAYTPV_APM_beeline', 'PAYTPV_APM_paysafecard', 'PAYTPV_APM_skrill',
        'PAYTPV_APM_webmoney', 'PAYTPV_APM_instant_credit', 'PAYTPV_APM_klarna_payments', 'PAYTPV_APM_paypal');

        $arrInstantCredit = array('PAYTPV_APM_instant_credit_simuladorCoutas','PAYTPV_APM_instant_credit_environment',
        'PAYTPV_APM_instant_credit_hashToken', 'PAYTPV_APM_instant_credit_minFin',
        'PAYTPV_APM_instant_credit_maxFin');

        $arrConfig = array_merge($arrPaycomet, $arrApms, $arrInstantCredit);

        return Configuration::getMultiple($arrConfig);
    }

    public function saveCard(
        $id_customer,
        $paytpv_iduser,
        $paytpv_tokenuser,
        $paytpv_cc,
        $paytpv_brand
    ) {

        $paytpv_cc = '************' . Tools::substr($paytpv_cc, -4);

        PaytpvCustomer::addCustomer($paytpv_iduser, $paytpv_tokenuser, $paytpv_cc, $paytpv_brand, $id_customer);

        $result = array();
        $result["paytpv_iduser"] = $paytpv_iduser;
        $result["paytpv_tokenuser"] = $paytpv_tokenuser;

        return $result;
    }


    public function removeCard($paytpv_iduser)
    {
        $arrTerminal = PaytpvTerminal::getTerminalByCurrency(
            $this->context->currency->iso_code,
            $this->context->shop->id
        );
        $idterminal = $arrTerminal["idterminal"];

        include_once(_PS_MODULE_DIR_ . '/paytpv/classes/PaycometApiRest.php');

        $result = PaytpvCustomer::getCustomerIduser($paytpv_iduser);

        if (empty($result) === true) {
            return false;
        } else {
            $paytpv_iduser = $result["paytpv_iduser"];
            $paytpv_tokenuser = $result["paytpv_tokenuser"];

            if ($this->apikey != '') {
                $apiRest = new PaycometApiRest($this->apikey);
                $result = $apiRest->removeUser(
                    $idterminal,
                    $paytpv_iduser,
                    $paytpv_tokenuser
                );
            } else {
                return false;
            }

            PaytpvCustomer::removeCustomerIduser((int) $this->context->customer->id, $paytpv_iduser);

            return true;
        }
    }


    public function removeSuscription($id_suscription)
    {
        $arrTerminal = PaytpvTerminal::getTerminalByCurrency(
            $this->context->currency->iso_code,
            $this->context->shop->id
        );
        $idterminal = $arrTerminal["idterminal"];

        include_once(_PS_MODULE_DIR_ . '/paytpv/classes/PaycometApiRest.php');

        // Datos usuario

        $result = PaytpvSuscription::getSuscriptionId((int) $this->context->customer->id, $id_suscription);

        if (empty($result) === true) {
            return false;
        } else {
            $paytpv_iduser = $result["paytpv_iduser"];
            $paytpv_tokenuser = $result["paytpv_tokenuser"];

            if ($this->apikey != '') {
                $apiRest = new PaycometApiRest($this->apikey);
                $removeSubscriptionResponse = $apiRest->removeSubscription(
                    $idterminal,
                    $paytpv_iduser,
                    $paytpv_tokenuser
                );
                $result["DS_RESPONSE"] = ($removeSubscriptionResponse->errorCode > 0)? 0 : 1;
            } else {
                $result["DS_RESPONSE"] = 0; // Error
            }

            if ((int) $result['DS_RESPONSE'] == 1) {
                PaytpvSuscription::removeSuscription((int) $this->context->customer->id, $id_suscription);

                return true;
            }
            return false;
        }
    }

    public function cancelSuscription($id_suscription)
    {
        $arrTerminal = PaytpvTerminal::getTerminalByCurrency(
            $this->context->currency->iso_code,
            $this->context->shop->id
        );
        $idterminal = $arrTerminal["idterminal"];

        include_once(_PS_MODULE_DIR_ . '/paytpv/classes/PaycometApiRest.php');

        // Datos usuario
        $result = PaytpvSuscription::getSuscriptionId((int) $this->context->customer->id, $id_suscription);
        if (empty($result) === true) {
            return false;
        } else {
            $paytpv_iduser = $result["paytpv_iduser"];
            $paytpv_tokenuser = $result["paytpv_tokenuser"];
            $order = new Order((int) $result["id_order"]);
            $order_ref = str_pad($order->id_cart, 8, "0", STR_PAD_LEFT);

            if ($this->apikey != '') {
                $apiRest = new PaycometApiRest($this->apikey);
                try {
                    $removeSubscriptionResponse = $apiRest->removeSubscription(
                        $idterminal,
                        $order_ref,
                        $paytpv_iduser,
                        $paytpv_tokenuser
                    );
                    if (!isset($removeSubscriptionResponse) || $removeSubscriptionResponse->errorCode > 0) {
                        $result["DS_RESPONSE"] = 0;
                    } else {
                        $result["DS_RESPONSE"] = 1;
                    }
                } catch (exception $e) {
                    $result["DS_RESPONSE"] = 0;
                }
            } else {
                $result["DS_RESPONSE"] = 0; // Error
            }
            $response = array();

            if ((int) $result['DS_RESPONSE'] == 1) {
                PaytpvSuscription::cancelSuscription((int) $this->context->customer->id, $id_suscription);
                $response["error"] = 0;
            } else {
                $response["error"] = 1;
            }
            return $response;
        }
    }

    public function validPassword($id_customer, $passwd)
    {
        $sql = 'select * from ' . _DB_PREFIX_ . 'customer where id_customer = ' . pSQL($id_customer) .
                ' and passwd="' . md5(pSQL(_COOKIE_KEY_ . $passwd)) . '"';
        $result = Db::getInstance()->getRow($sql);
        return (empty($result) === true) ? false : true;
    }


    /*
        Refund
    */
    public function hookActionProductCancel($params)
    {

        if (Tools::isSubmit('generateDiscount')) {
            return false;
        } elseif ($params['order']->module != $this->name ||
                !($order = $params['order']) ||
                !Validate::isLoadedObject($order)) {
            return false;
        } elseif (!$order->hasBeenPaid()) {
            return false;
        }

        $order_detail = new OrderDetail((int) $params['id_order_detail']);
        if (!$order_detail || !Validate::isLoadedObject($order_detail)) {
            return false;
        }

        $paytpv_order = PaytpvOrder::getOrder((int) $order->id);
        if (empty($paytpv_order)) {
            return false;
        }

        $paytpv_date = date("Ymd", strtotime($paytpv_order['date']));
        $paytpv_iduser = $paytpv_order["paytpv_iduser"];

        $id_currency = $order->id_currency;
        $currency = new Currency((int) $id_currency);

        $orderPayment = $order->getOrderPaymentCollection()->getFirst();
        $authcode = $orderPayment->transaction_id;

        $products = $order->getProducts();
        $cancel_quantity = Tools::getValue('cancelQuantity');

        $amt = (float) ($products[(int) $order_detail->id]['product_price_wt'] *
                        (int) $cancel_quantity[(int) $order_detail->id]);
        $amount = number_format($amt * 100, 0, '.', '');

        $paytpv_order_ref = str_pad((int) $order->id_cart, 8, "0", STR_PAD_LEFT);

        $response = $this->makeRefund(
            $params['order'],
            $paytpv_iduser,
            $order->id,
            $paytpv_order_ref,
            $paytpv_date,
            $currency->iso_code,
            $authcode,
            $amount,
            1
        );

        $refund_txt = $response["txt"];

        $message = $this->l('PAYCOMET Refund ') .  ", " . $amt . " " . $currency->sign . " [" . $refund_txt . "]" .
        '<br>';

        $this->addNewPrivateMessage((int) $order->id, $message);
    }

    private function makeRefund(
        $order,
        $paytpv_iduser,
        $order_id,
        $paytpv_order_ref,
        $paytpv_date,
        $currency_iso_code,
        $authcode,
        $amount,
        $type
    ) {

        $arrTerminal = PaytpvTerminal::getTerminalByCurrency($currency_iso_code, $order->id_shop);

        $idterminal = $arrTerminal["idterminal"];

        // Refund amount
        include_once(_PS_MODULE_DIR_ . '/paytpv/classes/PaycometApiRest.php');

        $ip = Tools::getRemoteAddr();

        if ($this->apikey != '') {
            $notifyDirectPayment = 2;

            $apiRest = new PaycometApiRest($this->apikey);
            $executeRefundReponse = $apiRest->executeRefund(
                $paytpv_order_ref,
                $idterminal,
                $amount,
                $currency_iso_code,
                $authcode,
                $ip,
                $notifyDirectPayment
            );

            $result = array();

            $result["DS_RESPONSE"] = ($executeRefundReponse->errorCode > 0)? 0 : 1;
            $result['DS_ERROR_ID'] = $executeRefundReponse->errorCode;

            if ($executeRefundReponse->errorCode == 0) {
                $result['DS_MERCHANT_AUTHCODE'] = $executeRefundReponse->authCode;
            }
        } else {
            $result["DS_RESPONSE"] = 0;
            $result['DS_ERROR_ID'] = 1004;
        }

        $response = array();

        $response["error"] = 0;
        $response["txt"] = $this->l('OK');

        // If is a subscription and error y initial refund.
        if ($result['DS_ERROR_ID'] == 130) {
            $paytpv_order_ref .= "[" . $paytpv_iduser . "]" . $paytpv_date;
            // Refund amount of transaction

            if ($this->apikey != '') {
                $notifyDirectPayment = 2;

                $apiRest = new PaycometApiRest($this->apikey);
                $executeRefundReponse = $apiRest->executeRefund(
                    $paytpv_order_ref,
                    $idterminal,
                    $amount,
                    $currency_iso_code,
                    $authcode,
                    $ip,
                    $notifyDirectPayment
                );

                $result["DS_RESPONSE"] = ($executeRefundReponse->errorCode > 0)? 0 : 1;
                $result['DS_ERROR_ID'] = $executeRefundReponse->errorCode;
                if ($executeRefundReponse->errorCode == 0) {
                    $result['DS_MERCHANT_AUTHCODE'] = $executeRefundReponse->authCode;
                }
            } else {
                $result["DS_RESPONSE"] = 0;
                $result['DS_ERROR_ID'] = 1004;
            }

            $response["error"] = 0;
            $response["txt"] = $this->l('OK');
        }

        if ((int) $result['DS_RESPONSE'] != 1) {
            $response["txt"] = $this->l('ERROR') . " " . $result['DS_ERROR_ID'];
            $response["error"] = 1;
        } else {
            $amount = number_format($amount / 100, 2, '.', '');
            PaytpvRefund::addRefund($order_id, $amount, $type);
        }
        return $response;
    }

    public function addNewPrivateMessage($id_order, $message)
    {
        if (!(bool) $id_order) {
            return false;
        }

        $new_message = new Message();
        $message = strip_tags($message, '<br>');

        if (!Validate::isCleanHtml($message)) {
            $message = $this->l('Payments messages are invalid, please check the module.');
        }

        $new_message->message = $message;
        $new_message->id_order = (int) $id_order;
        $new_message->private = 1;

        return $new_message->add();
    }

    /*

    Datos cuenta
    */

    public function hookDisplayCustomerAccount($params)
    {
        // If not disableoffersavecard
        if (!$this->disableoffersavecard == 1) {
            $this->smarty->assign('in_footer', false);
            return $this->display(__FILE__, 'my-account.tpl');
        }
    }


    /*

    Datos cuenta
    */

    public function hookDisplayAdminOrder($params)
    {

        if (Tools::isSubmit('submitPayTpvRefund')) {
            $this->doTotalRefund($params['id_order']);
        }

        if (Tools::isSubmit('submitPayTpvPartialRefund')) {
            $this->doPartialRefund($params['id_order']);
        }

        $order = new Order((int) $params['id_order']);
        $result = PaytpvSuscription::getSuscriptionOrderPayments($params["id_order"]);

        if ($order->module == $this->name && !empty($result)) {
            $id_currency = $order->id_currency;
            $currency = new Currency((int) $id_currency);

            $suscription = $result["suscription"];
            if ($suscription == 1) {
                $suscription_type = $this->l('This order is a Subscription');
            } else {
                $suscription_type = $this->l('This order is a payment for Subscription');
            }

            $id_customer = $result["id_customer"];
            $periodicity = $result["periodicity"];
            $cycles = ($result['cycles'] != 0) ? $result['cycles'] : $this->l('N');
            $status = $result["status"];

            $price = number_format($result['price'], 2, '.', '') . " " . $currency->sign;
            $num_pagos = $result['pagos'];

            if ($status == 0) {
                $status = $this->l('ACTIVE');
            } elseif ($status == 1) {
                $status = $this->l('CANCELLED');
            } elseif ($num_pagos == $result['cycles'] && $result['cycles'] > 0) {
                $status = $this->l('ENDED');
            }

            $date_YYYYMMDD = ($this->context->language->iso_code == "es") ?
                            date("d-m-Y", strtotime($result['date'])) : date("Y-m-d", strtotime($result['date']));


            $this->context->smarty->assign('suscription_type', $suscription_type);
            $this->context->smarty->assign('id_customer', $id_customer);
            $this->context->smarty->assign('periodicity', $periodicity);
            $this->context->smarty->assign('cycles', $cycles);
            $this->context->smarty->assign('status', $status);
            $this->context->smarty->assign('date_yyyymmdd', $date_YYYYMMDD);
            $this->context->smarty->assign('price', $price);

            $this->html .= $this->display(__FILE__, 'order_suscription_info.tpl');
        }

        // Total Refund Template
        if ($order->module == $this->name && $this->canRefund($order->id)) {
            $order_state = $order->current_state;
            $total_amount =  number_format($order->total_paid, 2, '.', '');

            $amount_returned =  PaytpvRefund::getTotalRefund($order->id);
            $amount_returned = number_format($amount_returned, 2, '.', '');

            $total_pending = $total_amount - $amount_returned;
            $total_pending =  number_format($total_pending, 2, '.', '');

            $currency = new Currency((int) $order->id_currency);

            $amt_sign = $total_pending . " " . $currency->sign;

            $error_msg = "";
            if (Tools::getValue('paytpPartialRefundAmount')) {
                $amt_refund = str_replace(",", ".", Tools::getValue('paytpPartialRefundAmount'));
                if (is_numeric($amt_refund)) {
                    $amt_refund = number_format($amt_refund, 2, '.', '');
                }

                if (Tools::getValue('paytpPartialRefundAmount') &&
                    ($amt_refund > $total_pending || $amt_refund == "" || !is_numeric($amt_refund))) {
                    $error_msg = Tools::displayError(
                        $this->l('The partial amount should be less than the outstanding amount')
                    );
                }
            }

            $arrRefunds = array();
            if ($amount_returned > 0) {
                $arrRefunds = PaytpvRefund::getRefund($order->id);
            }


            $this->context->smarty->assign(
                array(
                    'base_url' => _PS_BASE_URL_ . __PS_BASE_URI__,
                    'module_name' => $this->name,
                    'ref_paycomet' => str_pad((int) $order->id_cart, 8, "0", STR_PAD_LEFT),
                    'order_state' => $order_state,
                    'params' => $params,
                    'total_amount' => $total_amount,
                    'amount_returned' => $amount_returned,
                    'arrRefunds' => $arrRefunds,
                    'amount' => $amt_sign,
                    'sign'     => $currency->sign,
                    'error_msg' => $error_msg,
                    'ps_version' => _PS_VERSION_
                )
            );


            $template_refund = 'views/templates/admin/admin_order/refund.tpl';
            $this->html .=  $this->display(__FILE__, $template_refund);
            $this->postProcess();
        }

        return $this->html;
    }

    private function doPartialRefund($id_order)
    {

        $paytpv_order = PaytpvOrder::getOrder((int) $id_order);
        if (empty($paytpv_order)) {
            return false;
        }

        $order = new Order((int) $id_order);
        if (!Validate::isLoadedObject($order)) {
            return false;
        }

        $currency = new Currency((int) $order->id_currency);
        if (!Validate::isLoadedObject($currency)) {
            $this->_errors[] = $this->l('Invalid Currency');
        }

        if (count($this->_errors)) {
            return false;
        }

        $total_amount = $order->total_paid;

        $total_pending = $total_amount - PaytpvRefund::getTotalRefund($order->id);
        $total_pending =  number_format($total_pending, 2, '.', '');

        $amt_refund  = str_replace(",", ".", Tools::getValue('paytpPartialRefundAmount'));
        if (is_numeric($amt_refund)) {
            $amt_refund = number_format($amt_refund, 2, '.', '');
        }

        if ($amt_refund > $total_pending || $amt_refund == "" || !is_numeric($amt_refund)) {
            $this->errors[] = Tools::displayError($this->l('The partial amount should be less than the outstanding
             amount'));
        } else {
            $amt = $amt_refund;

            $paytpv_date = date("Ymd", strtotime($paytpv_order['date']));
            $paytpv_iduser = $paytpv_order["paytpv_iduser"];

            $id_currency = $order->id_currency;
            $currency = new Currency((int) $id_currency);

            $orderPayment = $order->getOrderPaymentCollection()->getFirst();
            $authcode = $orderPayment->transaction_id;

            $amount = number_format($amt * 100, 0, '.', '');

            $paytpv_order_ref = str_pad((int) $order->id_cart, 8, "0", STR_PAD_LEFT);

            $response = $this->makeRefund(
                $order,
                $paytpv_iduser,
                $order->id,
                $paytpv_order_ref,
                $paytpv_date,
                $currency->iso_code,
                $authcode,
                $amount,
                1
            );
            $refund_txt = $response["txt"];
            $message = $this->l('PAYCOMET Refund ') .  ", " . $amt . " " . $currency->sign .
                        " [" . $refund_txt . "]" .  '<br>';

            $this->addNewPrivateMessage((int) $id_order, $message);

            Tools::redirect($_SERVER['HTTP_REFERER']);
        }
    }

    private function doTotalRefund($id_order)
    {

        $paytpv_order = PaytpvOrder::getOrder((int) $id_order);
        if (empty($paytpv_order)) {
            return false;
        }

        $order = new Order((int) $id_order);
        if (!Validate::isLoadedObject($order)) {
            return false;
        }


        $currency = new Currency((int) $order->id_currency);
        if (!Validate::isLoadedObject($currency)) {
            $this->_errors[] = $this->l('Invalid Currency');
        }

        if (count($this->_errors)) {
            return false;
        }

        $total_amount = $order->total_paid;

        $total_pending = $total_amount - PaytpvRefund::getTotalRefund($order->id);
        $total_pending =  number_format($total_pending, 2, '.', '');

        $paytpv_date = date("Ymd", strtotime($paytpv_order['date']));
        $paytpv_iduser = $paytpv_order["paytpv_iduser"];

        $id_currency = $order->id_currency;
        $currency = new Currency((int) $id_currency);

        $orderPayment = $order->getOrderPaymentCollection()->getFirst();
        $authcode = $orderPayment->transaction_id;

        $amount = number_format($total_pending * 100, 0, '.', '');

        $paytpv_order_ref = str_pad((int) $order->id_cart, 8, "0", STR_PAD_LEFT);

        $response = $this->makeRefund(
            $order,
            $paytpv_iduser,
            $order->id,
            $paytpv_order_ref,
            $paytpv_date,
            $currency->iso_code,
            $authcode,
            $amount,
            0
        );
        $refund_txt = $response["txt"];
        $message = $this->l('PAYCOMET Total Refund ') .  ", " . $total_pending . " " . $currency->sign .
                             " [" . $refund_txt . "]" .  '<br>';

        if ($response['error'] == 0) {
            if (!PaytpvOrder::setOrderRefunded($id_order)) {
                die(Tools::displayError('Error when updating PAYCOMET database'));
            }

            $history = new OrderHistory();
            $history->id_order = (int) $id_order;
            $history->changeIdOrderState((int) Configuration::get('PS_OS_REFUND'), $history->id_order);
            $history->addWithemail();
            $history->save();
        }

        $this->addNewPrivateMessage((int) $id_order, $message);

        Tools::redirect($_SERVER['HTTP_REFERER']);
    }

    private function canRefund($id_order)
    {
        if (!(bool) $id_order) {
            return false;
        }

        $paytpv_order = PaytpvOrder::getOrder((int) $id_order);

        return $paytpv_order; //&& $paytpv_order['payment_status'] != 'Refunded';
    }

    /* Compruebas si un pedido está en estado Pagado */
    public function isOrderPaid($id_order)
    {
        $sql = 'SELECT COUNT(oh.`id_order_history`) AS nb
            FROM `' . _DB_PREFIX_ . 'order_history` oh
            WHERE oh.`id_order` = ' . (int) $id_order . '
        AND oh.id_order_state = ' . Configuration::get('PS_OS_PAYMENT');
        $n = Db::getInstance()->getValue($sql);
        return ($n > 0);
    }

    /* Compruebas si un pedido ya ha sido pagado en la hora anterior*/
    public function isPaymentProcesed($authCode)
    {
         $sql = 'SELECT COUNT(*) as np  FROM `' . _DB_PREFIX_ . 'order_payment`
            WHERE transaction_id = ' . $authCode . ' and date_add > DATE_ADD(SYSDATE(),INTERVAL - 1 hour)';
        $n = Db::getInstance()->getValue($sql);
        return ($n > 0);
    }
}
