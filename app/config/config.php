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
 * @copyright (c) 2015, TpyMaH (Vadims Bucinskis) <v.buchinsky@etwebsolutions.com>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

$sysMail['to'] = 'vadim.buchinsky@gmail.com';
$sysMail['from'] = 'test@test.com';
$sysMail['subject'] = 'CES notice';
$sysMail['error_to'] = 'vadim.buchinsky@gmail.com';
$sysMail['error_from'] = 'test@test.com';
$sysMail['error_subject'] = 'CES error notice';

$sysSms['enabled'] = false;
$sysSms['serverHost'] = 'test.com';
$sysSms['sendPage'] = 'http://tes.com/sms/send.php';
$sysSms['taskId'] = '10';
$sysSms['number'] = array('37122222222');

$sysExec['mysqldump']['path'] = '/opt/mysql/bin/mysqldump';
