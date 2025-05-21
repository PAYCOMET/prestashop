<?php
/**
 *  @author     PAYCOMET <info@paycomet.com>
 *  @copyright  2021 PAYCOMET S.L.U
 *  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_7_7_11()
{
    try {
        Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'paytpv_terminal` 
	    ADD COLUMN `dcc` INT(2) UNSIGNED NOT NULL DEFAULT 0
        ');
    } catch (Exception $e) {
    }

    return true;
}
