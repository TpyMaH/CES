<?php
namespace ces\models\exec;

use \ces\Ces;
use \ces\models\Exec;

/**
 * Class Model_Exec_du
 * @package ces\models\exec
 */
class DU extends Exec
{
    public function __construct($data)
    {
        $this->_name = 'du';

        $commandParams['what'] = array_shift($data);

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

        $command = "du -sh " . $this->commandParams['what'] . " | awk '{ print $1}'";
        $return = null;
        if ($this->DoExec($command, true, $return)) {
            if (empty($return)) {
                $return = 'path error';
                $a = false;
            } else {
                $return = $return[0];
                $a = true;
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

