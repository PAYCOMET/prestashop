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
*  @author     Jose Ramon Garcia <jrgarcia@paytpv.com>
*  @copyright  2015 PAYTPV ON LINE S.L.
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/


$(document).ready(function() {
    paytpv_initialize()
    $("#open_conditions").fancybox({
            autoSize:false,
            'width':parseInt($(window).width() * 0.7)
        });


    

    $("#open_directpay").fancybox({
            'beforeShow': onOpenDirectPay
        });

    $("body").on("click",".exec_directpay",function(event) {
        event.preventDefault();
        $.fancybox.close();
        $(".paytpv_pay").hide();
        $("#clockwait").show();
        $("#pago_directo").attr("action",$("#card").val());
        $("#card option,#suscripcion").attr("disabled", true);
        $("#pago_directo").submit();
    });


    $("body").on("change",".paytpv #susc_periodicity, .paytpv #susc_cycles",function(){
        validateSuscription($(this));
    });

    $("body").on("change","#card",function(event){
        
        if ($("#payment_mode_paytpv")){
            $("#payment_mode_paytpv").attr("data-payment-link",$(this).val());
        }
    });

    // Conditions 
    $("body").on("change",'#conditions-to-approve input[type="checkbox"]',function (event){
        checkConditions();
        
    });

});

function paytpv_initialize(){
    $("#div_periodicity,.paytpv_iframe").hide();
    checkCard();
}

function check_suscription(){
    if ($("#suscripcion").is(':checked')){
        $("#div_periodicity").show();
        suscribeJQ();
        $("#saved_cards, #storingStep").hide();
    }else{
        $("#div_periodicity,.paytpv_iframe").hide();
        addCardJQ();
        checkCard();
    }
}

function checkConditions(){
    var cond_paytpv = true;

     $('#conditions-to-approve input[type="checkbox"]').each(function (_, checkbox) {
        if (!checkbox.checked) {
          cond_paytpv = false;
        }
    });

    if ($("#card").val()=="0"){
        if (cond_paytpv){
            $("#storingStep,.paytpv_iframe").removeClass("hidden").show();
            $("#paytpv_checkconditions").hide();
        }else{
            $("#paytpv_checkconditions").removeClass("hidden").show();
            $("#storingStep,.paytpv_iframe").removeClass("hidden").hide();
        }
        $("#open_directpay,#exec_directpay").hide();
    }else{
        $("#storingStep,.paytpv_iframe").hide();
        if (cond_paytpv){
            $("#paytpv_checkconditions").hide();
            $("#open_directpay,#exec_directpay").show();
        }else{
            $("#paytpv_checkconditions").removeClass("hidden").show();
            $("#open_directpay,#exec_directpay").hide();
        }
    }

    return cond_paytpv;
}

function checkCard(){

    // Show Cards only if exists saved cards
    if ($("#card option").length>1){
        $("#saved_cards").show();
        if ($("#payment_mode_paytpv")==2){
            $("#button_directpay").hide();
            $("#payment_mode_paytpv").attr("data-payment-link",$("#card").val());
        }
    }
    
    cond_paytpv = checkConditions();  
    
    

}

function validateSuscription(element){ 
    switch (element.attr("id")){
        case 'susc_periodicity':
            $("#susc_cycles option").each(function() {
                if ($(this).val()*element.val()>(365*5))
                    $(this).hide();
                else
                    $(this).show();
            });
        break;
        case 'susc_cycles':
            $("#susc_periodicity option").each(function() {
                if ($(this).val()*element.val()>(365*5))
                    $(this).hide();
                else
                    $(this).show();
            });
        break;
    }
}

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



function onOpenDirectPay(){
    $("#datos_tarjeta").html($("#card :selected").text());
    var suscripcion="&suscripcion="+($("#suscripcion").is(':checked')?1:0)+"&periodicity="+$("#susc_periodicity").val()+"&cycles="+$("#susc_cycles").val();
    
    $("#pago_directo").attr("action",$("#card").val()+suscripcion);
}

function addParam(url,param){   

    var hasQuery = url.indexOf("?") + 1;
    var hasHash = url.indexOf("#") + 1;
    var appendix = (hasQuery ? "&" : "?") + param;

    return hasHash ? href.replace("#", appendix + "#") : url + appendix;

}


function saveOrderInfoJQ(paytpv_suscripcion){
    switch (paytpv_suscripcion){
        case 0: // Normal Payment
            paytpv_agree = $("#savecard").is(':checked')?1:0;
            paytpv_periodicity = 0;
            paytpv_cycles = 0;
        break;
        case 1: // Suscription
            paytpv_agree = 0;
            paytpv_periodicity = $("#susc_periodicity").val();
            paytpv_cycles = $("#susc_cycles").val()
            break;
    }

    $.ajax({
        url: addParam($("#paytpv_module").val(),'process=saveOrderInfo'),
        type: "POST",
        data: {
            'paytpv_agree': paytpv_agree,
            'paytpv_suscripcion': paytpv_suscripcion,
            'paytpv_periodicity': paytpv_periodicity,
            'paytpv_cycles': paytpv_cycles,
            'id_cart' : $("#id_cart").val(),
            'ajax': true
        },
        dataType:"json"
    })
}

function addCardJQ(){
    $("#paytpv_iframe").attr("src","");
    $(".paytpv_iframe").show();
    $("#ajax_loader").show();
    paytpv_agree = $("#savecard").is(':checked')?1:0;
    $.ajax({
        url: addParam($("#paytpv_module").val(),'process=addCard'),
        type: "POST",
        data: {
            'paytpv_agree': paytpv_agree,
            'id_cart' : $("#id_cart").val(),
            'ajax': true
        },
        success: function(result)
        {   
            if (result.error=='0')
            {
                $("#paytpv_iframe").attr("src",result.url).one("load",function() {
                    $("#ajax_loader").hide();
                });

                //$(".paytpv_iframe").show(500);
            }
        },
        dataType:"json"
    });
}

function suscribeJQ(){
    $("#paytpv_iframe").attr("src","");
    $(".paytpv_iframe").show();
    $("#ajax_loader").show();
    $.ajax({
        url: addParam($("#paytpv_module").val(),'process=suscribe'),
        type: "POST",
        data: {
            'paytpv_agree': 0,
            'paytpv_suscripcion': 1,
            'paytpv_periodicity': $("#susc_periodicity").val(),
            'paytpv_cycles': $("#susc_cycles").val(),
            'id_cart' : $("#id_cart").val(),
            'ajax': true
        },
        success: function(result)
        {
                       
            if (result.error=='0')
            {
                $("#storingStep").hide();
                $("#paytpv_iframe").attr("src",result.url).one("load",function() {
                    $("#ajax_loader").hide();
                });;
                //$(".paytpv_iframe").show(500);
            }
        },
        dataType:"json"
    });
}




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
        ShowHidePaymentButton(true);
    } else {
        
        var newInputField = document.createElement("input");

        newInputField.type = "hidden";
        newInputField.name = "paytpvToken";
        newInputField.value = passenger.paytpvToken;

        var paytpvPaymentForm = document.forms["paytpvPaymentForm"];
        paytpvPaymentForm.appendChild(newInputField);

        var newInputField = document.createElement("input");

        newInputField.type = "hidden";
        newInputField.name = "savecard_jet";
        newInputField.value = $("#savecard").is(':checked')?1:0;
        paytpvPaymentForm.appendChild(newInputField);


        if ($("#suscription") && $("#suscripcion").is(':checked')){
            var newInputField = document.createElement("input");

            newInputField.type = "hidden";
            newInputField.name = "suscription";
            newInputField.value = 1;

            var paytpvPaymentForm = document.forms["paytpvPaymentForm"];
            paytpvPaymentForm.appendChild(newInputField);


            var newInputField = document.createElement("input");

            newInputField.type = "hidden";
            newInputField.name = "periodicity";
            newInputField.value = $("#susc_periodicity").val();

            var paytpvPaymentForm = document.forms["paytpvPaymentForm"];
            paytpvPaymentForm.appendChild(newInputField);


            var newInputField = document.createElement("input");

            newInputField.type = "hidden";
            newInputField.name = "cycles";
            newInputField.value = $("#susc_cycles").val();

            var paytpvPaymentForm = document.forms["paytpvPaymentForm"];
            paytpvPaymentForm.appendChild(newInputField);


        }
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