<?php
/**
 *  @author     PAYCOMET <info@paycomet.com>
 *  @copyright  2021 PAYCOMET S.L.U
 *  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_7_7_15($object)
{
    return $object->registerHook('displayOrderDetail');
}
