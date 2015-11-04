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

/**
 * Class Ces
 */
class Ces
{
    /** @var  Model_Notice */
    static protected $_notice;
    /** @var  CTask */
    static protected $_task;
    /** @var  CLog */
    static protected $_log;

    static public $basePath;
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

    private static $classMap = array(
        'CModel'   => '/Core/CModel.php',
        'CTask'    => '/Core/CTask.php',
        'CCommand' => '/Core/CCommand.php',
        'CLog'     => '/Core/CLog.php',
    );

    /**
     * @param $className
     *
     * @todo need refactoring
     */
    public static function autoload($className)
    {
        if (!self::$basePath) {
            self::$basePath = realpath(__DIR__ . "/../");
        }

        if (array_key_exists($className, self::$classMap)){
            $filePath = self::$basePath . '/lib' .self::$classMap[$className];
            require_once($filePath);
            return;
        }

        $path = explode('_', $className);
        if (strpos($className, '\\') !== false) {
            $path = explode('\\', $className);
        }

        $class = array_pop($path);


        $filePath = self::$basePath . '/lib/' . implode('/', $path) . '/' . $class . '.php';

        if (file_exists($filePath)) {
            require_once($filePath);
        }
    }
}

spl_autoload_register(['Ces', 'autoload']);

