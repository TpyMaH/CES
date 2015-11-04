<?php

/**
 * Class MA_CTask
 */
class MA_CTask
{
    protected $_taskList;
    protected $_currentTask;
    protected $_currnetTaskObj;
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
            MA::Log()->log("Can't find '" . $stacKey . "' task stac.", LOG_WARNING);
            exit();
        }
    }

    /**
     * @return bool|MA_CCommand
     */
    public function Next()
    {
        if (is_object($this->_currnetTaskObj)) {
            MA::Log()->Log("End '" . $this->_currentTask['info']['name'] . "' task.");
        }

        if ($task = $this->GetNext()) {
            $this->_currentTask = $task;

            MA::Log()->log("Start '" . $this->_currentTask['info']['name'] . "' task.");

            $this->_currnetTaskObj = new MA_CCommand($task);
            return $this->_currnetTaskObj;
        } else {
            return false;
        }
    }

    /**
     * @return bool|mixed
     */
    protected function GetNext()
    {
        if (is_array($this->_taskList) && !empty($this->_taskList)) {
            $task = array_shift($this->_taskList);
            if ($this->SchedulerCheck($task)) {
                return $task;
            } else {
                return $this->GetNext();
            }
        } else {
            return false;
        }
    }

    /**
     * @param $task
     * @return bool
     */
    protected function SchedulerCheck($task)
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
    public function CurrentTaskData()
    {
        if (is_object($this->_currnetTaskObj)) {
            return $this->_currentTask;
        } else {
            return false;
        }
    }

    /**
     * @return bool
     */
    public function CurrentTaskInfo()
    {
        if (is_object($this->_currnetTaskObj)) {
            return $this->_currentTask['info'];
        } else {
            return false;
        }
    }
}

