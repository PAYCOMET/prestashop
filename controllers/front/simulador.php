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

class PaytpvSimuladorModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        /** @var Paytpv $paytpv */
        $paytpv = $this->module;

        $urlSimulatorJs = $paytpv->getICSimulatorUrl();

        parent::initContent();

        $importe_financiar = Tools::getValue('importe_financiar');
        $hashToken = Configuration::get('PAYTPV_APM_instant_credit_hashToken');

        if (isset($importe_financiar)) {
            $importe_financiar = str_replace(',', '.', $importe_financiar);
            $html = '<html>' .
                    '<body>' .
                    '<div class="ic-configuration" style="display:none;">' . $hashToken . '</div>' .
                    '<div class="ic-simulator" amount="' . $importe_financiar . '"></div>' .
                    '<script src="' . $urlSimulatorJs . '"></script>' .
                    '</body>' .
                    '</html>';
            header_remove();
            header('content-type: text/html');
            echo $html;
            exit(0);
        }
    }
}
