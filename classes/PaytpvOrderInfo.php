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

class PaytpvOrderInfo extends ObjectModel
{
    public $id_customer;
    public $id_cart;
    public $paytpv_iduser;
    public $paytpvagree;
    public $suscription;
    public $periodicity;
    public $cycles;
    public $date;


    public static function saveOrderInfo(
        $id_customer,
        $id_cart,
        $paytpvagree,
        $suscription,
        $peridicity,
        $cycles,
        $paytpv_iduser = 0
    ) {
        // Eliminamos la orden si existe.
        $sql = 'DELETE FROM ' . _DB_PREFIX_ . 'paytpv_order_info where id_customer = ' . (int)$id_customer . ' and
        id_cart= "' . (int)$id_cart . '"';
        Db::getInstance()->Execute($sql);

        // Insertamos los datos de la orden
        $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'paytpv_order_info (`id_customer`,`id_cart`,`paytpvagree`,`suscription`,
        `periodicity`,`cycles`,`date`,`paytpv_iduser`) VALUES(' . (int)$id_customer . ',"' . (int)$id_cart . '",' .
        (int)$paytpvagree . ',' . (int)$suscription . ',' . (int)$peridicity . ',' . (int)$cycles . ',"' .
        pSQL(date('Y-m-d H:i:s')) . '",' . (int)$paytpv_iduser . ')';
        Db::getInstance()->Execute($sql);

        return true;
    }


    public static function getOrderInfo($id_customer, $id_cart, $defaultsavecard)
    {
        $sql = 'select * from ' . _DB_PREFIX_ . 'paytpv_order_info where id_customer = ' . (int)$id_customer . ' and
        id_cart="' . (int)$id_cart . '"';
        $result = Db::getInstance()->getRow($sql);

        // Si no hay datos los almacenamos segun la configuración: Disable Offer Card != SI y
        // Remember Card (Unselected) != SI Por defecto se guarda la tarjeta si está por defecto seleccionado.
        if (empty($result) === true) {
            self::saveOrderInfo($id_customer, $id_cart, $defaultsavecard, 0, 0, 0, 0, 0);
            $result = self::getOrderInfo($id_customer, $id_cart, $defaultsavecard);
        }

        return $result;
    }
}
