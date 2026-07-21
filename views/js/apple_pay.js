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
*  @author     PAYCOMET <info@paycomet.com>
*  @copyright  2019 PAYTPV ON LINE ENTIDAD DE PAGO S.L
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

function applePayAvailability() {
    jQuery.getScript("https://applepay.cdn-apple.com/jsapi/1.latest/apple-pay-sdk.js");

    ApplePaySession.canMakePaymentsWithActiveCard().then((resp) => {
        console.log(resp);

        if (ApplePaySession.canMakePayments()) {
            console.log("Apple Pay disponible");
        }
    }).catch((e) => {
        var style = document.createElement('style');
        style.textContent = '.payment - option: has(#paytpv_apple_pay) {display: none;}';
        document.head.appendChild(style);
    })
}
applePayAvailability()