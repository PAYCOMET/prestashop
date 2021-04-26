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

var max_term = 0;

function checkAllTerminales()
{
    for(i=0;i<jQuery(".term").length;i++) {
        checkterminales($("#term_"+i));
    }
}

function checkterminales(element)
{
    cont = jQuery(element).attr('id').replace('term_','');
    jQuery(".term_s_container_"+cont).parents(".form-group").show();

    if (jQuery("#integration").val()==1) {
        jQuery(".class_jetid.term_s_container_"+cont).parents(".form-group").show();
    }else{
        jQuery(".class_jetid.term_s_container_"+cont).parents(".form-group").hide();
    }
}

function addTerminal()
{
    if (max_term == 0 ) {
        cont = jQuery(".term").length;
        max_term = cont;
    } else {
        cont = max_term+1;
    }

    var $term = jQuery(".terminal:first").closest('.panel').clone()
            .find("input").val("").end()
            .find("select").val("").end()
            .find("#term_0").attr("name","term["+cont+"]").end()
            .find("#term_0").attr("id","term_"+cont).end()
            .find(".term_s_container_0").addClass("term_s_container_"+cont).removeClass('term_s_container_0').end()
            .find("#pass_0").attr("name","pass["+cont+"]").end()
            .find("#pass_0").attr("id","pass_"+cont).end()
            .find("#jetid_0").attr("name","jetid["+cont+"]").end()
            .find("#jetid_0").attr("id","jeitd_"+cont).end()
            .find("#moneda_0").attr("name","moneda["+cont+"]").end()
            .find("#moneda_0").attr("id","moneda_"+cont).end()
            .find("#removeterminal").removeClass("hidden").show().end()
            .find("#addterminal").remove().end()
            .find("a").show().end().insertAfter( jQuery(".terminal:last").closest('.panel'));

    jQuery("#moneda_"+cont+" option:first").attr('selected','selected');
    checkterminales($("#term_"+cont));
    checkaddTerminal();

}

function removeTerminal(el)
{
    if (confirm(confirm_delete)) {
        jQuery(el).closest('.panel').remove();
        checkaddTerminal();
    }
}

function checkaddTerminal()
{
    if (jQuery(".term").length<jQuery("#moneda_0").find("option").size()) {
        jQuery("#addterminal").show()
    } else {
        jQuery("#addterminal").hide()
    }
}



function changeScoring(select)
{
    if (select.value==1)
        jQuery("." + select.id + "_data").show();
    else
        jQuery("." + select.id + "_data").hide();

}

function changeNewPage()
{
    if (jQuery("#newpage_payment").val()==2) {
        jQuery("#iframe_height").parents(".form-group").hide();
    } else {
        jQuery("#iframe_height").parents(".form-group").show();
    }
}

function checkScoring()
{
    firstPurchase = jQuery("#firstpurchase_scoring_off")
    if (firstPurchase.is(':checked') === false) {
        jQuery("#firstpurchase_scoring_score").parents('.form-group').show();
    } else {
        jQuery("#firstpurchase_scoring_score").parents('.form-group').hide();
    }

    sessiontime_scoring = jQuery("#sessiontime_scoring_off")
    if (sessiontime_scoring.is(':checked') === false) {
        jQuery("#sessiontime_scoring_score").parents('.form-group').show();
        jQuery("#sessiontime_scoring_val").parents('.form-group').show();
    } else {
        jQuery("#sessiontime_scoring_score").parents('.form-group').hide();
        jQuery("#sessiontime_scoring_val").parents('.form-group').hide();
    }

    dcountry_scoring = jQuery("#dcountry_scoring_off")
    if (dcountry_scoring.is(':checked') === false) {
        jQuery("#dcountry_scoring_score").parents('.form-group').show();
        jQuery("#dcountry_scoring_val\\[\\]").parents('.form-group').show();
    } else {
        jQuery("#dcountry_scoring_score").parents('.form-group').hide();
        jQuery("#dcountry_scoring_val\\[\\]").parents('.form-group').hide();

    }

    ip_change_scoring = jQuery("#ip_change_scoring_off")
    if (ip_change_scoring.is(':checked') === false) {
        jQuery("#ip_change_scoring_score").parents('.form-group').show();
    } else {
        jQuery("#ip_change_scoring_score").parents('.form-group').hide();
    }

    browser_scoring = jQuery("#browser_scoring_off")
    if (browser_scoring.is(':checked') === false) {
        jQuery("#browser_scoring_score").parents('.form-group').show();
    } else {
        jQuery("#browser_scoring_score").parents('.form-group').hide();
    }

    so_scoring = jQuery("#so_scoring_off")
    if (so_scoring.is(':checked') === false) {
        jQuery("#so_scoring_score").parents('.form-group').show();
    } else {
        jQuery("#so_scoring_score").parents('.form-group').hide();
    }
}

$(document).ready(function()
{
    checkAllTerminales();
    checkaddTerminal();
    checkScoring();
    changeNewPage();

    jQuery("input[name$='scoring']").on('change', function()
    {
        checkScoring();
    })

    jQuery('#integration').on('change', function()
    {
        checkAllTerminales();
    })

    jQuery('.terminales').live('change', function()
    {
        checkterminales(this);
    })
    jQuery('.addTerminal').live('click', function()
    {
        addTerminal(this);
    })
    jQuery('.removeTerminal').live('click', function()
    {
        removeTerminal(this);
    })

    jQuery("#newpage_payment").on('change', function()
    {
        changeNewPage();
    })

});