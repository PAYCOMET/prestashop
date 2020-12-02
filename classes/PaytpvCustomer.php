<?php
/**
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

class PaytpvCustomer extends ObjectModel
{
    public $paytpv_iduser;
    public $paytpv_tokenuser;
    public $paytpv_cc;
    public $paytpv_brand;
    public $id_customer;
    public $date;
    public $card_desc;
    

    

    public static function getCardsCustomer($customer_id)
    {
        $res = array();
        $sql = 'SELECT paytpv_iduser,paytpv_tokenuser,paytpv_cc,paytpv_brand,card_desc FROM '._DB_PREFIX_
        .'paytpv_customer WHERE not paytpv_cc="" and id_customer = '.(int)$customer_id . ' order by date desc';

        $assoc = Db::getInstance()->executeS($sql);

        foreach ($assoc as $key => $row) {
            $res[$key]['IDUSER']= $row['paytpv_iduser'];
            $res[$key]['TOKEN_USER']= $row['paytpv_tokenuser'];
            $res[$key]['CC'] = $row['paytpv_cc'];
            $res[$key]['BRAND'] = $row['paytpv_brand'];
            $res[$key]['CARD_DESC'] = $row['card_desc'];
        }

        return  $res;
    }


    public static function getCardsToken($token)
    {
        $res = array();

        $sql = 'SELECT paytpv_iduser,paytpv_tokenuser,paytpv_cc FROM '._DB_PREFIX_.'paytpv_customer WHERE
        paytpv_tokenuser="'.pSQL($token).'"';

        $assoc = Db::getInstance()->executeS($sql);

        foreach ($assoc as $row) {
            $res['IDUSER']= $row['paytpv_iduser'];
            $res['TOKEN_USER']= $row['paytpv_tokenuser'];
            $res['CC'] = $row['paytpv_cc'];
        }

        return  $res;
    }

    public static function getCardTokenCustomer($token, $customer_id)
    {
        $res = array();

        $sql = 'SELECT paytpv_iduser,paytpv_tokenuser,paytpv_cc FROM '._DB_PREFIX_.'paytpv_customer WHERE
        paytpv_tokenuser="'.pSQL($token).'" and id_customer = '.(int)$customer_id;
       
        if ($row = Db::getInstance()->getRow($sql)) {
            $res['IDUSER']= $row['paytpv_iduser'];
            $res['TOKEN_USER']= $row['paytpv_tokenuser'];
            $res['CC'] = $row['paytpv_cc'];
        }

        return  $res;
    }

    public static function getCustomerIduser($paytpv_iduser)
    {
        $sql = 'SELECT * FROM '._DB_PREFIX_.'paytpv_customer WHERE paytpv_iduser="'.pSQL($paytpv_iduser).'"';
        $result = Db::getInstance()->getRow($sql);
        return $result;
    }

    

    public static function getCustomer()
    {
        $sql = 'select max(paytpv_iduser) as "max_iduser" from '. _DB_PREFIX_ .'paytpv_customer where
        paytpv_iduser<100000';
        $result = Db::getInstance()->getRow($sql);
        if (empty($result) === true) {
            $paytpv_iduser = 1;
        } else {
            $paytpv_iduser = $result["max_iduser"]+1;
        }
        return $paytpv_iduser;
    }

    public static function addCustomer($paytpv_iduser, $paytpv_tokenuser, $paytpv_cc, $paytpv_brand, $id_customer)
    {
        try {
            $sql = 'INSERT INTO '. _DB_PREFIX_ .'paytpv_customer (`paytpv_iduser`, `paytpv_tokenuser`, `paytpv_cc`,
            `paytpv_brand`,`id_customer`,`date`) VALUES('.(int)$paytpv_iduser.',"'.pSQL($paytpv_tokenuser).'","'
            .pSQL($paytpv_cc).'","'.pSQL($paytpv_brand).'",'.(int)$id_customer.',"'.pSQL(date('Y-m-d H:i:s')).'")';
            Db::getInstance()->Execute($sql);
        } catch (exception $e) {
        }
    }


    
    public static function removeCustomerIduser($customer_id, $paytpv_iduser)
    {
        $sql = 'DELETE FROM '. _DB_PREFIX_ .'paytpv_customer where id_customer = '.(int)$customer_id
        .' and `paytpv_iduser`="'.pSQL($paytpv_iduser).'"';
        Db::getInstance()->Execute($sql);
    }

    public static function saveCustomerCarDesc($customer_id, $paytpv_iduser, $card_desc)
    {
        $sql = 'UPDATE '. _DB_PREFIX_ .'paytpv_customer set card_desc = "' .pSQL($card_desc) .'" where id_customer = '
        .(int)$customer_id . ' and `paytpv_iduser`="'.(int)$paytpv_iduser.'"';
        Db::getInstance()->Execute($sql);
        return true;
    }
}
