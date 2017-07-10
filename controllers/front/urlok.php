<?php

/*
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
*  @author     Jose Ramon Garcia <jrgarcia@paytpv.com>
*  @copyright  2015 PAYTPV ON LINE S.L.
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/
/**
 * @since 1.5.0
 */

class PaytpvUrlokModuleFrontController extends ModuleFrontController
{
	public $display_column_left = false;

	public $ssl = true;
	/**
	 * @see FrontController::initContent()
	 */

	public function initContent()
	{

		parent::initContent();

		$id_cart = (int)(Tools::getValue('id_cart', 0));
		$id_order = Order::getOrderByCartId(intval($id_cart));
		$key = Tools::getValue('key');

		// Vienen los parametros por GET
		if ($id_cart>0 && $id_cart>0){
			$values = array(
				'id_cart' => $id_cart,
				'id_module' => (int)$this->module->id,
				'id_order' => $id_order,
				'key' => $key
			);
			Tools::redirect(Context::getContext()->link->getPageLink('order-confirmation',$this->ssl,null,$values));
		// No vienen los parametros
		}else{
			
			$id_customer = (Context::getContext()->customer->id>0)?Context::getContext()->customer->id:0;

			$result = Paytpv_Order::get_Order_Customer($id_customer);
			if (empty($result) === false){
				$id_order = $result["id_order"];
				$fecha_order = strtotime($result['date']);
				$fecha_actual = strtotime($result['fechaactual']);

				// Si hay order y se ha realizado hace menos de un minuto
				if ($id_order>0 && $fecha_order > strtotime('-1 minute',$fecha_actual)){
					$order = new Order((int)($id_order));
					$id_cart = $order->id_cart;

					$values = array(
						'id_cart' => $id_cart,
						'id_module' => (int)$this->module->id,
						'id_order' => $id_order,
						'key' => Context::getContext()->customer->secure_key
					);

					Tools::redirect(Context::getContext()->link->getPageLink('order-confirmation',$this->ssl,null,$values));
				}
			}
			$this->context->smarty->assign('base_dir', __PS_BASE_URI__);

			$this->context->smarty->assign(array(
				'this_path' => Tools::getShopDomainSsl(true, true).__PS_BASE_URI__.'modules/'.$this->module->name.'/',
				'base_dir' =>  __PS_BASE_URI__
			));

			$this->setTemplate('module:paytpv/views/templates/front/payment_ok.tpl');
			
		}
	}

}

