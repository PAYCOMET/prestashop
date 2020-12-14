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

    <input type="hidden" data-paycomet="jetID" value="{$jet_id|escape:'htmlall':'UTF-8':FALSE}">
    <ul>
        <li>
            <label for="MERCHANT_PAN">{l s='Credit Card Number' mod='paytpv'}:</label>
            <div id="paycomet-pan">
                <input required="required" paycomet-style="width: 78%; width: 78%; border: 1px solid #e5e5e5; height: 32px; display: inline-block; color: #333;font-size: 18px; padding: 0 5px;" paycomet-name="pan">
            </div>
        </li>
        <li class="vertical">
            <ul>
                <li>
                    <label for="expiry_date">{l s='Expiration' mod='paytpv'}</label>
                    <input maxlength="5" placeholder="{l s='MM/YY' mod='paytpv'}" id="expiry_date" pattern="{literal}[0-9]{2}/+[0-9]{2}{/literal}" type="text" onChange="buildED();" onfocus="buildED();">
                    <input type="hidden" data-paycomet="dateMonth" maxlength="2"  value="">
                    <input type="hidden" data-paycomet="dateYear" maxlength="2" value="">
                </li>
                <li>
                    <label for="MERCHANT_CVC2">CVV</label>
                    <div id="paycomet-cvc2">
                        <input paycomet-name="cvc2" paycomet-style="border: 1px solid #e5e5e5; height: 32px; display: inline-block; color: #333;font-size: 18px; padding: 0 5px;" class="form-control" required="required" type="text">
                    </div>
                </li>
                <small class="help">{l s='The CVV is a numerical code, usually 3 digits behind the card' mod='paytpv'}.</small>
            </ul>
        </li>
        <input type="hidden" class="paytpv_cardholdername" data-paycomet="cardHolderName" width="360" maxlength="50" value="NONAME"  placeholder="{l s='Cardholder name' mod='paytpv'}" onclick="this.value='';">        
    </ul>
    <div>
        {if (($newpage_payment==1 || $paytpv_integration==0) && $account==0)}
            <button type="submit" title="{l s='Make Payment' mod='paytpv'}" class="btn btn-primary button-small" id="btnforg">
                <span>{l s='Make Payment' mod='paytpv'}<i class="icon-chevron-right right"></i></span>
            </button>
        {elseif (($newpage_payment==0 && $paytpv_integration==1) && $account==0)}
            <button type="submit" id="submit_jet"></button>
        {elseif ($account==1)}
            <button type="submit" title="{l s='Save Card' mod='paytpv'}" class="btn btn-primary button-small" id="btnforg">
                <span>{l s='Save Card' mod='paytpv'}<i class="icon-chevron-right right"></i></span>
            </button>
        {/if}
    </div>
    <div id="paymentErrorMsg"></div>
    <script type="text/javascript" src="{$jet_paytpv|escape:'htmlall':'UTF-8':FALSE}?lang={$jet_lang|escape:'htmlall':'UTF-8':FALSE}"></script>

    <input type="hidden" name="paytpv_jetid_url" id="paytpv_jetid_url" value="{$paytpv_jetid_url|escape:'htmlall':'UTF-8':FALSE}">