<?php
class Model_Exec_du extends Model_Exec{
    public function __construct($data) {
        $this->_name = 'du';
        
        $commandParams['what'] = array_shift($data);
        
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

        $command = "du -sh " . $this->_commandParams['what'] . " | awk '{ print $1}'";
        if ($this->DoExec($command, true, $return)){
            if (empty($return)){
                $return = 'path error';
                $a = false;
            } else {
                $return = $return[0];
                $a = true;
            }

            if (isset($this->_commandParams['comment'])){
                $return .= " (" . $this->_commandParams['comment']. ")";
            }
            Ces::notice()->CommandReturn($return);
            
            $funcReturn = ((isset($funcReturn) && $funcReturn === FALSE) ? FALSE : TRUE);
        }
        else {
            Ces::log()->log("Can't exec '" . $command . "' in '" . $this->_name . "' command of '" . $currentTaskInfo['name'] . "' task.", LOG_WARNING);
            $funcReturn = FALSE;
        }

        return $funcReturn;
    }
}
?>
