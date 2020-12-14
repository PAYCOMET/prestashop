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
{extends file='page.tpl'}
{capture name=path}{l s='Payment completed' mod='paytpv'}{/capture}


{block name='page_content'}
<h2>{l s='Payment completed' mod='paytpv'}</h2>
	<img src="{$this_path|escape:'htmlall':'UTF-8':FALSE}views/img/icon-valid.png"/>&nbsp;&nbsp;
	{l s='Thank you for trusting us. Your purchase has been formalized correctly and we will process your order soon.'  mod='paytpv'}

<ul class="footer_links">
	<li>
		<a href="{$link->getPageLink('my-account')|escape:'htmlall':'UTF-8':FALSE}" title="{l s='Go to your account'  mod='paytpv'}">
			<img src="{$this_path|escape:'htmlall':'UTF-8':FALSE}views/img/nav-user.gif" alt="{l s='Go to your account' mod='paytpv'}" class="icon" />&nbsp;{l s='Go to your account'  mod='paytpv'}
		</a>
	</li>
	<li>&nbsp;&nbsp;</li>
	<li>
		<a href="{$base_dir|escape:'htmlall':'UTF-8':FALSE}">
			<img src="{$this_path|escape:'htmlall':'UTF-8':FALSE}views/img/home.gif" alt="{l s='Home' mod='paytpv'}" class="icon" />&nbsp;{l s='Home'  mod='paytpv'}
		</a>
	</li>
</ul>
{/block}