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
		<div class="panel">

			<div class="panel-heading"><img src="{$base_url}modules/{$module_name}/logo.gif" alt="" /> {l s='PAYCOMET Refund' mod='paytpv'}</div>
			<form method="post"  class="form-inline" action="{$smarty.server.REQUEST_URI|escape:htmlall}">
				<input type="hidden" name="id_order" value="{$params.id_order|intval}" />
				<p><b>{l s='Information:' mod='paytpv'}</b> {l s='Payment accepted' mod='paytpv'}</p>
				<ul>
					<li>
					{l s='"Standard refund" or "Return Products": performs a partial Refund in the Customer\'s credit card unless you select "Create a voucher"' mod='paytpv'}</li>
					<li>
					{l s='"Partial refund": does not perform the refund of the amount in the Customer\'s credit card.' mod='paytpv'}
					</li>
				</ul>
				<p><b>{l s='Total amount' mod='paytpv'}:</b> <span class="badge badge-success">{$total_amount} {$sign}</span></p>
				<p><b>{l s='Amount returned' mod='paytpv'}:</b> <span class="badge badge-important">{$amount_returned} {$sign}</span></p>
				<p>
					<ul>
					{foreach from=$arrRefunds item=refund}

					    <li>
					    	{$refund["date"]|date_format:"%d-%m-%Y %H:%M"} - {$refund["amount"]} {$sign}
					    </li>

					{/foreach}
					</ul>
				</p>
				<p><b>{l s='Outstanding amount' mod='paytpv'}:</b> <span class="badge badge-info">{$amount}</span></p>
				{if $amount>0}
				<p class="center">
					<button type="submit" class="btn btn-default" name="submitPayTpvRefund" onclick="if (!confirm('{l s='Are you sure?' mod='paytpv'}'))return false;">
						<i class="icon-undo"></i>
						{l s='Total Refund of the Payment' mod='paytpv'}  [{$amount}]
					</button>
					{l s='Change order status to Refunded' mod='paytpv'}
				</p>
				<p class="input-group">
					<input type="text" class="form-control" name="paytpPartialRefundAmount" size="10" value="">
					<span class="input-group-btn">
						<button type="submit" class="btn btn-default" name="submitPayTpvPartialRefund" onclick="if (!confirm('{l s='Are you sure?' mod='paytpv'}'))return false;">
							<i class="icon-undo"></i>
							{l s='Partial Refund of the Payment' mod='paytpv'} [{l s='Máx' mod='paytpv'}.: {$amount}]
						</button>
					</span>
				</p>
				<div class="row">
					<span class="label label-warning">{$error_msg}</span>
				</div>
				{/if}
			</form>	
		</div>
	</div>
</div>
{else}
<br />
<fieldset {if isset($ps_version) && ($ps_version < '1.5')}style="width: 400px"{/if}>
	<legend><img src="{$base_url}modules/{$module_name}/logo.gif" alt="" />{l s='PAYCOMET Refund' mod='paytpv'}</legend>
	<form method="post"  class="form-inline" action="{$smarty.server.REQUEST_URI|escape:htmlall}">
	<p><b>{l s='Information:' mod='paytpv'}</b> {l s='Payment accepted' mod='paytpv'}</p>
	<ul>
		<li>
		{l s='"Standard refund" or "Return Products": performs a partial Refund in the Customer\'s credit card unless you select "Create a voucher".' mod='paytpv'}</li>
		<li>
		{l s='"Partial refund": does not perform the refund of the amount in the Customer\'s credit card.' mod='paytpv'}
		</li>
	</ul>
	<p><b>{l s='Total amount' mod='paytpv'}:</b> <span class="badge badge-success">{$total_amount} {$sign}</span></p>
	<p><b>{l s='Amount returned' mod='paytpv'}:</b> <span class="badge badge-important">{$amount_returned} {$sign}</span></p>
	<p>
		<ul>
		{foreach from=$arrRefunds item=refund}

		    <li>
		    	{$refund["date"]|date_format:"%d-%m-%Y %H:%M"} - {$refund["amount"]} {$sign}
		    </li>

		{/foreach}
		</ul>
	</p>
	<p><b>{l s='Outstanding amount:' mod='paytpv'}</b> {$amount}</p>
	

	{if $amount>0}
	<p>
		<button type="submit" class="btn btn-default" name="submitPayTpvRefund" onclick="if (!confirm('{l s='Are you sure?' mod='paytpv'}'))return false;">
			<i class="icon-undo"></i>
			{l s='Total Refund of the Payment' mod='paytpv'}  [{$amount}]
		</button>
		{l s='Change order status to Refunded' mod='paytpv'}
	</p>
	<p>
		<input type="text" class="form-control" name="paytpPartialRefundAmount" size="10" value="">
		<span class="input-group-btn">
			<button type="submit" class="btn btn-default" name="submitPayTpvPartialRefund" onclick="if (!confirm('{l s='Are you sure?' mod='paytpv'}'))return false;">
				<i class="icon-undo"></i>
				{l s='Partial Refund of the Payment' mod='paytpv'} [{l s='Máx' mod='paytpv'}.: {$amount}]
			</button>
		</span>
	</p>
	<div class="row">
		<span class="label label-warning">{$error_msg}</span>
	</div>
	{/if}
	</form>

</fieldset>

{/if}
