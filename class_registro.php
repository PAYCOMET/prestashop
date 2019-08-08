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


if(!class_exists ('class_registro')){

class class_registro

{

    static private function ExecuteS($sql){

        $sql = str_replace('`table_registro`', '`'._DB_PREFIX_.basename(dirname(__FILE__)).'registro`',$sql);

        return Db::getInstance()->ExecuteS($sql);

    }
    static private function Execute($sql){

        $sql = str_replace('`table_registro`', '`'._DB_PREFIX_.basename(dirname(__FILE__)).'registro`',$sql);

        return Db::getInstance()->Execute($sql);

    }
    static private function getRow($sql){

        $sql = str_replace('`table_registro`', '`'._DB_PREFIX_.basename(dirname(__FILE__)).'registro`',$sql);

        return Db::getInstance()->getRow($sql);

    }

    static public function select()

    {

        return (class_registro::ExecuteS('

        SELECT w.`id_registro`, w.`id_customer`, w.`id_cart`, w.`amount`, w.`date_add`, w.`error_code`, c.`lastname` AS customer_lastname, c.`firstname` AS customer_firstname

          FROM `table_registro` w

        LEFT JOIN `'._DB_PREFIX_.'customer` c ON w.`id_customer` = c.`id_customer`

          ORDER BY w.`date_add` DESC'));

    }
// Introduce cesta en el registro @boolean

    static public function add($id_customer, $id_cart, $amount, $error_code)

    {

        if (!Validate::isUnsignedId($id_customer) OR

            !Validate::isUnsignedId($id_cart))

            die (Tools::displayError());
            return (class_registro::Execute('

            INSERT INTO `table_registro` (`id_customer`, `id_cart`, `amount`, `date_add`, `error_code`) VALUES(

            '.intval($id_customer).',

            '.intval($id_cart).',

            '.floatval($amount).',

            \''.pSQL(date('Y-m-d H:i:s')).'\',

            \''.pSQL($error_code).'\')'));

    }
// Elimina cesta del registro @boolean

    static public function removeByCartID($id_cart)

    {

        if (!Validate::isUnsignedId($id_cart))

            die (Tools::displayError());
        $result = class_registro::getRow('

        SELECT `id_cart`

          FROM `table_registro`

        WHERE `id_cart` = '.intval($id_cart));
        if (empty($result) === true OR

            $result === false OR

            !sizeof($result) OR

            $result['id_cart'] != $id_cart)

            return (false);

        $result = class_registro::Execute('

        DELETE FROM `table_registro`

        WHERE `id_cart` = '.intval($id_cart));

    }
    // Elimina cesta del registro @boolean

    static public function remove($id_registro)

    {

        if (!Validate::isUnsignedId($id_registro))

            die (Tools::displayError());
        $result = class_registro::getRow('

        SELECT `id_registro`

          FROM `table_registro`

        WHERE `id_registro` = '.intval($id_registro));
        if (empty($result) === true OR

            $result === false OR

            !sizeof($result) OR

            $result['id_registro'] != $id_registro)

            return (false);

        $result = class_registro::Execute('

        DELETE FROM `table_registro`

        WHERE `id_registro` = '.intval($id_registro));

    }
// Actualiza cesta del registro @boolean

    static public function update($id_cart, $error_code)

    {

        if (!Validate::isUnsignedId($id_cart))

            die (Tools::displayError());
        return (class_registro::Execute('

        UPDATE `table_registro` SET

        `error_code` = \''.pSQL($error_code).'\'

        WHERE `id_cart` = '.intval($id_cart)));

    }

}

}

?>