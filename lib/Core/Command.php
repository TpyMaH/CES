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

use \ces\Ces;

/**
 * Class Command
 * @package ces\core
 */
class Command
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

    protected $commandList;
    protected $currentCommand;
    protected $currentCommandClass;
    protected $currentCommandObj;
    protected $finishedCommands;

    /**
     * @param $commandList
     */
    public function __construct($commandList)
    {
        if (!isset($commandList['command'])) {
            $commandList['command'] = array();
        }
        $this->commandList = $commandList['command'];
    }

    /**
     * @return bool|\ces\models\Exec
     * @throws \Exception
     */
    public function next()
    {
        $currentTaskInfo = Ces::task()->currentTaskInfo();
        if (is_object($this->currentCommandObj)) {
            $message = "End '" . $this->currentCommand[0] . "' command of '" . $currentTaskInfo['name'] . "' task.";
            Ces::log()->Log($message);
        }
        if (is_array($this->commandList) && !empty($this->commandList)) {
            $command = array_shift($this->commandList);
            if (is_array($command) && !empty($command)) {
                $this->currentCommand = $command;
                $message = "Start '"
                    . $this->currentCommand[0]
                    . "' command of '"
                    . $currentTaskInfo['name'] . "' task.";
                Ces::log()->log($message);
                $this->currentCommandObj = $this->setCommandObj();
                return $this->currentCommandObj;
            } else {
                $message = "Params error in unknow command of '" . $currentTaskInfo['name'] . "' task.";
                Ces::log()->log($message, LOG_WARNING);
            }
        }
        return false;
    }

    /**
     * @return mixed
     */
    public function commandClass()
    {
        return $this->currentCommandClass;
    }


    /**
     * @return bool|\ces\models\Exec
     * @throws \Exception
     */
    protected function setCommandObj()
    {
        $currentTaskInfo = Ces::task()->currentTaskInfo();
        $types = $this->types;
        $command = $this->currentCommand;
        if (is_array($command) && !empty($command)) {
            $commandClass = array_shift($command);
            $this->currentCommandClass = $commandClass;
            if (isset($types[$commandClass])) {
                $class = $types[$commandClass];
                return new $class($command);
            } else {
                Ces::notice()->taskError();
                $message = "Unknown command - '"
                    . $this->currentCommand[0]
                    . "' of '" . $currentTaskInfo['name']
                    . "' task.";
                Ces::log()->log($message, LOG_WARNING);
                return false;
            }
        } else {
            $message = "Params error in unknown command of '" . $currentTaskInfo['name'] . "' task.";
            Ces::log()->log($message, LOG_WARNING);
            return false;
        }
    }
}
