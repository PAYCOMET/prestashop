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

    <div id="storingStep" class="alert alert-info {if (sizeof($saved_card))>1}hidden{/if}" style="clear:left;">
        <strong>{l s='STREAMLINE YOUR FUTURE PURCHASES!' mod='paytpv'}</strong>
        <div class="checkbox"><input type="checkbox" name="paytpv_savecard" id="paytpv_savecard" onChange="saveOrderInfoJQ(0)" {if (!$remembercardunselected==1)} checked="true"{/if}>
            <label for="paytpv_savecard" style="display:inline!important"> {l s='Yes, remember my card accepting the ' mod='paytpv'}</label>
            <span class="js-terms-paytpv">
                <a id="open_conditions" href="#conditions">{l s='terms and conditions of the service' mod='paytpv'}</a>
            </span>
        </div>
    </div>
