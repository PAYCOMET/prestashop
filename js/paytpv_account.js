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


$(document).ready(function() {
    $("#open_conditions").fancybox({
            autoSize:false,
            'width':parseInt($(window).width() * 0.7)
        });
    
    $(".remove_card").on("click", function(e){   
        e.preventDefault();
        $("#paytpv_iduser").val($(this).attr("id"));
        cc_iduser = $("#cc_"+$(this).attr("id")).val()
        confirm(msg_removecard + ": " + cc_iduser, true, function(resp) {
            if (resp)   removeCard();
        });
    });

    $(".save_desc").on("click", function(e){   
        e.preventDefault();
        $("#paytpv_iduser").val($(this).attr("id"));
        card_desc = $("#card_desc_"+$(this).attr("id")).val()
        confirm(msg_savedesc + ": " + card_desc, true, function(resp) {
            if (resp)   saveDescriptionCard();
        });
    });

    $(".cancel_suscription").on("click", function(e){   
        e.preventDefault();
        $("#id_suscription").val($(this).attr("id"));
        confirm(msg_cancelsuscription, true, function(resp) {
            if (resp)   cancelSuscription();
        });
    });

});

function confirm(msg, modal, callback) {
    $.fancybox("#confirm",{
        modal: modal,
        beforeShow: function() {
            $(".title").html(msg);
        },
        afterShow: function() {
            $(".confirm").on("click", function(event){
                if($(event.target).is(".yes")){
                    ret = true;
                } else if ($(event.target).is(".no")){
                    ret = false;
                }
                $.fancybox.close();
            });
        },
        afterClose: function() {
            callback.call(this, ret);
        }
    });
}

function alert(msg) {
    $.fancybox("#alert",{
        beforeShow: function() {
            $(".title").html(msg);
        },
        modal: false,
    });
}

function vincularTarjeta(){
    if ($("#savecard").is(':checked')){
        $('#savecard').attr("disabled", true);
        if ($('#newpage_payment').val()==2)
            window.location = ($("#paytpv_iframe").attr('src'));
        else{
            $('#close_vincular').show();
            $('#nueva_tarjeta').show();
        }
    }else{
        alert(msg_accept);
    }

}

function close_vincularTarjeta(){
    $('#savecard').attr("disabled", false);
    $('#nueva_tarjeta').hide();
    $('#close_vincular').hide();
}

function confirmationRemove(paytpv_cc){
    $("#cc").html(paytpv_cc);
    $("#paytpv_cc").val(paytpv_cc);
    $("#deltecard").open();
}

function removeCard()
{
    paytpv_iduser = $("#paytpv_iduser").val();
    $.ajax({
        url: url_removecard,
        type: "POST",
        data: {
            'paytpv_iduser': paytpv_iduser,
            'ajax': true
        },
        success: function(result)
        {
            if (result == '0')
            {
               $("#card_"+paytpv_iduser).fadeOut(1000);
            }
        }
    });
    
};

function saveDescriptionCard()
{
    paytpv_iduser = $("#paytpv_iduser").val();
    car_desc = $("#card_desc_"+paytpv_iduser).val();
    $.ajax({
        url: url_savedesc,
        type: "POST",
        data: {
            'paytpv_iduser': paytpv_iduser,
            'card_desc': car_desc,
            'ajax': true
        },
        success: function(result)
        {
            if (result == '0')
            {
               alert(msg_descriptionsaved)
               
            }
        }
    });
    
};


function cancelSuscription()
{
    id_suscription = $("#id_suscription").val();
    $.ajax({
        url: url_cancelsuscription,
        type: "POST",
        data: {
            'id_suscription': id_suscription,
            'ajax': true
        },
        success: function(result)
        {
            if (result["error"] == '0')
            {
                $("#suscription_"+id_suscription).find(".button_del").html("<span class=\"canceled_suscription\">"+status_canceled+"</span>");
            }else if (result["txt"]!=""){
                alert(result["txt"]);

            }
        },
        dataType:"json"
    });
    
};


function takingOff() {
    ShowHidePaymentButton(false);
    var x = new PAYTPV.Tokenizator();
    x.getToken(document.forms["paytpvPaymentForm"], boarding);
    return false;
};

function boarding(passenger) {
    document.getElementById("paymentErrorMsg").innerHTML = "";
    if (passenger.errorID !== 0 || passenger.paytpvToken === "") {
        document.getElementById("paymentErrorMsg").innerHTML = passenger.errorText;
    } else {
        
        var newInputField = document.createElement("input");

        newInputField.type = "hidden";
        newInputField.name = "paytpvToken";
        newInputField.value = passenger.paytpvToken;

        var paytpvPaymentForm = document.forms["paytpvPaymentForm"];
        paytpvPaymentForm.appendChild(newInputField);

        paytpvPaymentForm.submit();
        
    }
}

function ShowHidePaymentButton(show){
  
  if (show){
    $("#clockwait_jet").hide('fast');
    $("#btnforg").show('fast');
  }else{
    $("#btnforg").hide('fast');
    $("#clockwait_jet").show('fast');
  }
}