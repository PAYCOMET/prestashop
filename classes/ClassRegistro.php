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

if (!class_exists('ClassRegistro')) {

    class ClassRegistro
    {

        private static function executeS($sql)
        {
            $sql = str_replace('`table_registro`', '`' . _DB_PREFIX_ . 'paytpvregistro`', $sql);
            return Db::getInstance()->executeS($sql);
        }

        private static function execute($sql)
        {
            $sql = str_replace('`table_registro`', '`' . _DB_PREFIX_ . 'paytpvregistro`', $sql);
            return Db::getInstance()->execute($sql);
        }

        private static function getRow($sql)
        {
            $sql = str_replace('`table_registro`', '`' . _DB_PREFIX_ . 'paytpvregistro`', $sql);
            return Db::getInstance()->getRow($sql);
        }

        public static function select()
        {
            return (ClassRegistro::executeS('SELECT w.`id_registro`, w.`id_customer`, w.`id_cart`, w.`amount`,
            w.`date_add`, w.`error_code`, c.`lastname` AS customer_lastname, c.`firstname` AS customer_firstname
            FROM `table_registro` w LEFT JOIN `' . _DB_PREFIX_ . 'customer` c ON w.`id_customer` = c.`id_customer`
            ORDER BY w.`date_add` DESC'));
        }
        // Introduce cesta en el registro @boolean

        public static function add($id_customer, $id_cart, $amount, $error_code)
        {
            if (!Validate::isUnsignedId($id_customer) or !Validate::isUnsignedId($id_cart)) {
                die(Tools::displayError());
            }

            return (ClassRegistro::execute('

            INSERT INTO `table_registro` (`id_customer`, `id_cart`, `amount`, `date_add`, `error_code`) VALUES(

            ' . (int) $id_customer . ',

            ' . (int) $id_cart . ',

            ' . (float) $amount . ',

            \'' . pSQL(date('Y-m-d H:i:s')) . '\',

            \'' . pSQL($error_code) . '\')'));
        }
        // Elimina cesta del registro @boolean

        public static function removeByCartID($id_cart)
        {
            if (!Validate::isUnsignedId($id_cart)) {
                die(Tools::displayError());
            }

            $result = ClassRegistro::getRow('
                    SELECT `id_cart`
                    FROM `table_registro`
                    WHERE `id_cart` = ' . (int) $id_cart);
            
            if (empty($result) === true or
                $result === false or
                !sizeof($result) or
                $result['id_cart'] != $id_cart
            ) {
                return (false);
            }
            $result = ClassRegistro::execute('
                DELETE FROM `table_registro`
                WHERE `id_cart` = ' . (int) $id_cart);
        }
        // Elimina cesta del registro @boolean

        public static function remove($id_registro)
        {
            if (!Validate::isUnsignedId($id_registro)) {
                die(Tools::displayError());
            }

            $result = ClassRegistro::getRow('
                SELECT `id_registro`
                FROM `table_registro`
                WHERE `id_registro` = ' . (int) $id_registro);
            if (empty($result) === true or
                $result === false or
                !sizeof($result) or
                $result['id_registro'] != $id_registro
            ) {
                return (false);
            }

            $result = ClassRegistro::execute('
                DELETE FROM `table_registro`
                WHERE `id_registro` = ' . (int) $id_registro);
        }
        // Actualiza cesta del registro @boolean

        public static function update($id_cart, $error_code)
        {
            if (!Validate::isUnsignedId($id_cart)) {
                die(Tools::displayError());
            }

            return (ClassRegistro::execute('
                UPDATE `table_registro` SET
                `error_code` = \'' . pSQL($error_code) . '\'
                WHERE `id_cart` = ' . (int) $id_cart));
        }
    }
}
