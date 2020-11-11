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
   
                
    <div id="tipo-pago">
        <div>
            <span class="checked"><input type="checkbox" name="paytpv_suscripcion" id="paytpv_suscripcion" onclick="check_suscription();" value="1"></span>
            <label for="paytpv_suscripcion" style="display:inline!important">{l s='Would you like to subscribe to this order?' mod='paytpv'}</label>
        </div>

        <div id="div_periodicity" class="suscription_period" style="display:none">
            <div class="nota">
                {l s='The first purchase will be made when placing the order and the following as defined as the frequency of the subscription' mod='paytpv'}.            
            </div>

            <div class="form-inline">
                <div class="form-group">    
                    <label for="paytpv_periodicity" class="control-label">{l s='Frequency:' mod='paytpv'} </label>
                    <select name="paytpv_periodicity" id="paytpv_periodicity" onChange="saveOrderInfoJQ(1)" class="form-control" style="min-width:200px;">
                        <option value="7">{l s='7 days (weekly)' mod='paytpv'}</option>
                        <option value="30" selected>{l s='30 days (monthly)' mod='paytpv'}</option>
                        <option value="90">{l s='90 days (quarterly)' mod='paytpv'}</option>
                        <option value="180">{l s='180 days (biannual)' mod='paytpv'}</option>
                        <option value="365">{l s='365 days (annual)' mod='paytpv'}</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="paytpv_cycles">{l s='Payments:' mod='paytpv'}</label>
                    <select name="paytpv_cycles" id="paytpv_cycles" class="form-control" onChange="saveOrderInfoJQ(1)" style="min-width:200px;">
                        <option value="0" selected>{l s='Permanent' mod='paytpv'}</option>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                        <option value="6">6</option>
                        <option value="7">7</option>
                        <option value="8">8</option>
                        <option value="9">9</option>
                        <option value="10">10</option>
                        <option value="11">11</option>
                        <option value="12">12</option>
                    </select>
                </div>
            </div>                    
        </div>
    </div>            
           
            
            