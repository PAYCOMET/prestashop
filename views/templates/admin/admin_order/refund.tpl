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

{if $smarty.const._PS_VERSION_ >= 1.6}
<div class="row">
	<div class="col-lg-12">
		<div class="panel card">
			<div class="panel-heading card-header"><img src="{$base_url|escape:'htmlall':'UTF-8':FALSE}modules/{$module_name|escape:'htmlall':'UTF-8':FALSE}/logo.png" width="20" alt="" /> {l s='PAYCOMET Refund' mod='paytpv'}</div>
			<div class="card-body">
				<form method="post" class="card-body" action="{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8':FALSE}">
					<input type="hidden" name="id_order" value="{$params.id_order|intval}" />
					<p class=><b>{l s='Information:' mod='paytpv'}</b> {l s='Payment accepted' mod='paytpv'} [{$ref_paycomet|escape:'htmlall':'UTF-8':FALSE}]</p>
					<p>{l s='"Standard refund" or "Return Products": performs a partial Refund in the Customer\'s credit card unless you select "Create a voucher"' mod='paytpv'}</p>
					<p>{l s='"Partial refund": does not perform the refund of the amount in the Customer\'s credit card.' mod='paytpv'}</p>
					<p><b>{l s='Total amount' mod='paytpv'}:</b> <span class="badge badge-success">{$total_amount|escape:'htmlall':'UTF-8':FALSE} {$sign|escape:'htmlall':'UTF-8':FALSE}</span></p>
					<p><b>{l s='Amount returned' mod='paytpv'}:</b> <span class="badge badge-important">{$amount_returned|escape:'htmlall':'UTF-8':FALSE} {$sign|escape:'htmlall':'UTF-8':FALSE}</span></p>				
					{foreach from=$arrRefunds item=refund}
						<p>
							-  {$refund["date"]|date_format:"%d-%m-%Y %H:%M"} - {$refund["amount"]|escape:'htmlall':'UTF-8':FALSE} {$sign|escape:'htmlall':'UTF-8':FALSE}
						</p>
					{/foreach}
					<p><b>{l s='Outstanding amount' mod='paytpv'}:</b> <span class="badge badge-info">{$amount|escape:'htmlall':'UTF-8':FALSE}</span></p>
					{if $amount>0}
						<p class="center">
							<button type="submit" class="btn btn-default btn-primary" name="submitPayTpvRefund" onclick="if (!confirm('{l s='Are you sure?' mod='paytpv'}'))return false;this.classList.add('d-none');">
								<i class="icon-undo"></i>
								{l s='Total Refund of the Payment' mod='paytpv'}  [{$amount|escape:'htmlall':'UTF-8':FALSE}]
							</button>
							{l s='Change order status to Refunded' mod='paytpv'}
						</p>
						<p class="input-group">
							<input type="text" class="col-sm-1 form-control" name="paytpPartialRefundAmount" size="10" value="">
							<span class="input-group-btn">
								<button type="submit" class="btn btn-default btn-primary ml-3" name="submitPayTpvPartialRefund" onclick="if (!confirm('{l s='Are you sure?' mod='paytpv'}'))return false;this.classList.add('d-none');">
									<i class="icon-undo"></i>
									{l s='Partial Refund of the Payment' mod='paytpv'} [{l s='Máx' mod='paytpv'}.: {$amount|escape:'htmlall':'UTF-8':FALSE}]
								</button>
							</span>
						</p>
						<div class="row">
							<span class="label label-warning">{$error_msg|escape:'htmlall':'UTF-8':FALSE}</span>
						</div>
					{/if}
				</form>
			</div>
		</div>
	</div>
</div>
{else}
<br />
<fieldset {if isset($ps_version) && ($ps_version < '1.5')}style="width: 400px"{/if}>
	<legend><img src="{$base_url|escape:'htmlall':'UTF-8':FALSE}modules/{$module_name|escape:'htmlall':'UTF-8':FALSE}/logo.png" width="20" alt="" />{l s='PAYCOMET Refund' mod='paytpv'}</legend>
	<form method="post"  class="form-inline" action="{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8':FALSE}">
	<p><b>{l s='Information:' mod='paytpv'}</b> {l s='Payment accepted' mod='paytpv'}</p>
	<ul>
		<li>
		{l s='"Standard refund" or "Return Products": performs a partial Refund in the Customer\'s credit card unless you select "Create a voucher".' mod='paytpv'}</li>
		<li>
		{l s='"Partial refund": does not perform the refund of the amount in the Customer\'s credit card.' mod='paytpv'}
		</li>
	</ul>
	<p><b>{l s='Total amount' mod='paytpv'}:</b> <span class="badge badge-success">{$total_amount|escape:'htmlall':'UTF-8':FALSE} {$sign|escape:'htmlall':'UTF-8':FALSE}</span></p>
	<p><b>{l s='Amount returned' mod='paytpv'}:</b> <span class="badge badge-important">{$amount_returned|escape:'htmlall':'UTF-8':FALSE} {$sign|escape:'htmlall':'UTF-8':FALSE}</span></p>
	<p>
		<ul>
		{foreach from=$arrRefunds item=refund}

		    <li>
		    	{$refund["date"]|date_format:"%d-%m-%Y %H:%M"|escape:'htmlall':'UTF-8':FALSE} - {$refund["amount"]|escape:'htmlall':'UTF-8':FALSE} {$sign|escape:'htmlall':'UTF-8':FALSE}
		    </li>

		{/foreach}
		</ul>
	</p>
	<p><b>{l s='Outstanding amount:' mod='paytpv'}</b> {$amount|escape:'htmlall':'UTF-8':FALSE}</p>

	{if $amount>0}
	<p>
		<button type="submit" class="btn btn-default" name="submitPayTpvRefund" onclick="if (!confirm('{l s='Are you sure?' mod='paytpv'}'))return false;this.classList.add('d-none');">
			<i class="icon-undo"></i>
			{l s='Total Refund of the Payment' mod='paytpv'}  [{$amount|escape:'htmlall':'UTF-8':FALSE}]
		</button>
		{l s='Change order status to Refunded' mod='paytpv'}
	</p>
	<p>
		<input type="text" class="form-control" name="paytpPartialRefundAmount" size="10" value="">
		<span class="input-group-btn">
			<button type="submit" class="btn btn-default" name="submitPayTpvPartialRefund" onclick="if (!confirm('{l s='Are you sure?' mod='paytpv'}'))return false;this.classList.add('d-none');">
				<i class="icon-undo"></i>
				{l s='Partial Refund of the Payment' mod='paytpv'} [{l s='Máx' mod='paytpv'}.: {$amount|escape:'htmlall':'UTF-8':FALSE}]
			</button>
		</span>
	</p>
	<div class="row">
		<span class="label label-warning">{$error_msg|escape:'htmlall':'UTF-8':FALSE}</span>
	</div>
	{/if}
	</form>

</fieldset>

{/if}
