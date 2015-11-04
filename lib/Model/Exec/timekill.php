<?php
class Model_Exec_timekill extends Model_Exec{
    public function __construct($data) {
        $this->_name = 'timekill';
        
        $commandParams['what'] = array_shift($data);
        if (is_array($data) && !empty($data)){
            $commandParams['time'] = array_shift($data);
        }
        if (is_array($data) && !empty($data)){
            $commandParams['comment'] = array_shift($data);
        }
        if (is_array($data) && !empty($data)){
            $commandParams['hide'] = array_shift($data);
            unset($data);
        }
        $this->SetExecPath('ps');
        parent::__construct($commandParams,'ps');
    }
    
    public function run(){
        $currentTaskInfo = Ces::task()->currentTaskInfo();

        $command = $this->_execPath . " -eo pid,etime,cmd |grep " . $this->_commandParams['what'];

        if ($this->DoExec($command, true, $return)){
            $lines = array();
            if (is_array($return)){
                foreach ($return as $line){
                    if (strpos($line, 'grep ' . $this->_commandParams['what']) === false){
                        $lines[] = $line;
                    }
                }
                $return = $lines;
            }

            $status = 0;
            foreach($return as $line) {
                $line = explode(" ", trim($line));
                $pid = $line[0];

                $this->DoExec('ps -eo pid,etimes,command | grep -i ' . $pid . ' | grep -v grep | awk \'{printf("%.0f\n", $2)}\'',true, $time);

                if (!empty($time)) {
                    $time = $time[0];
                }else {
                    $time = 0;
                }

                if ($time >= $this->_commandParams['time']){
                    $this->DoExec('kill -HUP ' . $pid,true, $tmp);
                    $status = 1;
                }
            }

            $return = $status;
            if ($return == 1){
                Ces::log()->log("'" . $this->_commandParams['what'] . "' process was terminated. '" . $command . "' in '" . $this->_name . "' command of '" . $currentTaskInfo['name'] . "' task.", LOG_WARNING);
                $funcReturn = FALSE;
            }
            else {
                $funcReturn = TRUE;
            }
        }
        else {
            Ces::log()->log("Can't exec '" . $command . "' in '" . $this->_name . "' command of '" . $currentTaskInfo['name'] . "' task.", LOG_WARNING);
            $funcReturn = FALSE;
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
