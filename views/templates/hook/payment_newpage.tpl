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


<div class="row">
    <div class="col-xs-12 col-md-6">
        <p class="payment_module">      
            <a class="bankwire" href="{$link->getModuleLink('paytpv', 'payment')|escape:'htmlall':'UTF-8'}" title="{l s='Pay with Card' mod='paytpv'}"rel="nofollow">
                <img src="{$this_path}views/img/paytpv_logo.svg" width="135">
                {l s='Pay with Card' mod='paytpv'}
            </a>
        </p>
    </div>
</div>