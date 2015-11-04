#!/usr/bin/php
<?php
set_time_limit(9999);

error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 1);

define('MA_BACKUP_ROOT', dirname(__FILE__));
if ((include MA_BACKUP_ROOT . "/config.php")) {
    if (include(MA_BACKUP_ROOT . "/lib/MA.php")) {
        MA::Run();
    } else {
        die('Can\'t find function library.');
    }
} else {
    die('Can\'t find config file.');
}
?>
