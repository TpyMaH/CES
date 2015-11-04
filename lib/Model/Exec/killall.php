<?php
class Model_Exec_killall extends Model_Exec{
    public function __construct($data) {
        $this->_name = 'killall';
        
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
    
    protected function ParseOptions(){
        if (isset($this->_commandParams['options'])){
            $options = $this->_commandParams['options'];
            if (is_array($options)){
                $options = array_shift($options);
            }
            $this->_commandParams['options'] = $options;
            return true;
        }
        return false;
    }
    
    public function run(){
        $currentTaskInfo = Ces::task()->currentTaskInfo();

        $command = $this->_execPath . " " . $this->_commandParams['options'] . " " . $this->_commandParams['what'];
        if ($this->DoExec($command, true)){
            $funcReturn = TRUE;
        }
        else {
            //Ces::log()->log("Can't exec '" . $command . "' in '" . $this->_name . "' command of '" . $currentTaskInfo['name'] . "' task.", LOG_WARNING);
            $funcReturn = true;
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
