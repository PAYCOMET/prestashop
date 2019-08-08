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
*  @author     PAYCOMET <info@paycomet.com>
*  @copyright  2019 PAYTPV ON LINE ENTIDAD DE PAGO S.L
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

class Paytpv_Suscription extends ObjectModel
{
	public $id_suscription;
	public $id_customer;
	public $id_order;
	public $paytpv_iduser;
	public $paytpv_tokenuser;
	public $periodicity;
	public $cycles;
	public $price;
	public $date;
	public $status;
	

	
	public static function save_Suscription($id_customer,$id_order,$paytpv_iduser,$paytpv_tokenuser,$periodicity,$cycles,$importe){
		// Datos usuario
		$sql = 'select * from ' . _DB_PREFIX_ .'paytpv_suscription where id_customer = ' . pSQL($id_customer) .' AND id_order="'.pSQL($id_order).'"';	
		$result = Db::getInstance()->getRow($sql);

		// Si no existe la suscripcion la creamos
		if (empty($result) === true){
			$sql = 'INSERT INTO '. _DB_PREFIX_ .'paytpv_suscription(`id_customer`, `id_order`, `paytpv_iduser`,`paytpv_tokenuser`,`periodicity`,`cycles`,`price`,`date`) VALUES('.pSQL($id_customer).','.pSQL($id_order).','.pSQL($paytpv_iduser).',"'.pSQL($paytpv_tokenuser).'",'.pSQL($periodicity).','.pSQL($cycles).','.pSQL($importe).',"'.pSQL(date('Y-m-d H:i:s')).'")';
			Db::getInstance()->Execute($sql);
		}
	}

	public static function get_Suscription_Id($id_customer,$id_suscription){
		// Datos usuario
		$sql = 'select * from ' . _DB_PREFIX_ .'paytpv_suscription where id_customer = '.(int)$id_customer . ' and id_suscription = '.pSQL($id_suscription);
		$result = Db::getInstance()->getRow($sql);
		return $result;
	}

	public static function get_Suscription_Order($id_customer,$id_order){
		// Datos usuario
		$sql = 'select * from ' . _DB_PREFIX_ .'paytpv_suscription where id_customer = ' . pSQL($id_customer) .' AND id_order="'. pSQL($id_order).'"';	
		$result = Db::getInstance()->getRow($sql);
		return $result;
	}


	public static function get_Suscription_Order_Payments($id_order){
		// Check if is a subscription order
		$sql = 'select ps.*,count(po.id_order) as "pagos",1 as "suscription" FROM '._DB_PREFIX_.'paytpv_suscription ps
LEFT OUTER JOIN '._DB_PREFIX_.'paytpv_order po on ps.id_suscription = po.id_suscription and po.id_order!='. pSQL($id_order) . '
where ps.id_order = '. pSQL($id_order). ' group by ps.id_suscription order by ps.date desc';
		$result = Db::getInstance()->getRow($sql);

		if (empty($result)){
			// Check if is a suscription payment
			$sql = 'select ps.*,count(po.id_order) as "pagos", 0 as "suscription" FROM '._DB_PREFIX_.'paytpv_suscription ps
LEFT OUTER JOIN '._DB_PREFIX_.'paytpv_order po on ps.id_suscription = po.id_suscription 
where po.id_order = '. pSQL($id_order). ' group by ps.id_suscription order by ps.date desc';
			$result = Db::getInstance()->getRow($sql);

		}
		return $result;
	}


	/* Obtener las suscripciones del usuario */
	public static function get_Suscriptions_Customer($iso_code,$customer_id){

		$paytpv = new Paytpv();
		$res = array();
		$sql = 'select ps.*,count(po.id_order) as "pagos" FROM '._DB_PREFIX_.'paytpv_suscription ps LEFT OUTER JOIN '._DB_PREFIX_.'paytpv_order po on ps.id_suscription = po.id_suscription and ps.id_order!=po.id_order where ps.id_customer = '.(int)$customer_id . ' group by ps.id_suscription order by ps.date desc';
		
		$assoc = Db::getInstance()->executeS($sql);

		foreach ($assoc as $key=>$row) {
			$res[$key]['ID_SUSCRIPTION']= $row['id_suscription'];
			$res[$key]['SUSCRIPTION_PAY'] = Paytpv_Order::get_OrdersSuscription($iso_code,$row['id_suscription']);
			$order = new Order($row['id_order']);

			
			$id_currency = $order->id_currency;
			$currency = new Currency(intval($id_currency));

			$res[$key]['ORDER_REFERENCE']= $order->reference;
			$res[$key]['ID_ORDER']= $row['id_order'];
			$res[$key]['PERIODICITY'] = $row['periodicity'];
			$res[$key]['CYCLES'] = ($row['cycles']!=0)?$row['cycles']:$paytpv->l('Permanent');
			$res[$key]['PRICE'] = number_format($row['price'], 2, '.', '')  . " " . $currency->sign;		
			$res[$key]['DATE'] = $row['date'];
			$res[$key]['DATE_YYYYMMDD'] = ($iso_code=="es")?date("d-m-Y",strtotime($row['date'])):date("Y-m-d",strtotime($row['date']));

			$num_pagos = $row['pagos'];
			
			$status = $row['status'];
			if ($row['status']==1)
				$status = $row['status'];  // CANCELADA
			else if ($num_pagos==$row['cycles'] && $row['cycles']>0)	
				$status = 2; // FINALIZADO
							


			$res[$key]['STATUS'] = $status;
		}
		
		return  $res;
	}


	public static function remove_Suscription($customer_id,$id_suscription){
		$sql = 'DELETE FROM '. _DB_PREFIX_ .'paytpv_suscription where id_customer = '.(int)$customer_id . ' and id_suscription = '.pSQL($id_suscription);
		Db::getInstance()->Execute($sql);
	}

	public static function cancel_Suscription($customer_id,$id_suscription){
		$sql = 'UPDATE '. _DB_PREFIX_ .'paytpv_suscription set status=1 where id_customer = '.(int)$customer_id . ' and id_suscription = '.pSQL($id_suscription);
		Db::getInstance()->Execute($sql);
	}

	
}
