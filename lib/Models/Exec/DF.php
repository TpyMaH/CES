<?php
namespace ces\models\exec;

use \ces\Ces;
use \ces\models\Exec;

class DF extends Exec
{
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

    public function run()
    {
        $currentTaskInfo = Ces::task()->currentTaskInfo();

        $command = "df -h "
            . $this->commandParams['what']
            . " | grep \""
            . $this->commandParams['what']
            . "\" | awk '{ print $5}'";

        $return = null;
        if ($this->DoExec($command, true, $return)) {
            if (empty($return)) {
                $return = 'path error';
                $a = false;
            } else {
                $return = $return[0];
                $a = true;
            }
            if ($a && isset($this->commandParams['limit'])) {
                $t = str_replace("%", "", $return);
                if ($t >= $this->commandParams['limit']) {
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

?>
