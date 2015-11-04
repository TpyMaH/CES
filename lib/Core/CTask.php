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
 * Class CTask
 */
class CTask
{
    protected $_taskList;
    protected $_currentTask;
    protected $_currentTaskObj;
    protected $_finishedTasks;

    public static $config;

    /**
     *
     */
    public function __construct()
    {
        global $sysTaskStac;
        $stacKey = isset($_SERVER['argv'][1]) ? $_SERVER['argv'][1] : 'default';

        if (isset($sysTaskStac[$stacKey])) {
            self::$config = isset($sysTaskStac[$stacKey]['config']) ? $sysTaskStac[$stacKey]['config'] : array();
            if (isset($sysTaskStac[$stacKey]['config'])) {
                unset($sysTaskStac[$stacKey]['config']);
            }
            $this->_taskList = $sysTaskStac[$stacKey];
        } else {
            Ces::log()->log("Can't find '" . $stacKey . "' task stac.", LOG_WARNING);
            exit();
        }
    }

    /**
     * @return bool|CCommand
     */
    public function next()
    {
        if (is_object($this->_currentTaskObj)) {
            Ces::log()->Log("End '" . $this->_currentTask['info']['name'] . "' task.");
        }

        if ($task = $this->_getNext()) {
            $this->_currentTask = $task;

            Ces::log()->log("Start '" . $this->_currentTask['info']['name'] . "' task.");

            $this->_currentTaskObj = new CCommand($task);
            return $this->_currentTaskObj;
        } else {
            return false;
        }
    }

    /**
     * @return bool|mixed
     */
    protected function _getNext()
    {
        if (is_array($this->_taskList) && !empty($this->_taskList)) {
            $task = array_shift($this->_taskList);
            if ($this->_schedulerCheck($task)) {
                return $task;
            } else {
                return $this->_getNext();
            }
        }
        return false;
    }

    /**
     * @param $task
     * @return bool
     */
    protected function _schedulerCheck($task)
    {
        if (isset($task['config']['scheduler']) && !empty($task['config']['scheduler'])) {
            $scheduler = $task['config']['scheduler'];
            if (isset($scheduler['monthly']) && !empty($scheduler['monthly'])) {
                $scheduler['monthly'] = is_array($scheduler['monthly']) ? $scheduler['monthly'] : array($scheduler['monthly']);
                if (!in_array(date('n'), $scheduler['monthly'])) {
                    return FALSE;
                }
            }
            if (isset($scheduler['weekly']) && !empty($scheduler['weekly'])) {
                $scheduler['weekly'] = is_array($scheduler['weekly']) ? $scheduler['weekly'] : array($scheduler['weekly']);
                if (!in_array(date('N'), $scheduler['weekly'])) {
                    return FALSE;
                }
            }
            if (isset($scheduler['daily']) && !empty($scheduler['daily'])) {
                $scheduler['daily'] = is_array($scheduler['daily']) ? $scheduler['daily'] : array($scheduler['daily']);
                if (!in_array(date('j'), $scheduler['daily'])) {
                    return FALSE;
                }
            }
        }
        return TRUE;
    }

    /**
     * @return bool
     */
    public function currentTaskData()
    {
        if (is_object($this->_currentTaskObj)) {
            return $this->_currentTask;
        } else {
            return false;
        }
    }

    /**
     * @return bool
     */
    public function currentTaskInfo()
    {
        if (is_object($this->_currentTaskObj)) {
            return $this->_currentTask['info'];
        } else {
            return false;
        }
    }
}

