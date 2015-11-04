<?php
class Model_Exec_df extends Model_Exec{
    public function __construct($data) {
        $this->_name = 'df';
        
        $commandParams['what'] = array_shift($data);
        
        if (is_array($data) && !empty($data)){
            $commandParams['limit'] = array_shift($data);
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

        $command = "df -h " . $this->_commandParams['what'] . " | grep \"" . $this->_commandParams['what'] . "\" | awk '{ print $5}'";
        if ($this->DoExec($command, true, $return)){
            if (empty($return)){
                $return = 'path error';
                $a = false;
            } else {
                $return = $return[0];
                $a = true;
            }
            if ($a && isset($this->_commandParams['limit'])){
                $t = str_replace("%", "", $return);
                if ($t >= $this->_commandParams['limit']){
                    $funcReturn = FALSE;
                    Ces::log()->log("Space limit in '" . $command . "' in '" . $this->_name . "' command of '" . $currentTaskInfo['name'] . "' task.", LOG_WARNING);
                }
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
