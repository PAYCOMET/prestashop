<?php
/**
 * 2007-2019 PrestaShop
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

class PaytpvTerminal extends ObjectModel
{
    public $id;
    public $id_shop;
    public $idterminal;
    public $password;
    public $jetid;
    public $currency_iso_code;


    public static function existTerminal()
    {
        $id_shop = Context::getContext()->shop->id;
        $sql = 'select * from ' . _DB_PREFIX_ . 'paytpv_terminal where id_shop=' . (int)$id_shop . ' order by id';
        $result = Db::getInstance()->getRow($sql);
        if (empty($result) === true) {
            return false;
        }

        return true;
    }


    public static function removeTerminals()
    {
        $id_shop = Context::getContext()->shop->id;
        Db::getInstance()->Execute('DELETE FROM ' . _DB_PREFIX_ . 'paytpv_terminal where id_shop=' . (int)$id_shop);
    }

    public static function addTerminal(
        $id,
        $idterminal,
        $password,
        $jetid,
        $currency_iso_code
    ) {
        $idterminal = ($idterminal=="")?"null":(int)$idterminal;

        $id_shop = Context::getContext()->shop->id;
        $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'paytpv_terminal (id,id_shop,idterminal,password,
        jetid,currency_iso_code) VALUES(' . (int)$id .
        ',' . (int)$id_shop . ',' . (int)$idterminal . ',"' . pSQL($password) .
        '","' . pSQL($jetid) . '","' .
        pSQL($currency_iso_code) . '")';
        Db::getInstance()->Execute($sql);
    }

    public static function getTerminals()
    {
        $id_shop = Context::getContext()->shop->id;
        return Db::getInstance()->executeS("SELECT idterminal, password, jetid, currency_iso_code
         FROM " . _DB_PREFIX_ . "paytpv_terminal where id_shop=" .
        (int)$id_shop);
    }

    public static function getTerminalCurrency($currency_iso_code, $id_shop = 0)
    {
        if ($id_shop == 0) {
            $id_shop = Context::getContext()->shop->id;
        }
        $sql = 'select * from ' . _DB_PREFIX_ . 'paytpv_terminal where currency_iso_code="' .
        pSQL($currency_iso_code) . '" and id_shop=' . (int)$id_shop;
        $result = Db::getInstance()->getRow($sql);
        return $result;
    }

    public static function getFirstTerminal()
    {
        $id_shop = Context::getContext()->shop->id;
        $sql = 'select * from ' . _DB_PREFIX_ . 'paytpv_terminal where id_shop=' . (int)$id_shop . ' order by id';
        $result = Db::getInstance()->getRow($sql);
        return $result;
    }

    public static function getTerminalById()
    {
        $id_shop = Context::getContext()->shop->id;
        $sql = 'select * from ' . _DB_PREFIX_ . 'paytpv_terminal where id_shop=' . (int)$id_shop . ' order by id';
        $result = Db::getInstance()->getRow($sql);
        return $result;
    }


    public static function getTerminalByCurrency($currency_iso_code, $id_shop = 0)
    {
        if ($id_shop == 0) {
            $id_shop = Context::getContext()->shop->id;
        }
        $result2 = self::getTerminalCurrency($currency_iso_code, $id_shop);

        // Select first termnial defined
        if (empty($result2) === true) {
            // Search for terminal in merchant default currency
            $id_currency = (int) Configuration::get('PS_CURRENCY_DEFAULT');
            $currency = new Currency($id_currency);

            $result2 = self::getTerminalCurrency($currency->iso_code, $id_shop);

            // If not exists terminal in default currency. Select first terminal defined
            if (empty($result2) === true) {
                $result2 = self::getFirstTerminal();
            }
        }
        $arrDatos = array();
        $arrDatos["idterminal"] = $result2["idterminal"];
        $arrDatos["password"] = $result2["password"];
        $arrDatos["jetid"] = $result2["jetid"];

        return $arrDatos;
    }

    public static function getTerminalByIdTerminal($idterminal)
    {
        $id_shop = Context::getContext()->shop->id;
        $idterminal = ($idterminal>0) ? $idterminal:0;
        $sql = 'select * from ' . _DB_PREFIX_ . 'paytpv_terminal where idterminal=' . (int)$idterminal . ' and
        id_shop=' . (int)$id_shop;
        $result2 = Db::getInstance()->getRow($sql);

        $arrDatos = array();
        $arrDatos["idterminal"] = $result2["idterminal"];
        $arrDatos["password"] = $result2["password"];
        $arrDatos["jetid"] = $result2["jetid"];

        return $arrDatos;
    }
}
