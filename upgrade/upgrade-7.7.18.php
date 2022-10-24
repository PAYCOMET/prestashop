<?php
/**
*
*  @author     PAYCOMET <info@paycomet.com>
*  @copyright  2021 PAYCOMET S.L.U
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_7_7_18($object)
{
    try {
        Db::getInstance()->execute('ALTER TABLE `'._DB_PREFIX_.'paytpv_customer` 
        ADD COLUMN IF NOT EXISTS `paytpv_expirydate` VARCHAR(7) DEFAULT NULL
        ');
    } catch (exception $e) {
    }
    return ($object->registerHook('actionEmailAddAfterContent'));
}
