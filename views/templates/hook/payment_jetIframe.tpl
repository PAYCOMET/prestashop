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

    <form action="{$paytpv_jetid_url|escape:'htmlall':'UTF-8':FALSE}" method="POST" class="paytpv_jet paycomet_jet" id="paycometPaymentForm" style="clear:left;">
    <div class="row">
        <div class="paytpv">

            <p style="padding-top: 5px;"><a href="http://www.paycomet.com" target="_blank"><img src="{$this_path|escape:'htmlall':'UTF-8':FALSE}views/img/paytpv_logo.svg" width="135"></a></p>

            {if ($msg_paytpv!="")}
            <p>
                <span class="message">{$msg_paytpv|escape:'htmlall':'UTF-8':FALSE}</span>
            </p>
            {/if}

            {if ($active_suscriptions)}
                {include file='modules/paytpv/views/templates/hook/inc_payment_suscription.tpl'}
            {/if}

            <div id="saved_cards" style="display:none">
                {include file='modules/paytpv/views/templates/hook/inc_payment_cards.tpl'}
            </div>

            {if (!$disableoffersavecard==1)}
                {include file='modules/paytpv/views/templates/hook/inc_payment_savecards.tpl'}
            {/if}

            <br class="clear"/>

            <div class="payment_module paytpv_iframe">

                {include file='modules/paytpv/views/templates/hook/inc_payment_jetIframe.tpl'}

            </div>
        </div>
    </div>

    <input type="hidden" name="paytpv_module" id="paytpv_module" value="{$link->getModuleLink('paytpv', 'actions',[], true)|escape:'htmlall':'UTF-8'}">
    <input type="hidden" name="paytpv_integration" id="paytpv_integration" value="{$paytpv_integration|escape:'htmlall':'UTF-8':FALSE}">
    <input type="hidden" name="id_cart" id="id_cart"  value="{$id_cart|escape:'htmlall':'UTF-8':FALSE}">

    </form>
