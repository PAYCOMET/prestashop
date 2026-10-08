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
async function applePayAvailability() {
    jQuery.getScript("https://applepay.cdn-apple.com/jsapi/1.latest/apple-pay-sdk.js");
    try {
        const response = await ApplePaySession.canMakePayments();
        if (response) {
            console.log("Apple Pay disponible");
            return;
        }
    } catch (error) {}
    document.querySelector('#paytpv_apple_pay')?.closest('.payment-option')?.style.setProperty('display', 'none');
}

function checkConditions2() {
    var cond_paytpv = true;
    $('#conditions-to-approve input[type="checkbox"]').each(function (_, checkbox) {
        if (!checkbox.checked) {
            cond_paytpv = false;
        }
    });
    if ($("#card").val() == "0") {
        if (cond_paytpv) {
            $("#paytpv_checkconditions_apple_pay").hide();
            $("#paytpv_applepay_button").show();
        } else {
            $("#paytpv_checkconditions_apple_pay").removeClass("hidden").show();
            $("#paytpv_applepay_button").hide();
        }
    } else {
        if (cond_paytpv) {
            $("#paytpv_checkconditions_apple_pay").hide();
            $("#paytpv_applepay_button").show();
        } else {
            $("#paytpv_checkconditions_apple_pay").removeClass("hidden").show();
            $("#paytpv_applepay_button").hide();
        }
    }

    return cond_paytpv;
}

applePayAvailability()
checkConditions2()

$(document).ready(function () {
    
    // Conditions
    $("body").on("change", '#conditions-to-approve input[type="checkbox"]', function (event) {
        checkConditions2();
    });

});