#!/usr/bin/php
<?php
/**
 * CES - Cron Exec System
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright (c) 2015, TpyMaH (Vadims Bucinskis) <vadim.buchinsky@gmail.com>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

set_time_limit(9999);

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 1);

define('BACKUP_ROOT', dirname(__FILE__));
if ((include BACKUP_ROOT . "/config.php")) {
    if (include(BACKUP_ROOT . "/lib/Ces.php")) {
        Ces::run();
    } else {
        die('Can\'t find function library.');
    }
} else {
    die('Can\'t find config file.');
}
?>
