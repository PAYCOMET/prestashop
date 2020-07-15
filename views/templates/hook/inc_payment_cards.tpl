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
       
    <div id="cards_paytpv">
        <div class="form-group" style="width:100%">
            <label for="card" style="text-align:left">{l s='Card' mod='paytpv'}:</label>
            <select name="card" id="card" onChange="checkCard()" class="form-control" style="width:60%">
                {section name=card loop=$saved_card }
                    {if ($saved_card[card].url=="0")}
                        {if ($newpage_payment==2)}
                            <option value='{$paytpv_iframe}'>{l s='NEW CARD' mod='paytpv'}</option>
                        {else}
                            <option value='0'>{l s='NEW CARD' mod='paytpv'}</option>
                        {/if}
                    {else}
                        <option value='{$saved_card[card].url}'>{$saved_card[card].CC} ({$saved_card[card].BRAND}){if ($saved_card[card].CARD_DESC!="")} - {$saved_card[card].CARD_DESC}{/if}</option>
                    {/if}
                {/section}
            </select>
        </div>
    </div>
                