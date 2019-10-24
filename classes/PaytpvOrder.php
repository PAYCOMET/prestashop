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

class PaytpvOrder extends ObjectModel
{
    public $id;
    public $paytpv_iduser;
    public $paytpv_tokenuser;
    public $id_suscription;
    public $id_customer;
    public $id_order;
    public $price;
    public $date;
    public $payment_status;




    public static function isFirstPurchaseToken($id_customer, $paytpv_iduser)
    {
        $sql = 'select * from ' . _DB_PREFIX_ . 'paytpv_order where id_customer=' . (int) $id_customer . ' and
                paytpv_iduser=' . pSQL($paytpv_iduser);
        $result = Db::getInstance()->getRow($sql);
        if (empty($result) === true) {
            return true;
        }
        return false;
    }

    public static function isFirstPurchaseCustomer($id_customer)
    {
        $sql = 'select * from ' . _DB_PREFIX_ . 'orders where id_customer=' . (int) $id_customer;

        $result = Db::getInstance()->getRow($sql);
        if (empty($result) === true) {
            return true;
        }
        return false;
    }

    /**
     * Obtiene transacciones realizadas
     * @param int $id_customer codigo cliente
     * @param int $valid completadas o no
     * @param int $interval intervalo
     * @return string $intervalType tipo de intervalo (DAY,MONTH)
     **/
    public static function numPurchaseCustomer($id_customer, $valid = 1, $interval = 1, $intervalType = "DAY")
    {
        $sql = 'SELECT COUNT(`id_order`) AS nb_orders
        FROM `' . _DB_PREFIX_ . 'orders` o
        WHERE o.`id_customer` = ' . (int) $id_customer;

        if ($valid == 1) {
            $sql .= ' AND o.valid = 1';
        }

        $sql .= ' AND o.date_add > DATE(NOW() - INTERVAL ' . $interval . ' ' . $intervalType . ')';

        $result = Db::getInstance()->getRow($sql);

        return $result['nb_orders'];
    }


    /**
     * Obtiene Fecha del primer envio a una direccion
     * @param int $id_customer codigo cliente
     * @param int $id_address_delivery direccion de envio
     * @param int $interval intervalo
     * @return string $intervalType tipo de intervalo (DAY,MONTH)
     **/

    public static function firstAddressDelivery($id_customer, $id_address_delivery)
    {
        $sql = 'SELECT min(`date_add`) AS min_date
        FROM `' . _DB_PREFIX_ . 'orders` o
        WHERE o.`id_customer` = ' . (int) $id_customer;
        $sql .= ' AND o.valid = 1';
        $sql .= ' AND o.id_address_delivery = ' . (int) $id_address_delivery;

        $result = Db::getInstance()->getRow($sql);

        return $result['min_date'];
    }

    public static function addOrder(
        $paytpv_iduser,
        $paytpv_tokenuser,
        $id_suscription,
        $id_customer,
        $id_order,
        $price
    ) {

        $sql = 'INSERT INTO ' . _DB_PREFIX_ . 'paytpv_order (`paytpv_iduser`,`paytpv_tokenuser`,`id_suscription`,
        `id_customer`, `id_order`,`price`,`date`) VALUES(' . (int)$paytpv_iduser . ',"' . pSQL($paytpv_tokenuser) .
        '",' . (int)$id_suscription . ',' . (int)$id_customer . ',' . (int)$id_order . ',"' . (float)$price . '","' .
        pSQL(date('Y-m-d H:i:s')) . '")';
        Db::getInstance()->Execute($sql);
    }


    /* Obtener los pagos de una suscripcion */
    public static function getOrdersSuscription($iso_code, $id_suscription)
    {

        $sql = 'select * from ' . _DB_PREFIX_ . 'paytpv_order where id_suscription = ' . (int)$id_suscription . '
        LIMIT 1,100';


        $assoc = Db::getInstance()->executeS($sql);
        $res = array();
        foreach ($assoc as $key => $row) {
            $res[$key]["ID"] = $row["id"];
            $order = new Order($row['id_order']);

            $currency = new Currency((int) $order->id_currency);

            $res[$key]['ID_ORDER'] = $row['id_order'];
            $res[$key]['ORDER_REFERENCE'] = $order->reference;
            $res[$key]["PRICE"] = number_format($row['price'], 2, '.', '')  . " " . $currency->sign;
            $res[$key]['DATE'] = $row['date'];
            $res[$key]['DATE_YYYYMMDD'] = ($iso_code == "es") ?
                                            date("d-m-Y", strtotime($row['date'])) :
                                            date("Y-m-d", strtotime($row['date']));
        }


        return $res;
    }


    public static function getOrder($id_order)
    {
        $sql = 'select * from ' . _DB_PREFIX_ . 'paytpv_order where id_order="' . (int)$id_order . '"';
        $result = Db::getInstance()->getRow($sql);
        return $result;
    }

    public static function getOrderCustomer($id_customer)
    {
        $sql = 'SELECT now() as "fechaactual",paytpv_order.* FROM `' . _DB_PREFIX_ . 'paytpv_order` as paytpv_order
        WHERE `id_customer` = ' . (int)$id_customer . ' ORDER BY `date` DESC';
        $result = Db::getInstance()->getRow($sql);
        return $result;
    }

    public static function setOrderRefunded($id_order)
    {
        return Db::getInstance()->Execute('UPDATE `' . _DB_PREFIX_ . 'paytpv_order` SET `payment_status` = \'Refunded\'
        WHERE `id_order` = ' . (int)$id_order);
    }
}
