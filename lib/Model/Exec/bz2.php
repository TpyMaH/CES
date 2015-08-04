<?php
class MA_Model_Exec_bz2 extends MA_Model_Exec{
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
    
    public function Run(){
        $currentTaskInfo = MA::Task()->CurrentTaskInfo();

        $command = "cat " . $this->_commandParams['what'] . " | gzip > " . $this->_commandParams['to'];
        if ($this->DoExec($command, true)){
            $funcReturn = TRUE;
        }
        else {
            MA::Log()->log("Can't exec '" . $command . "' in '" . $this->_name . "' command of '" . $currentTaskInfo['name'] . "' task.", LOG_WARNING);
            $funcReturn = FALSE;
        }
        $return = "";
        if (isset($this->_commandParams['comment'])){
            $return .= " (" . $this->_commandParams['comment']. ")";
        }
        MA::Notice()->CommandReturn($return);
        return $funcReturn;
    }
}
?>
