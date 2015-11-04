<?php

class Model_Exec_bz2 extends Model_Exec{
    public function __construct($data) {
        $this->_name = 'bz2';
        
        $commandParams['what'] = array_shift($data);
        $commandParams['to'] = array_shift($data);
        if (is_array($data) && !empty($data)){
            $commandParams['comment'] = array_shift($data);
        }
        if (is_array($data) && !empty($data)){
            $commandParams['hide'] = array_shift($data);
            unset($data);
        }
        parent::__construct($commandParams);
    }
    
    public function run(){
        $currentTaskInfo = Ces::task()->currentTaskInfo();

        $command = "cat " . $this->_commandParams['what'] . " | gzip > " . $this->_commandParams['to'];
        if ($this->DoExec($command, true)){
            $funcReturn = TRUE;
        }
        else {
            Ces::log()->log("Can't exec '" . $command . "' in '" . $this->_name . "' command of '" . $currentTaskInfo['name'] . "' task.", LOG_WARNING);
            $funcReturn = FALSE;
        }
        $return = "";
        if (isset($this->_commandParams['comment'])){
            $return .= " (" . $this->_commandParams['comment']. ")";
        }
        Ces::notice()->CommandReturn($return);
        return $funcReturn;
    }
}
?>
