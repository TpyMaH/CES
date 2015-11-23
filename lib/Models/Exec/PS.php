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
 * Class PS
 * @package ces\models\exec
 */
class PS extends Exec
{
    /**
     * @inheritdoc
     */
    public function __construct($data)
    {
        $this->_name = 'ps';

        $commandParams['what'] = array_shift($data);

        if (is_array($data) && !empty($data)) {
            $commandParams['command'] = array_shift($data);
        }
        if (is_array($data) && !empty($data)) {
            $commandParams['repeat'] = array_shift($data);
        }
        if (is_array($data) && !empty($data)) {
            $commandParams['comment'] = array_shift($data);
        }
        if (is_array($data) && !empty($data)) {
            $commandParams['hide'] = array_shift($data);
            unset($data);
        }
        parent::__construct($commandParams);
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $currentTaskInfo = Ces::task()->currentTaskInfo();

        $command = $this->execPath . " -A|grep " . $this->commandParams['what'] . "|wc -l";
        if ($this->doExec($command, true, $return)) {
            $return = $return[0];
            if ($return == 0) {
                $this->execSecond($command);
                $message = "'" . $this->commandParams['what'] . "' process is not running. '"
                    . $command . "' in '" . $this->_name . "' command of '" . $currentTaskInfo['name'] . "' task.";
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

    /**
     * @param $command
     * @return bool
     * @throws \Exception
     */
    protected function execSecond($command)
    {
        if (!isset($this->commandParams['command'])) {
            return true;
        }
        $currentTaskInfo = Ces::task()->currentTaskInfo();
        $repeat = 0;
        if (isset($this->commandParams['repeat'])) {
            $repeat = (int)$this->commandParams['repeat'];
        }
        if ($repeat == 0) {
            $repeat = 1;
        }
        $return = false;
        for ($i = 1; $i <= $repeat; $i++) {
            if ($this->doExec($this->commandParams['command'], true)) {
                sleep(2);
                if ($this->doExec($command, true, $ret)) {
                    $ret = $ret[0];
                    if ($ret == 0) {
                        $return = false;
                    } else {
                        Ces::notice()->sms = false;
                        $return = true;
                        break;
                    }
                }

            } else {
                $message = "Can't exec '" . $this->commandParams['command'] . "' in '"
                    . $this->_name . "' command of '" . $currentTaskInfo['name'] . "' task.";
                Ces::log()->log($message, LOG_WARNING);
                $return = false;
            }
            sleep(2);
        }
        return $return;
    }
}
