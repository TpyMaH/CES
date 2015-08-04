<?php
class MA_Model_Exec_rm extends MA_Model_Exec{
    public function __construct($data) {
        $this->_name = 'rm';
        
        $commandParams['what'] = array_shift($data);
        if (is_array($data) && !empty($data)){
            $commandParams['options'] = array_shift($data);
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

        $this->PrepareOptions();
        $this->ImplodePreparedOptions();

        $command = $this->_execPath . " " . $this->_prepareCommand['options'] . " " . $this->_commandParams['what'];
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
