<?php
define("MA_LIB_ROOT", dirname(__FILE__));

include MA_LIB_ROOT . "/Core/CModel.php";
include MA_LIB_ROOT . "/Core/CTask.php";
include MA_LIB_ROOT . "/Core/CCommand.php";
include MA_LIB_ROOT . "/Core/CLog.php";
include MA_LIB_ROOT . "/Model/Mail.php";
include MA_LIB_ROOT . "/Model/Notice.php";
include MA_LIB_ROOT . "/Model/Sms.php";
include MA_LIB_ROOT . "/Model/Exec.php";
include MA_LIB_ROOT . "/Model/Exec/tar.php";
include MA_LIB_ROOT . "/Model/Exec/cp.php";
include MA_LIB_ROOT . "/Model/Exec/mv.php";
include MA_LIB_ROOT . "/Model/Exec/rm.php";
include MA_LIB_ROOT . "/Model/Exec/killall.php";
include MA_LIB_ROOT . "/Model/Exec/mysqldump.php";
include MA_LIB_ROOT . "/Model/Exec/exec.php";
include MA_LIB_ROOT . "/Model/Exec/bz2.php";
include MA_LIB_ROOT . "/Model/Exec/df.php";
include MA_LIB_ROOT . "/Model/Exec/raid.php";
include MA_LIB_ROOT . "/Model/Exec/ps.php";
include MA_LIB_ROOT . "/Model/Exec/httpstat.php";
include MA_LIB_ROOT . "/Model/Exec/du.php";
include MA_LIB_ROOT . "/Model/Exec/ping.php";
include MA_LIB_ROOT . "/Model/Exec/timekill.php";

/**
 * Class MA
 */
class MA
{
    static protected $_notice;
    static protected $_task;
    static protected $_log;

    static public $version = "0.5.0.0";

    /**
     * @return MA_Model_Notice
     */
    static public function Notice()
    {
        if (!is_object(self::$_notice)) {
            self::$_notice = new MA_Model_Notice();
        }
        return self::$_notice;
    }

    /**
     * @return bool
     */
    static public function Task()
    {
        if (is_object(self::$_task)) {
            return self::$_task;
        }
        return false;
    }

    /**
     * @return MA_CLog
     */
    static public function Log()
    {
        if (!is_object(self::$_log)) {
            self::$_log = new MA_CLog();
        }
        return self::$_log;
    }

    /**
     * Run
     */
    static public function Run()
    {
        self::$_log = new MA_Clog();
        self::$_task = new MA_CTask();
        while ($taskCommands = self::$_task->Next()) {
            self::notice()->OpenTask(self::$_task->CurrentTaskData());

            while ($currentCommand = $taskCommands->Next()) {
                self::notice()->StartCommand($taskCommands->CommandClass());
                if ($currentCommand->getHideStatus()) {
                    self::notice()->CommandHide();
                }
                if ($currentCommand->Run()) {
                    self::notice()->CommandStatus(TRUE);
                } else {
                    self::notice()->CommandStatus(FALSE);
                    self::notice()->EndCommand();
                    break;
                }
                self::notice()->EndCommand();
            }
        }
        self::notice()->Finish();
    }

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

