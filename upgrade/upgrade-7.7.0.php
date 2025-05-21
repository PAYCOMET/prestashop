<?php
/**
 *  @author     PAYCOMET <info@paycomet.com>
 *  @copyright  2021 PAYCOMET S.L.U
 *  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_7_7_0()
{
    try {
        Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . 'paytpv_terminal` 
	    DROP COLUMN `idterminal_ns`,
        DROP COLUMN `password_ns`,
        DROP COLUMN `jetid_ns`,
        DROP COLUMN `terminales`,
        DROP COLUMN `tdfirst`,
        DROP COLUMN `tdmin`
        ');
    } catch (Exception $e) {
    }

    return true;
}
