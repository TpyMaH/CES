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
 * Class DF
 * @package ces\models\exec
 */
class DF extends Exec
{
    /**
     * @inheritdoc
     */
    public function __construct($data)
    {
        $this->_name = 'df';

        $commandParams['what'] = array_shift($data);

        if (is_array($data) && !empty($data)) {
            $commandParams['limit'] = array_shift($data);
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

        $command = "df -h "
            . $this->commandParams['what']
            . " | grep \""
            . $this->commandParams['what']
            . "\" | awk '{ print $5}'";

        $return = null;
        if ($this->doExec($command, true, $return)) {
            if (empty($return)) {
                $return = 'path error';
                $hasSize = false;
            } else {
                $return = $return[0];
                $hasSize = true;
            }
            if ($hasSize && isset($this->commandParams['limit'])) {
                $sizePercent = str_replace("%", "", $return);
                if ($sizePercent >= $this->commandParams['limit']) {
                    $funcReturn = false;
                    $message = "Space limit in '" . $command . "' in '"
                        . $this->_name . "' command of '" . $currentTaskInfo['name'] . "' task.";
                    Ces::log()->log($message, LOG_WARNING);
                }
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
