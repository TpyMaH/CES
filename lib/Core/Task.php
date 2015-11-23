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
namespace ces\core;

use \ces\Ces as Ces;

/**
 * Class Task
 * @package ces\core
 */
class Task
{
    protected $taskList;
    protected $currentTask;
    protected $currentTaskObj;
    protected $finishedTasks;

    public static $config;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $sysTaskStack;
        $stackKey = isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : 'default';

        if (isset($sysTaskStack[$stackKey])) {
            self::$config = isset($sysTaskStack[$stackKey]['config']) ? $sysTaskStack[$stackKey]['config'] : array();
            if (isset($sysTaskStack[$stackKey]['config'])) {
                unset($sysTaskStack[$stackKey]['config']);
            }
            $this->taskList = $sysTaskStack[$stackKey];
        } else {
            Ces::log()->log("Can't find '" . $stackKey . "' task stack.", LOG_WARNING);
            exit();
        }
    }

    /**
     * @return bool|Command
     */
    public function next()
    {
        if (is_object($this->currentTaskObj)) {
            Ces::log()->Log("End '" . $this->currentTask['info']['name'] . "' task.");
        }

        if ($task = $this->getNext()) {
            $this->currentTask = $task;

            Ces::log()->log("Start '" . $this->currentTask['info']['name'] . "' task.");

            $this->currentTaskObj = new Command($task);
            return $this->currentTaskObj;
        } else {
            return false;
        }
    }

    /**
     * @return bool|mixed
     */
    protected function getNext()
    {
        if (is_array($this->taskList) && !empty($this->taskList)) {
            $task = array_shift($this->taskList);
            if ($this->schedulerCheck($task)) {
                return $task;
            } else {
                return $this->getNext();
            }
        }
        return false;
    }

    /**
     * @param $task
     * @return bool
     */
    protected function schedulerCheck($task)
    {
        if (isset($task['config']['scheduler']) && !empty($task['config']['scheduler'])) {
            $scheduler = $task['config']['scheduler'];
            if (isset($scheduler['monthly']) && !empty($scheduler['monthly'])) {
                if (!in_array(date('n'), (array)$scheduler['monthly'])) {
                    return false;
                }
            }
            if (isset($scheduler['weekly']) && !empty($scheduler['weekly'])) {
                if (!in_array(date('N'), (array)$scheduler['weekly'])) {
                    return false;
                }
            }
            if (isset($scheduler['daily']) && !empty($scheduler['daily'])) {
                if (!in_array(date('j'), (array)$scheduler['daily'])) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * @return bool
     */
    public function currentTaskData()
    {
        if (is_object($this->currentTaskObj)) {
            return $this->currentTask;
        } else {
            return false;
        }
    }

    /**
     * @return bool
     */
    public function currentTaskInfo()
    {
        if (is_object($this->currentTaskObj)) {
            return $this->currentTask['info'];
        } else {
            return false;
        }
    }
}

