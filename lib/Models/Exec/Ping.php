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
 * Class Ping
 * @package ces\models\exec
 */
class Ping extends Exec
{
    /**
     * @inheritdoc
     */
    public function __construct($data)
    {
        $this->_name = 'ping';

        $commandParams['what'] = array_shift($data);

        if (is_array($data) && !empty($data)) {
            $commandParams['packet'] = array_shift($data);
        }
        if (is_array($data) && !empty($data)) {
            $commandParams['lost'] = array_shift($data);
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

        $command = "ping -c " . $this->commandParams['packet'] .
            " " . $this->commandParams['what'] . " | grep 'packet loss,'";
        $return = null;
        if ($this->doExec($command, true, $return)) {
            if (empty($return)) {
                $return = 'host error';
            } else {
                $loststring = $return[0];

                $loststring = explode(", ", $loststring);
                foreach ($loststring as $value) {
                    if (strpos($value, "packet loss") !== false) {
                        $lost = str_replace("% packet loss", "", $value);
                    }
                }

                if (!isset($lost)) {
                    $lost = 'unknow';
                    $funcReturn = false;
                } elseif (isset($this->commandParams['lost']) && $this->commandParams['lost'] <= $lost) {
                    $funcReturn = false;
                }

                $return = $lost;

            }

            if (isset($this->commandParams['comment'])) {
                $return .= " (" . $this->commandParams['comment'] . ")";
            }
            Ces::notice()->commandReturn($return);

            $funcReturn = ((isset($funcReturn) && $funcReturn === false) ? false : true);
        } else {
            $message = "Can't exec '" . $command . "' in '"
                . $this->_name . "' command of '" . $currentTaskInfo['name'] . "' task.";
            Ces::log()->log($message, LOG_WARNING);
            $funcReturn = false;
        }

        return $funcReturn;
    }
}
