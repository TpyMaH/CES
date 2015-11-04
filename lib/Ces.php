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

define("LIB_ROOT", dirname(__FILE__));

include LIB_ROOT . "/Core/CModel.php";
include LIB_ROOT . "/Core/CTask.php";
include LIB_ROOT . "/Core/CCommand.php";
include LIB_ROOT . "/Core/CLog.php";
include LIB_ROOT . "/Model/Mail.php";
include LIB_ROOT . "/Model/Notice.php";
include LIB_ROOT . "/Model/Sms.php";
include LIB_ROOT . "/Model/Exec.php";
include LIB_ROOT . "/Model/Exec/tar.php";
include LIB_ROOT . "/Model/Exec/cp.php";
include LIB_ROOT . "/Model/Exec/mv.php";
include LIB_ROOT . "/Model/Exec/rm.php";
include LIB_ROOT . "/Model/Exec/killall.php";
include LIB_ROOT . "/Model/Exec/mysqldump.php";
include LIB_ROOT . "/Model/Exec/exec.php";
include LIB_ROOT . "/Model/Exec/bz2.php";
include LIB_ROOT . "/Model/Exec/df.php";
include LIB_ROOT . "/Model/Exec/raid.php";
include LIB_ROOT . "/Model/Exec/ps.php";
include LIB_ROOT . "/Model/Exec/httpstat.php";
include LIB_ROOT . "/Model/Exec/du.php";
include LIB_ROOT . "/Model/Exec/ping.php";
include LIB_ROOT . "/Model/Exec/timekill.php";

/**
 * Class MA
 */
class Ces
{
    /** @var  Model_Notice */
    static protected $_notice;
    /** @var  CTask */
    static protected $_task;
    /** @var  CLog */
    static protected $_log;

    static public $version = "0.5.0.0";

    /**
     * @return Model_Notice
     */
    static public function notice()
    {
        if (!is_object(self::$_notice)) {
            self::$_notice = new Model_Notice();
        }
        return self::$_notice;
    }

    /**
     * @return CTask
     * @throws Exception
     */
    static public function task()
    {
        if (is_object(self::$_task)) {
            return self::$_task;
        }
        throw new Exception('task do not exist');
    }

    /**
     * @return CLog
     */
    static public function log()
    {
        if (!is_object(self::$_log)) {
            self::$_log = new CLog();
        }
        return self::$_log;
    }

    /**
     * run
     */
    static public function run()
    {
        self::log();
        $task = self::$_task = new CTask();

        $notice = self::notice();
        while ($taskCommands = $task->next()) {
            $notice->openTask($task->currentTaskData());

            while ($currentCommand = $taskCommands->next()) {
                $notice->startCommand($taskCommands->commandClass());
                if ($currentCommand->getHideStatus()) {
                    $notice->commandHide();
                }
                if ($currentCommand->run()) {
                    $notice->commandStatus(TRUE);
                } else {
                    $notice->commandStatus(FALSE);
                    $notice->endCommand();
                    break;
                }
                $notice->endCommand();
            }
        }
        $notice->finish();
    }

    private $classMap = array(
        'CModel'   => '/Core/CModel.php',
        'CTask'    => '/Core/CTask.php',
        'CCommand' => '/Core/CCommand.php',
        'CLog'     => '/Core/CLog.php',
    );

    /**
     * @param $className
     */
    public static function autoload($className)
    {
//        if (!self::$basePath) {
//            self::$basePath = realpath(__DIR__ . '/../../');
//        }
//        $path = explode('\\', $className);
//        $class = array_pop($path);
//
//        $filePath = self::$basePath . '/' . implode('/', $path) . '/' . $class . '.php';
//
//        if (file_exists($filePath)) {
//            require_once($filePath);
//        }
    }
}

