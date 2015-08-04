<?php
class MA_Model_Exec_ps extends MA_Model_Exec{
    public function __construct($data) {
        $this->_name = 'ps';
        
        $commandParams['what'] = array_shift($data);
        
        if (is_array($data) && !empty($data)){
            $commandParams['command'] = array_shift($data);
        }
        if (is_array($data) && !empty($data)){
            $commandParams['repeat'] = array_shift($data);
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

        $command = $this->_execPath . " -A|grep " . $this->_commandParams['what'] . "|wc -l";
        if ($this->DoExec($command, true, $return)){
            $return = $return[0];
            if ($return == 0){
                $this->ExecSecond($command);
                MA::Log()->log("'" . $this->_commandParams['what'] . "' process is not running. '" . $command . "' in '" . $this->_name . "' command of '" . $currentTaskInfo['name'] . "' task.", LOG_WARNING);
                $funcReturn = FALSE;
            }
            else {
                $funcReturn = TRUE;
            }
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
    
    protected function ExecSecond($command){
        if (!isset($this->_commandParams['command'])){
            return true;
        }
        $currentTaskInfo = MA::Task()->CurrentTaskInfo();
        $repeat = 0;
        if (isset($this->_commandParams['repeat'])){
            $repeat = (int) $this->_commandParams['repeat'];
        }
        if ($repeat == 0){
            $repeat = 1;
        }
        for ($i = 1; $i <= $repeat; $i++){
            if ($this->DoExec($this->_commandParams['command'], true)){
                sleep(2);
                if ($this->DoExec($command, true, $ret)){
                    $ret = $ret[0];
                    if ($ret == 0){
                        $return = false;
                    }
                    else {
                        MA::Notice()->sms = false;
                        $return = true;
                        break;
                    }
                }
                
            } else {
                MA::Log()->log("Can't exec '" . $this->_commandParams['command'] . "' in '" . $this->_name . "' command of '" . $currentTaskInfo['name'] . "' task.", LOG_WARNING);
                $return = false;
            }
            sleep(2);
        }
        return $return;
    }
}
?>
