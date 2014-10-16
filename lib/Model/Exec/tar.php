<?php
class MA_Model_Exec_tar extends MA_Model_Exec{
    public function __construct($data) {
        $this->_name = 'tar';
        $this->_requiredOptions = array(
            'c',
            'p',
            'l',
            'f',
        );
        
        $commandParams['file'] = array_shift($data);
        $commandParams['path'] = array_shift($data);
        if (is_array($data) && !empty($data)){
            $commandParams['options'] = array_shift($data);
        }
        if (is_array($data) && !empty($data)){
            $commandParams['comment'] = array_shift($data);
        }
        if (is_array($data) && !empty($data)){
            $commandParams['userCommand'] = array_shift($data);
        }
        if (is_array($data) && !empty($data)){
            $commandParams['ignoreCommand'] = array_shift($data);
        }
        if (is_array($data) && !empty($data)){
            $commandParams['hide'] = array_shift($data);
            unset($data);
        }
        parent::__construct($commandParams);
    }
    
    protected function ImplodePreparedOptions(){
        $options = "-";
        foreach ($this->_prepareCommand['options'] as $option){
            switch ($option){
                case 'c':
                    $options .= 'c';
                    break;
                case 'z':
                    $options .= 'z';
                    break;
                case 'j':
                    $options .= 'j';
                    break;
                case 'p':
                    $options .= 'p';
                    break;
                case 'l':
                    $options .= 'l';
                    break;
                case 'f':
                    $options .= 'f';
                    break;
            }
        }
        if (array_search("f", $this->_requiredOptions)){
            $options = str_replace("f", "", $options) . "f";
        }
        $this->_prepareCommand['options'] = $options;
    }
    
    public function Run(){
        $currentTaskInfo = MA::Task()->CurrentTaskInfo();
        
        $this->PrepareOptions();
        $this->ImplodePreparedOptions();

        $command  = "cd " . $this->_commandParams['path'] . " && ";
        if (isset($this->_commandParams['userCommand']) && $this->_commandParams['userCommand'] === true){
            $command .= $this->_execPath . " " . implode("", $this->_commandParams['options']);
        }
        else {
            $command .= $this->_execPath . " " . $this->_prepareCommand['options'] . " " . $this->_commandParams['file'] . " .";
        }
            
        if ($this->DoExec($command, false, $return, true, $code)){
            $funcReturn = TRUE;
        }
        else {
            MA::Log()->log("Can't exec '" . $command . "' in 'tar' command of '" . $currentTaskInfo['name'] . "' task.", LOG_WARNING);
            $funcReturn = FALSE;
        }
        $return = "code: " . $code;
        if (isset($this->_commandParams['comment'])){
            $return .= " (" . $this->_commandParams['comment']. ")";
        }
        MA::Notice()->CommandReturn($return);
        if (isset($this->_commandParams['ignoreCommand']) && $this->_commandParams['ignoreCommand'] === true){
            $funcReturn = true;
        }
        return $funcReturn;
    }
}
?>
