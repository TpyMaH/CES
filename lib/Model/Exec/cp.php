<?php
class Model_Exec_cp extends Model_Exec{
    public function __construct($data) {
        $this->_name = 'cp';
        
        $commandParams['from'] = array_shift($data);
        $commandParams['to'] = array_shift($data);
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
    
    public function run(){
        $currentTaskInfo = Ces::task()->currentTaskInfo();

        $this->PrepareOptions();
        $this->ImplodePreparedOptions();

        $return2 = "";
        
        $command = $this->_execPath . " " . $this->_prepareCommand['options'] . " " . $this->_commandParams['from'] . " " . $this->_commandParams['to'];
        if ($this->DoExec($command, true)){
            $funcReturn = TRUE;
            
            $command2 = "du -sh " . $this->_commandParams['to'] . " | awk '{ print $1}'";
            if ($this->DoExec($command2, true, $return2)){
                if (empty($return2)){
                    $return2 = 'path error';
                } else {
                    $return2 = $return2[0];
                }
            }
        }
        else {
            Ces::log()->log("Can't exec '" . $command . "' in '" . $this->_name . "' command of '" . $currentTaskInfo['name'] . "' task.", LOG_WARNING);
            $funcReturn = FALSE;
        }
        $return = $return2;
        if (isset($this->_commandParams['comment'])){
            $return .= " (" . $this->_commandParams['comment']. ")";
        }
        Ces::notice()->CommandReturn($return);
        return $funcReturn;
    }
}
?>
