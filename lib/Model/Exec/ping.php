<?php
class MA_Model_Exec_ping extends MA_Model_Exec{
    public function __construct($data) {
        $this->_name = 'ping';
        
        $commandParams['what'] = array_shift($data);

        if (is_array($data) && !empty($data)){
            $commandParams['packet'] = array_shift($data);
        }
        if (is_array($data) && !empty($data)){
            $commandParams['lost'] = array_shift($data);
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

        $command = "ping -c " . $this->_commandParams['packet'] . " " . $this->_commandParams['what'] . " | grep 'packet loss,'";
        if ($this->DoExec($command, true, $return)){
            if (empty($return)){
                $return = 'host error';
            } else {
                $loststring = $return[0];
                                
                $loststring = explode(", ", $loststring);
                foreach ($loststring as $value) {
                    if (strpos($value, "packet loss") !== FALSE){
                        $lost = str_replace("% packet loss", "", $value);
                    }
                }
                
                if (!isset($lost)){
                    $lost = 'unknow';
                    $funcReturn = false;
                }
                else if (isset($this->_commandParams['lost']) && $this->_commandParams['lost'] <= $lost){
                    $funcReturn = false;
                }
                
                $return = $lost;
                
            }

            if (isset($this->_commandParams['comment'])){
                $return .= " (" . $this->_commandParams['comment']. ")";
            }
            MA::Notice()->CommandReturn($return);
            
            $funcReturn = ((isset($funcReturn) && $funcReturn === FALSE) ? FALSE : TRUE);
        }
        else {
            MA::Log()->log("Can't exec '" . $command . "' in '" . $this->_name . "' command of '" . $currentTaskInfo['name'] . "' task.", LOG_WARNING);
            $funcReturn = FALSE;
        }

        return $funcReturn;
    }
}
?>
