<?php
/**
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

class PaytpvAccountModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    public function init()
    {

        parent::init();
    }


    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();

        $breadcrumb['links'][] = [
            'title' => $this->getTranslator()->trans('Your account', array(), 'Shop.Theme.Customeraccount'),
            'url' => $this->context->link->getPageLink('my-account', true),
        ];



        return $breadcrumb;
    }

    public function initContent()
    {
        parent::initContent();

        $error = "";

        $this->context->controller->addJqueryPlugin('fancybox');

        if (!Context::getContext()->customer->isLogged()) {
            Tools::redirect('index.php?controller=authentication&redirect=module&module=paytpv&action=account');
        }

        if (Context::getContext()->customer->id) {
            $paytpv = $this->module;

            $arrTerminal = PaytpvTerminal::getTerminalByCurrency($this->context->currency->iso_code);
            $idterminal = $arrTerminal["idterminal"];
            $jetid = $arrTerminal["jetid"];

            // SAVE BANKSTORE JET
            $token = Tools::getIsset("paytpvToken")?Tools::getValue("paytpvToken"):"";

            if ($token && Tools::strlen($token) == 64) {
                include_once(_PS_MODULE_DIR_.'/paytpv/classes/PaycometApiRest.php');

                if ($paytpv->apikey != '') {
                    $notify = 2;
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

                if (( int ) $addUserResponseErrorCode > 0) {
                    $error = $paytpv->l('Cannot operate with given credit card', 'account');
                } else {
                    $result = array();
                    if ($paytpv->apikey != '') {
                        $apiRest = new PaycometApiRest($paytpv->apikey);
                        $infoUserResponse = $apiRest->infoUser(
                            $idUser,
                            $tokenUser,
                            $idterminal
                        );

                        if ($infoUserResponse->errorCode == 0) {
                            $result['DS_MERCHANT_PAN'] = $infoUserResponse->pan;
                            $result['DS_CARD_BRAND'] = $infoUserResponse->cardBrand;

                            $paytpv->saveCard(
                                (int)$this->context->customer->id,
                                $idUser,
                                $tokenUser,
                                $result['DS_MERCHANT_PAN'],
                                $result['DS_CARD_BRAND']
                            );
                        }
                    }
                }
            }
            // FIN SAVE BANKSTORE JET

            $saved_card = PaytpvCustomer::getCardsCustomer((int) $this->context->customer->id);

            $language = $paytpv->getPaycometLang($this->context->language->language_code);

            $suscriptions = PaytpvSuscription::getSuscriptionCustomer($language, (int) $this->context->customer->id);

            $order = Context::getContext()->customer->id . "_" . Context::getContext()->shop->id;
            $operation = 107;
            $ssl = Configuration::get('PS_SSL_ENABLED');
            $paytpv_integration = (int)(Configuration::get('PAYTPV_INTEGRATION'));

            $URLOK=$URLKO=Context::getContext()->link->getModuleLink($paytpv->name, 'account', array(), $ssl);

            if ($paytpv->apikey != '') {
                try {
                    $apiRest = new PaycometApiRest($paytpv->apikey);
                    $formResponse = $apiRest->form(
                        $operation,
                        $language,
                        $idterminal,
                        '',
                        [
                            'terminal' => (int) $idterminal,
                            'order' => (string) $order,
                            'urlOk' => (string) $URLOK,
                            'urlKo' => (string) $URLKO
                        ],
                        []
                    );
                    $url_paytpv = "";
                    if ($formResponse->errorCode == 0) {
                        $url_paytpv = $formResponse->challengeUrl;
                    }
                } catch (exception $e) {
                    $url_paytpv = "";
                }
            } else {
                $url_paytpv = "";
            }
            $paytpv_path = Tools::getShopDomainSsl(true, true) . __PS_BASE_URI__ . 'modules/' . $paytpv->name . '/';

            $this->context->controller->addCSS($paytpv_path . 'views/css/account.css', 'all');
            $this->context->controller->addCSS($paytpv_path . 'views/css/fullscreen.css', 'all');
            $this->context->controller->addJS($paytpv_path . 'views/js/paytpv_account.js');

            $this->context->smarty->assign('url_paytpv', $url_paytpv);
            $this->context->smarty->assign('saved_card', $saved_card);
            $this->context->smarty->assign('suscriptions', $suscriptions);
            $this->context->smarty->assign('base_dir', __PS_BASE_URI__);

            $this->context->smarty->assign(
                'url_removecard',
                Context::getContext()->link->getModuleLink(
                    'paytpv',
                    'actions',
                    array("process" => "removeCard"),
                    true
                )
            );
            $this->context->smarty->assign(
                'url_savedesc',
                Context::getContext()->link->getModuleLink(
                    'paytpv',
                    'actions',
                    array("process" => "saveDescriptionCard"),
                    true
                )
            );
            $this->context->smarty->assign(
                'url_cancelsuscription',
                Context::getContext()->link->getModuleLink(
                    'paytpv',
                    'actions',
                    array("process" => "cancelSuscription"),
                    true
                )
            );


            $this->context->smarty->assign('newpage_payment', $paytpv->newpage_payment);
            $this->context->smarty->assign('paytpv_integration', $paytpv_integration);
            $this->context->smarty->assign('account', 1);

            $this->context->smarty->assign('jet_id', $jetid);

            $this->context->smarty->assign('jet_lang', $language);

            $this->context->smarty->assign('jet_paytpv', $paytpv->jet_paytpv);

            $this->context->smarty->assign(
                'paytpv_jetid_url',
                Context::getContext()->link->getModuleLink(
                    'paytpv',
                    'account',
                    array(),
                    $ssl
                )
            );

            $this->context->smarty->assign('error', $error);

            // Bankstore JET
            if ($paytpv_integration == 1) {
                $this->context->smarty->assign('this_path', $this->module->getPath());
            }

            $this->context->smarty->assign('status_canceled', $paytpv->l('CANCELLED'));

            $this->context->smarty->assign(
                array(
                    'this_path' => Tools::getShopDomainSsl(
                        true,
                        true
                    ) . __PS_BASE_URI__ . 'modules/' . $this->module->name . '/', 'base_dir' =>  __PS_BASE_URI__
                )
            );

            $this->setTemplate('module:paytpv/views/templates/front/paytpv-account.tpl');
        }
    }
}
