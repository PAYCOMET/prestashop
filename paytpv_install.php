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

if (!defined('_PS_VERSION_')) {
    exit;
}

class PayTpvInstall
{
    /**
     * Create PayTpv tables
     */
    public function createTables()
    {
        if (!Db::getInstance()->Execute('
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'paytpvregistro` (
              `id_registro` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `id_customer` int(10) unsigned NOT NULL,
              `id_cart` int(10) unsigned NOT NULL,
              `amount` decimal(13,6) unsigned NOT NULL,
              `date_add` datetime NOT NULL,
              `error_code` varchar(64) character set utf8 NOT NULL,
              PRIMARY KEY  (`id_registro`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 AUTO_INCREMENT=1')) {
            return false;
        }

        if (!Db::getInstance()->Execute('
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'paytpv_order_info` (
                `id_customer` int(10) unsigned NOT NULL,
                `id_cart` int(10) unsigned NOT NULL,
                `paytpv_iduser` int(11) UNSIGNED NOT NULL DEFAULT 0,
                `paytpvagree` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
                `suscription` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0,
                `periodicity` INT(3) NOT NULL,
                `cycles` INT(2) NOT NULL,
                `date` DATETIME NOT NULL,
                PRIMARY KEY (`id_customer`, `id_cart`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8')) {
            return false;
        }

        if (!Db::getInstance()->Execute('
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'paytpv_customer` (
                `paytpv_iduser` int(11) UNSIGNED NOT NULL,
                `paytpv_tokenuser` VARCHAR(64) NOT NULL,
                `paytpv_cc` VARCHAR(32) NOT NULL,
                `paytpv_brand` VARCHAR(32) NULL,
                `id_customer` int(10) unsigned NOT NULL,
                `date` DATETIME NOT NULL,
                `card_desc` VARCHAR(32) NULL DEFAULT NULL,
                PRIMARY KEY (`paytpv_iduser`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8')) {
            return false;
        }

        if (!Db::getInstance()->Execute('
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'paytpv_suscription` (
                `id_suscription` int(10) unsigned NOT NULL AUTO_INCREMENT,
                `id_customer` int(10) unsigned NOT NULL,
                `id_order` INT(10) UNSIGNED NOT NULL,
                `paytpv_iduser` INT(11) UNSIGNED NOT NULL,
                `paytpv_tokenuser` VARCHAR(64) NOT NULL,
                `periodicity` INT(3) NOT NULL,
                `cycles` INT(2) NOT NULL,
                `price` DECIMAL(20,6) NOT NULL DEFAULT 0,
                `date` DATETIME NOT NULL,
                `status` SMALLINT(1) NOT NULL DEFAULT 0,
                PRIMARY KEY (`id_suscription`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8')) {
            return false;
        }

        if (!Db::getInstance()->Execute('
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'paytpv_order` (
                `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `paytpv_iduser` INT(11) UNSIGNED NOT NULL,
                `paytpv_tokenuser` VARCHAR(64) NOT NULL,
                `id_suscription` INT(10) UNSIGNED NOT NULL,
                `id_customer` INT(10) UNSIGNED NOT NULL,
                `id_order` INT(10) UNSIGNED NOT NULL,
                `price` DECIMAL(20,6) NOT NULL DEFAULT 0,
                `date` DATETIME NOT NULL,
                `payment_status` VARCHAR(255) DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8')) {
            return false;
        }

        if (!Db::getInstance()->Execute('
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'paytpv_terminal` (
                `id` INT(2) UNSIGNED NOT NULL,
                `id_shop` INT(2) UNSIGNED NOT NULL DEFAULT 1,
                `idterminal` INT(6) UNSIGNED NULL,
                `idterminal_ns` INT(6) UNSIGNED NULL,
                `password` VARCHAR(30) NULL,
                `password_ns` VARCHAR(30) NULL,
                `jetid` VARCHAR(32),
                `jetid_ns` VARCHAR(32),
                `currency_iso_code` VARCHAR(3) NOT NULL,
                `terminales` SMALLINT(1) NOT NULL DEFAULT 0,
                `tdfirst` SMALLINT(1) NOT NULL DEFAULT 1,
                `tdmin` DECIMAL(17,2),
                PRIMARY KEY (`id`,`id_shop`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8')) {
            return false;
        }

        if (!Db::getInstance()->Execute('
            CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'paytpv_refund` (
                `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                `id_order` INT(10) UNSIGNED NOT NULL,
                `amount` decimal(13,2) unsigned NOT NULL,
                `type` SMALLINT(1) NOT NULL DEFAULT 0,
                `date` DATETIME NOT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8')) {
            return false;
        }

        return true;
    }

    /**
     * Set configuration table
     */
    public function updateConfiguration()
    {
    }

    /**
     * Delete PayTpv configuration
     */
    public function deleteConfiguration()
    {
        // Valores a quitar si desinstalamos el módulo
        Configuration::deleteByName('PAYTPV_CLIENTCODE');
        Configuration::deleteByName('PAYTPV_3DFIRST');
        Configuration::deleteByName('PAYTPV_3DMIN');
        Configuration::deleteByName('PAYTPV_TERMINALES');
        Configuration::deleteByName('PAYTPV_IFRAME');
        Configuration::deleteByName('PAYTPV_TERM');
        Configuration::deleteByName('PAYTPV_REG_ESTADO');
        Configuration::deleteByName('PAYTPV_PASS');
        Configuration::deleteByName('PAYTPV_SUSCRIPTIONS');
        Configuration::deleteByName('PAYTPV_NEWPAGEPAYMENT');
    }
}
