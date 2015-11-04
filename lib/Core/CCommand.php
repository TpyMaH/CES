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
 * Class CCommand
 */
class CCommand
{
    public $types = array(
        'tar' => 'Model_Exec_tar',
        'cp' => 'Model_Exec_cp',
        'mv' => 'Model_Exec_mv',
        'rm' => 'Model_Exec_rm',
        'killall' => 'Model_Exec_killall',
        'mysqldump' => 'Model_Exec_mysqldump',
        'exec' => 'Model_Exec_exec',
        'bz2' => 'Model_Exec_bz2',
        'df' => 'Model_Exec_df',
        'raid' => 'Model_Exec_raid',
        'ps' => 'Model_Exec_ps',
        'httpstat' => 'Model_Exec_httpstat',
        'du' => 'Model_Exec_du',
        'ping' => 'Model_Exec_ping',
        'timekill' => 'Model_Exec_timekill'
    );

    protected $_commandList;
    protected $_currentCommand;
    protected $_currentCommandClass;
    protected $_currentCommandObj;
    protected $_finishedCommands;

    /**
     * @param $commandList
     */
    public function __construct($commandList)
    {
        if (!isset($commandList['command'])) {
            $commandList['command'] = array();
        }
        $this->_commandList = $commandList['command'];
    }

    /**
     * @return bool|Model_Exec
     * @throws Exception
     */
    public function next()
    {
        $currentTaskInfo = Ces::task()->currentTaskInfo();
        if (is_object($this->_currentCommandObj)) {
            Ces::log()->Log("End '" . $this->_currentCommand[0] . "' command of '" . $currentTaskInfo['name'] . "' task.");
        }
        if (is_array($this->_commandList) && !empty($this->_commandList)) {
            $command = array_shift($this->_commandList);
            if (is_array($command) && !empty($command)) {
                $this->_currentCommand = $command;
                Ces::log()->log("Start '" . $this->_currentCommand[0] . "' command of '" . $currentTaskInfo['name'] . "' task.");
                $this->_currentCommandObj = $this->setCommandObj();
                return $this->_currentCommandObj;
            } else {
                Ces::log()->log("Params error in unknow command of '" . $currentTaskInfo['name'] . "' task.", LOG_WARNING);
            }
        } else {
            return false;
        }
    }

    /**
     * @return mixed
     */
    public function commandClass()
    {
        return $this->_currentCommandClass;
    }


    /**
     * @return bool|Model_Exec
     * @throws Exception
     */
    protected function setCommandObj()
    {
        $currentTaskInfo = Ces::task()->currentTaskInfo();
        $types = $this->types;
        $command = $this->_currentCommand;
        if (is_array($command) && !empty($command)) {
            $commandClass = array_shift($command);
            $this->_currentCommandClass = $commandClass;
            if (isset($types[$commandClass])) {
                $class = $types[$commandClass];
                return new $class($command);
            } else {
                Ces::notice()->TaskError();
                Ces::log()->log("Unknow command - '" . $this->_currentCommand[0] . "' of '" . $currentTaskInfo['name'] . "' task.", LOG_WARNING);
                return false;
            }
        } else {
            Ces::log()->log("Params error in unknow command of '" . $currentTaskInfo['name'] . "' task.", LOG_WARNING);
            return false;
        }
    }

}

?>
