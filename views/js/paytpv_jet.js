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

function buildED() {
    var t = document.getElementById('expiry_date').value,
        n = t.substr(0, 2),
        a = t.substr(3, 2);
    $('[data-paycomet=\'dateMonth\']').val(n), $('[data-paycomet=\'dateYear\']').val(a);
}
    
function ShowHidePaymentButton(show){
    if (show){
      $("#clockwait_jet").hide();
      $("#btnforg").show();
    }else{
      $("#btnforg").hide();
      $("#clockwait_jet").show();
    }
  }

$(document).ready(function() {

    $('#expiry_date').on('input',function()
    {
        var curLength = $(this).val().length;
        if(curLength === 2){
            var newInput = $(this).val();
            newInput += '/';
            $(this).val(newInput);
        }
    });
    
    // JetIframe -> newpage_payment=0
    $("body").on("submit",".paycomet_jet",function(event)
    {
        event.preventDefault();
        if ($("#card").val()!="0"){            
            window.location.href = $("#card").val();
        } else{                        
            $("#submit_jet").click();
            // Si hay error no continuamos
            if ($("#paymentErrorMsg").innerHTML!="") {
                $("#payment-confirmation .btn").attr("disabled", false);
            }
        }
    });
})
