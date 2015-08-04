<?php
class MA_Model_Exec_exec extends MA_Model_Exec{
    public function __construct($data) {
        $this->_name = 'exec';
        
        $commandParams['command'] = array_shift($data);

        if (is_array($data) && !empty($data)){
            $commandParams['return'] = array_shift($data);
        }
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

        $command = $this->_commandParams['command'];
        if ($this->DoExec($command, true)){
            $funcReturn = TRUE;
        }
        else {
            MA::Log()->log("Can't exec '" . $command . "' in '" . $this->_name . "' command of '" . $currentTaskInfo['name'] . "' task.", LOG_WARNING);
            $funcReturn = FALSE;
        }
        if (isset($this->_commandParams['return']) && $this->_commandParams['return'] == FALSE){
            $funcReturn = TRUE;
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
