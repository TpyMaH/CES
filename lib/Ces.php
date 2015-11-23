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
namespace ces;

use \ces\core\Log;
use \ces\core\Task;
use \ces\models\Notice;

/**
 * Class Ces
 */
class Ces
{
    /** @var  Notice */
    static protected $notice;
    /** @var  Task */
    static protected $task;
    /** @var  Log */
    static protected $log;

    static public $basePath;
    static public $version = "0.5.0.0";

    /**
     * @return Notice
     */
    public static function notice()
    {
        if (!is_object(self::$notice)) {
            self::$notice = new Notice();
        }
        return self::$notice;
    }

    /**
     * @return Task
     * @throws \Exception
     */
    public static function task()
    {
        if (is_object(self::$task)) {
            return self::$task;
        }
        throw new \Exception('task do not exist');
    }

    /**
     * @return Log
     */
    public static function log()
    {
        if (!is_object(self::$log)) {
            self::$log = new Log();
        }
        return self::$log;
    }

    /**
     * run
     */
    public static function run()
    {
        spl_autoload_register(['\\ces\\Ces', 'autoload']);
        self::log();
        $task = self::$task = new Task();

        $notice = self::notice();
        while ($taskCommands = $task->next()) {
            $notice->openTask($task->currentTaskData());

            while ($currentCommand = $taskCommands->next()) {
                $notice->startCommand($taskCommands->commandClass());
                if ($currentCommand->isHideStatus()) {
                    $notice->commandHide();
                }
                if ($currentCommand->run()) {
                    $notice->commandStatus(true);
                } else {
                    $notice->commandStatus(false);
                    $notice->endCommand();
                    break;
                }
                $notice->endCommand();
            }
        }
        $notice->finish();
    }

    private static $classMap = array(
        'CModel' => '/Core/CModel.php',
        'Task' => '/Core/Task.php',
        'CCommand' => '/Core/CCommand.php',
        'CLog' => '/Core/CLog.php',
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

        if (array_key_exists($className, self::$classMap)) {
            $filePath = self::$basePath . '/lib' . self::$classMap[$className];
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
