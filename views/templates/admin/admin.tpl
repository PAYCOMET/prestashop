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

<script type="text/javascript">
    var confirm_delete = "{l s='Are you sure?' mod='paytpv'}"
</script>


<div class="alert alert-info">
    <img src="../modules/paytpv/logo.png" width="46" style="float:left; margin-right:15px;">
    <p><strong>{l s='This module allows you to accept secure payments by PAYCOMET.' mod='paytpv'}</strong></p>
    <p>{l s='If the customer chooses this payment method, they will be able to make payments automatically' mod='paytpv'}</p>
</div>    

<div>
    <p><H1>{l s='PRERREQUISTES' mod='paytpv'}</H1></p>
        <ul>
            <li>{l s='PAYCOMET Account.' mod='paytpv'} {l s='You can request a Test account at: ' mod='paytpv'} <a href="mailto:info@paycomet.com">info@paycomet.com</a></li>
            <li>{l s='The store must be installed on-line, NOT in Local in order to use this module' mod='paytpv'}</li>
            <li>{l s='The PAYCOMET server must be accessible. (check that there are no problems when there is a firewall)' mod='paytpv'}</li>
        </ul>
    </p>
</div>

<div>
    <p class="important">{l s='IMPORTANT' mod='paytpv'}</p>
    <p><strong>{l s='Finally you need to configure in your account' mod='paytpv'} <a class='link' target="_blank" href="https://dashboard.paycomet.com/cp_control"> PAYCOMET </a>{l s='the following URLs for the payment module to work properly' mod='paytpv'}:</strong>
    </p>
    <ul class="paytpv">                
        <li><strong>{l s='Type of Notification (IMPORTANT)' mod='paytpv'}:</strong> {l s='Notification via URL or Notification via URL and email' mod='paytpv'}
            <ul class="paytpv">
                <li><strong>{l s='NOTIFICATION URL' mod='paytpv'}:</strong> {$NOTIFICACION|escape:'htmlall':'UTF-8':FALSE}</li>
            </ul>
        </li>       
    </ul>

</div>

<div>
    <p class="important">{l s='USER DOCUMENTATION' mod='paytpv'}</p>
    <p><strong>{l s='Link to documentation by clicking the following link' mod='paytpv'} <a class='link' target="_blank"  href="https://docs.paycomet.com/es/modulos-de-pago/prestashop">{l s='USER DOCUMENTATION'  mod='paytpv'}</a></strong>
</div>
    
<div id="paytpvconfigarea">
    {$errorMessage|escape:'quotes'}

    {$configform} {* no escaping needed, comes from PrestaShop Form Helper!!! *}

    <div class="text-right">
        <hr />        
        <button type="submit" value="1" name="btnSubmit" class="btn btn-default">
            <i class="process-icon-save"></i> {l s='Save' mod='paytpv'}
        </button>
    </div>
</div>



    