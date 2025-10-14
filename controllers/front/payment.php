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

class PaytpvPaymentModuleFrontController extends ModuleFrontController
{
    public $ssl = true;
    public $display_top = false;

    public function initContent()
    {
        if (property_exists($this, 'display_column_left')) {
            $this->display_column_left = false;
        }
        if (property_exists($this, 'display_column_right')) {
            $this->display_column_right = false;
        }
        $this->display_top = false;
        if (property_exists($this, 'display_menu')) {
            $this->display_menu = false;
        }

        parent::initContent();

        /** @var Paytpv $paytpv */
        $paytpv = $this->module;

        $this->context->smarty->assign('msg_paytpv', '');

        $msg_paytpv = '';

        $this->context->smarty->assign('msg_paytpv', $msg_paytpv);

        // Valor de compra
        $id_currency = $this->context->cart->id_currency;

        $currency = new Currency((int) $id_currency);
        $importe_tienda = $this->context->cart->getOrderTotal(true, Cart::BOTH);

        $ssl = Configuration::get('PS_SSL_ENABLED');
        $values = [
            'id_cart' => (int) $this->context->cart->id,
            'key' => $this->context->customer->secure_key,
        ];

        $active_suscriptions = (int) Configuration::get('PAYTPV_SUSCRIPTIONS');

        $saved_card = PaytpvCustomer::getCardsCustomer($this->context->customer->id);

        $index = 0;
        foreach ($saved_card as $key => $val) {
            $values_aux = array_merge(
                $values,
                ['TOKEN_USER' => $val['TOKEN_USER']]
            );
            $saved_card[$key]['url'] = $this->context->link->getModuleLink(
                $paytpv->name,
                'capture',
                $values_aux,
                $ssl
            );
            ++$index;
        }
        $saved_card[$index]['url'] = 0;

        $cart = $this->context->cart;
        $datos_pedido = $paytpv->terminalCurrency($cart);
        $jetid = $datos_pedido['jetid'];

        $newpage_payment = (int) Configuration::get('PAYTPV_NEWPAGEPAYMENT');
        $paytpv_integration = (int) Configuration::get('PAYTPV_INTEGRATION');

        $iframe_height = $paytpv->iframe_height;

        $disableoffersavecard = Configuration::get('PAYTPV_DISABLEOFFERSAVECARD');
        $iframe_height = $paytpv->iframe_height;

        $this->context->smarty->assign('newpage_payment', $newpage_payment);
        $this->context->smarty->assign('iframe_height', $iframe_height);
        $this->context->smarty->assign('iframe_height', $iframe_height);
        $this->context->smarty->assign('paytpv_integration', $paytpv_integration);
        $this->context->smarty->assign('account', 0);

        $this->context->smarty->assign('jet_id', $jetid);

        $this->context->smarty->assign('jet_paytpv', $paytpv->jet_paytpv);

        $apmsUrls = $paytpv->getUserApmsForPayment();
        $this->context->smarty->assign('apmsUrls', $apmsUrls);

        $language_data = explode('-', $this->context->language->language_code);
        $language = $language_data[0];

        $this->context->smarty->assign('jet_lang', $language);

        $this->context->smarty->assign(
            'paytpv_jetid_url',
            $this->context->link->getModuleLink(
                $paytpv->name,
                'capture',
                [],
                $ssl
            )
        );

        $this->context->smarty->assign(
            'paytpv_module',
            $this->context->link->getModuleLink(
                $paytpv->name,
                'actions',
                [],
                $ssl
            )
        );

        $tmpl_vars = [];
        $tmpl_vars['capture_url'] = $this->context->link->getModuleLink(
            $paytpv->name,
            'capture',
            $values,
            $ssl
        );
        $this->context->smarty->assign('active_suscriptions', $active_suscriptions);
        $this->context->smarty->assign('saved_card', $saved_card);
        $this->context->smarty->assign('id_cart', $this->context->cart->id);

        $this->context->smarty->assign('base_dir', __PS_BASE_URI__);

        $tmpl_vars = array_merge(
            [
                'this_path' => $paytpv->getPath(),
            ]
        );
        $this->context->smarty->assign($tmpl_vars);

        // call your media file like this
        $this->context->controller->addJqueryPlugin('fancybox');
        $this->context->controller->registerStylesheet('paytpv-payment', 'modules/paytpv/views/css/payment.css');
        $this->context->controller->registerStylesheet('paytpv-fullscreen', 'modules/paytpv/views/css/fullscreen.css');
        $this->context->controller->registerJavascript('paytpv-js', 'modules/paytpv/views/js/paytpv.js');

        $this->context->smarty->assign('total_amount', $importe_tienda);

        $this->context->smarty->assign('currency_symbol', $currency->sign);

        $this->context->smarty->assign('disableoffersavecard', $disableoffersavecard);

        $iframeURL = $paytpv->paytpvIframeURL();
        if (filter_var($iframeURL, FILTER_VALIDATE_URL) === false) {
            $paytpv_error = $iframeURL;
            $iframeURL = '';
            $this->context->smarty->assign('paytpv_error', $paytpv_error);
            $this->setTemplate('module:paytpv/views/templates/hook/payment_error.tpl');
        } else {
            // Si el pago es en PAYCOMET redireccionamos directamente
            if ($newpage_payment == 2) {
                Tools::redirect($iframeURL);
            }

            $this->context->smarty->assign('paytpv_iframe', $iframeURL);
            $this->setTemplate('module:paytpv/views/templates/hook/payment_bsiframe_newpage.tpl');
        }
    }
}
