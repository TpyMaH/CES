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
 * Class CP
 * @package ces\models\exec
 */
class CP extends Exec
{
    /**
     * @inheritdoc
     */
    public function __construct($data)
    {
        $this->name = 'cp';

        $commandParams['from'] = array_shift($data);
        $commandParams['to'] = array_shift($data);
        if (is_array($data) && !empty($data)) {
            $commandParams['options'] = array_shift($data);
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

        $this->prepareOptions();
        $this->implodePreparedOptions();

        $return2 = "";

        $command = $this->execPath . " " . $this->prepareCommand['options'] . " "
            . $this->commandParams['from'] . " " . $this->commandParams['to'];
        if ($this->doExec($command, true)) {
            $funcReturn = true;

            $command2 = "du -sh " . $this->commandParams['to'] . " | awk '{ print $1}'";
            if ($this->doExec($command2, true, $return2)) {
                if (empty($return2)) {
                    $return2 = 'path error';
                } else {
                    $return2 = $return2[0];
                }
            }
        } else {
            $message = "Can't exec '" . $command . "' in '"
                . $this->name . "' command of '" . $currentTaskInfo['name'] . "' task.";
            Ces::log()->log($message, LOG_WARNING);
            $funcReturn = false;
        }
        $return = $return2;
        if (isset($this->commandParams['comment'])) {
            $return .= " (" . $this->commandParams['comment'] . ")";
        }
        Ces::notice()->commandReturn($return);
        return $funcReturn;
    }
}
