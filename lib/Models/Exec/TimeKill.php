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
namespace ces\models\exec;

use \ces\Ces;
use \ces\models\Exec;

/**
 * Class TimeKill
 * @package ces\models\exec
 */
class TimeKill extends Exec
{
    /**
     * @inheritdoc
     */
    public function __construct($data)
    {
        $this->_name = 'timekill';

        $commandParams['what'] = array_shift($data);
        if (is_array($data) && !empty($data)) {
            $commandParams['time'] = array_shift($data);
        }
        if (is_array($data) && !empty($data)) {
            $commandParams['comment'] = array_shift($data);
        }
        if (is_array($data) && !empty($data)) {
            $commandParams['hide'] = array_shift($data);
            unset($data);
        }
        $this->SetExecPath('ps');
        parent::__construct($commandParams, 'ps');
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $currentTaskInfo = Ces::task()->currentTaskInfo();

        $command = $this->execPath . " -eo pid,etime,cmd |grep " . $this->commandParams['what'];

        if ($this->doExec($command, true, $return)) {
            $lines = array();
            if (is_array($return)) {
                foreach ($return as $line) {
                    if (strpos($line, 'grep ' . $this->commandParams['what']) === false) {
                        $lines[] = $line;
                    }
                }
                $return = $lines;
            }

            $status = 0;
            foreach ($return as $line) {
                $line = explode(" ", trim($line));
                $pid = $line[0];

                $timeCommand = 'ps -eo pid,etimes,command | grep -i '
                    . $pid . ' | grep -v grep | awk \'{printf("%.0f\n", $2)}\'';
                $this->doExec($timeCommand, true, $time);

                if (!empty($time)) {
                    $time = $time[0];
                } else {
                    $time = 0;
                }

                if ($time >= $this->commandParams['time']) {
                    $this->doExec('kill -HUP ' . $pid, true, $tmp);
                    $status = 1;
                }
            }

            $return = $status;
            if ($return == 1) {
                $message = "'" . $this->commandParams['what'] . "' process was terminated. '" . $command . "' in '"
                    . $this->_name . "' command of '" . $currentTaskInfo['name'] . "' task.";
                Ces::log()->log($message, LOG_WARNING);
                $funcReturn = false;
            } else {
                $funcReturn = true;
            }
        } else {
            $message = "Can't exec '" . $command . "' in '"
                . $this->_name . "' command of '" . $currentTaskInfo['name'] . "' task.";
            Ces::log()->log($message, LOG_WARNING);
            $funcReturn = false;
        }
        $return = "";
        if (isset($this->commandParams['comment'])) {
            $return .= " (" . $this->commandParams['comment'] . ")";
        }
        Ces::notice()->commandReturn($return);
        return $funcReturn;
    }
}
