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
    public $idterminal_ns;
    public $password;
    public $password_ns;
    public $jetid;
    public $jetid_ns;
    public $currency_iso_code;
    public $terminales;
    public $tdfirst;
    public $tdmin;



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
        $idterminal_ns,
        $password,
        $password_ns,
        $jetid,
        $jetid_ns,
        $currency_iso_code,
        $terminales,
        $tdfirst,
        $tdmin
    ) {
        $idterminal = ($idterminal=="")?"null":(int)$idterminal;
        $idterminal_ns = ($idterminal_ns=="")?"null":(int)$idterminal_ns;


        // Si solo opera por Seguro limpiamos los datos del No Seguro
        if ($terminales==0) {
            $idterminal_ns = "null";
            $password_ns = $jetid_ns = "";
            $tdfirst = 1;
        }
        // Si solo opera No Seguro limpiamos los datos del Seguro
        if ($terminales==1) {
            $idterminal = "null";
            $password = $jetid = "";
            $tdfirst = 0;
        }
        
        $id_shop = Context::getContext()->shop->id;
        $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'paytpv_terminal (id,id_shop,idterminal,idterminal_ns,password,
        password_ns,jetid,jetid_ns,currency_iso_code,terminales,tdfirst,tdmin) VALUES(' . (int)$id .
        ',' . (int)$id_shop . ',' . (int)$idterminal . ',' . (int)$idterminal_ns . ',"' . pSQL($password) .
        '","' . pSQL($password_ns) . '","' . pSQL($jetid) . '","' . pSQL($jetid_ns) . '","' .
        pSQL($currency_iso_code) . '",' . (int)$terminales . ',' . (int)$tdfirst . ',' . (float)$tdmin . ')';
        Db::getInstance()->Execute($sql);
    }

    public static function getTerminals()
    {
        $id_shop = Context::getContext()->shop->id;
        return Db::getInstance()->executeS("SELECT idterminal, idterminal_ns, password, password_ns, jetid, jetid_ns,
        currency_iso_code, terminales, tdfirst, tdmin FROM " . _DB_PREFIX_ . "paytpv_terminal where id_shop=" .
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

    public static function getTerminalByIdTerminal($idterminal)
    {
        $id_shop = Context::getContext()->shop->id;
        
        $idterminal = ($idterminal>0) ? $idterminal:0;
        $sql = 'select * from ' . _DB_PREFIX_ . 'paytpv_terminal where (idterminal=' . (int)$idterminal . ' or
         idterminal_ns=' . (int)$idterminal . ') and id_shop=' . (int)$id_shop;
        $result2 = Db::getInstance()->getRow($sql);

        $arrDatos = array();
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
