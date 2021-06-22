{*
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
    *}

<style>
    .payment-option-wrap{
        border: 1px solid #d6d4d4;
        border-radius: 4px; color: #333;
        display: block;
        font-size: 17px;
        letter-spacing: -1px; line-height: 23px;
        padding: 34px 10px 34px 10px;
        position: relative;
    }
    .payment-option-content{
        display: flex;
        flex-flow: row;
        justify-content: space-between;
        padding-bottom: 20px;
        align-items: center;
    }
    .payment-option-content img{
        margin:0 15px;
        width:20%;
    }
    .payment-option-content div{
        width:75%;
    }
    @media (max-width:640px){
        .payment-option-content{
            flex-flow: column;
        }
        .payment-option-content img{
            width:100%;
            margin:15px;
        }
        .payment-option-content div{
            width:100%;
        }
    }
</style>

<div class="payment-option-wrap" style="
        height: {if $simuladorCuotas == 1}auto  {else} 150px{/if}">

        <div id="encabezado" class="payment-option-content">
            <img src="{$logo|escape:'html':'UTF-8'}" alt="{l s='Instant installment payment' mod='paytpv'}">
            <div>{l s='Quick and paperless process with the confidence of Banco Sabadell. Have your ID at hand to process the financing.' mod='paytpv'}</div>
        </div>
        {if $simuladorCuotas == 1}
            <div class="ic-configuration" style="display:none;">{$hashToken|escape:'html':'UTF-8'}</div>
            <div class="ic-simulator" amount="{$importe_financiar|escape:'html':'UTF-8'}"></div>
            <script src="https://instantcredit.net/simulator/ic-simulator.js" charset="UTF-8"></script>
        {/if}
</div>





