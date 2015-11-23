<?php
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

        $command = $this->_commandParams['command'];

        if ($this->DoExec($command, true)) {
            $funcReturn = true;
        } else {
            Ces::log()->log("Can't exec '" . $command . "' in '" . $this->_name . "' command of '" . $currentTaskInfo['name'] . "' task.", LOG_WARNING);
            $funcReturn = false;
        }
        if (isset($this->_commandParams['return']) && $this->_commandParams['return'] == false) {
            $funcReturn = true;
        }

        $this->supportCommandRun($funcReturn ? 'success' : 'error');

        $return = "";
        if (isset($this->_commandParams['comment'])) {
            $return .= " (" . $this->_commandParams['comment'] . ")";
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
            return $this->DoExec($this->commands[$type], true);
        }
        return false;
    }
}
