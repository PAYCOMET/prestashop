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

class Paytpv_Terminal extends ObjectModel
{
	public $id;
	public $id_shop;
	public $idterminal;
	public $idterminal_ns;
	public $password;
	public $password_ns;
	public $jetid;
	public $jetid_ns;
	public $currency_iso_code;
	public $terminales;
	public $tdfirst;
	public $tdmin;
	


	public static function exist_Terminal(){
		$id_shop = Context::getContext()->shop->id;
		$sql = 'select * from ' . _DB_PREFIX_ .'paytpv_terminal where id_shop=' . $id_shop . ' order by id';
		$result = Db::getInstance()->getRow($sql);
		if (empty($result) === true)
			return false;

		return true;
	}


	public static function remove_Terminals(){
		$id_shop = Context::getContext()->shop->id;
		Db::getInstance()->Execute('DELETE FROM '. _DB_PREFIX_ .'paytpv_terminal where id_shop=' . $id_shop);
	}

	public static function add_Terminal($id,$idterminal,$idterminal_ns,$password,$password_ns,$jetid,$jetid_ns,$currency_iso_code,$terminales,$tdfirst,$tdmin){
		$idterminal = ($idterminal=="")?"null":$idterminal;
		$idterminal_ns = ($idterminal_ns=="")?"null":$idterminal_ns;
		$id_shop = Context::getContext()->shop->id;
		$sql = 'INSERT INTO '. _DB_PREFIX_ .'paytpv_terminal (id,id_shop,idterminal,idterminal_ns,password,password_ns,jetid,jetid_ns,currency_iso_code,terminales,tdfirst,tdmin) VALUES('.$id.','.$id_shop.','.$idterminal.','.$idterminal_ns.',"'.$password.'","'.$password_ns.'","'.$jetid.'","'.$jetid_ns.'","'.$currency_iso_code.'",'.$terminales.','.$tdfirst.','.$tdmin.')';
		Db::getInstance()->Execute($sql);
	}

	public static function get_Terminals(){
		$id_shop = Context::getContext()->shop->id;
		return Db::getInstance()->executeS("SELECT idterminal, idterminal_ns, password, password_ns, jetid, jetid_ns, currency_iso_code, terminales, tdfirst, tdmin FROM " . _DB_PREFIX_ . "paytpv_terminal where id_shop=" . $id_shop);
	}

	public static function get_Terminal_Currency($currency_iso_code,$id_shop=0){
		if ($id_shop == 0) {
			$id_shop = Context::getContext()->shop->id;
		}
		$sql = 'select * from ' . _DB_PREFIX_ .'paytpv_terminal where currency_iso_code="'.$currency_iso_code. '" and id_shop='.$id_shop;
		$result = Db::getInstance()->getRow($sql);
		return $result;
	}

	public static function get_First_Terminal(){
		$id_shop = Context::getContext()->shop->id;
		$sql = 'select * from ' . _DB_PREFIX_ .'paytpv_terminal where id_shop=' . $id_shop . ' order by id';
		$result = Db::getInstance()->getRow($sql);
		return $result;
	}

	public static function get_TerminalById(){
		$id_shop = Context::getContext()->shop->id;
		$sql = 'select * from ' . _DB_PREFIX_ .'paytpv_terminal where id_shop=' . $id_shop . ' order by id';
		$result = Db::getInstance()->getRow($sql);
		return $result;
	}


	public static function getTerminalByCurrency($currency_iso_code,$id_shop=0){
		if ($id_shop==0) {
			$id_shop = Context::getContext()->shop->id;
		}
		$result2 = self::get_Terminal_Currency($currency_iso_code,$id_shop);

		// Select first termnial defined
		if (empty($result2) === true){
			// Search for terminal in merchant default currency
			$id_currency = intval(Configuration::get('PS_CURRENCY_DEFAULT'));
			$currency = new Currency($id_currency);

			$result2 = self::get_Terminal_Currency($currency->iso_code,$id_shop);

			// If not exists terminal in default currency. Select first terminal defined
			if (empty($result2) === true){
				$result2 = self::get_First_Terminal();
			}
		}

		$arrDatos["idterminal"] = $result2["idterminal"];
		$arrDatos["idterminal_ns"] = $result2["idterminal_ns"];
		$arrDatos["password"] = $result2["password"];
		$arrDatos["password_ns"] = $result2["password_ns"];
		$arrDatos["jetid"] = $result2["jetid"];
		$arrDatos["jetid_ns"] = $result2["jetid_ns"];
		$arrDatos["terminales"] = $result2["terminales"];
		$arrDatos["tdfirst"] = $result2["tdfirst"];
		$arrDatos["tdmin"] = $result2["tdmin"];

		return $arrDatos;
	}

	public static function getTerminalByIdTerminal($idterminal){
		$id_shop = Context::getContext()->shop->id;
		$sql = 'select * from ' . _DB_PREFIX_ .'paytpv_terminal where (idterminal='.$idterminal . ' or idterminal_ns='. $idterminal .') and id_shop=' . $id_shop;
		$result2 = Db::getInstance()->getRow($sql);

		$arrDatos["idterminal"] = $result2["idterminal"];
		$arrDatos["idterminal_ns"] = $result2["idterminal_ns"];
		$arrDatos["password"] = $result2["password"];
		$arrDatos["password_ns"] = $result2["password_ns"];
		$arrDatos["jetid"] = $result2["jetid"];
		$arrDatos["jetid_ns"] = $result2["jetid_ns"];
		$arrDatos["terminales"] = $result2["terminales"];
		$arrDatos["tdfirst"] = $result2["tdfirst"];
		$arrDatos["tdmin"] = $result2["tdmin"];

		return $arrDatos;
	}

}
