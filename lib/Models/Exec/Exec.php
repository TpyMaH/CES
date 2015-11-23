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
use \ces\models\Exec as CoreExec;

/**
 * Class Model_Exec_exec
 * @package ces\models\exec
 */
class Exec extends CoreExec
{
    private $commands;

    public function __construct($data)
    {
        $this->_name = 'exec';

        $commandParams['command'] = array_shift($data);

        if (array_key_exists('command', $data)) {
            $this->commands = $data['command'];
            unset($data['command']);
        }

        if (is_array($data) && !empty($data)) {
            $commandParams['return'] = array_shift($data);
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

    public function run()
    {
        $currentTaskInfo = Ces::task()->currentTaskInfo();

        $command = $this->commandParams['command'];

        if ($this->doExec($command, true)) {
            $funcReturn = true;
        } else {
            $message = "Can't exec '" . $command . "' in '"
                . $this->_name . "' command of '" . $currentTaskInfo['name'] . "' task.";
            Ces::log()->log($message, LOG_WARNING);
            $funcReturn = false;
        }
        if (isset($this->commandParams['return']) && $this->commandParams['return'] == false) {
            $funcReturn = true;
        }

        $this->supportCommandRun($funcReturn ? 'success' : 'error');

        $return = "";
        if (isset($this->commandParams['comment'])) {
            $return .= " (" . $this->commandParams['comment'] . ")";
        }
        Ces::notice()->commandReturn($return);
        return $funcReturn;
    }

    /**
     * @param $type
     * @return bool
     */
    private function supportCommandRun($type)
    {
        if (array_key_exists($type, $this->commands) && !empty($this->commands[$type])) {
            return $this->doExec($this->commands[$type], true);
        }
        return false;
    }
}
