<?php
class MA_Model_Exec_raid extends MA_Model_Exec{
    public function __construct($data) {
        $this->_name = 'raid';
        
        $commandParams = array();
        if (is_array($data) && !empty($data)){
            $commandParams['hide'] = array_shift($data);
            unset($data);
        }
        parent::__construct($commandParams);
    }
    
    public function Run(){

        $data['command'] = $this->_name;
        $data['start'] = microtime(TRUE);


        $currentTaskInfo = MA::Task()->CurrentTaskInfo();

        if (!is_resource(($handle=@fopen("/proc/mdstat","rb")))) {
            MA::Log()->log("Can't exec '" . $this->_name . "' command of '" . $currentTaskInfo['name'] . "' task.", LOG_WARNING);
            return false;
        }
        $flag = false;
        $i = 0;
        $j = 0;
        while(!feof($handle)) {
            $tmp=trim(fgets($handle,8192));
            if (preg_match("/\[(\d+)\/(\d+)\]/",$tmp,$matches)){
                $i++;
            }
            if (preg_match("/\[(\d+)\/(\d+)\]/",$tmp,$matches) && ($matches[1]!=$matches[2])) {
                $flag = true;
                //break;
                $j++;
            }
        }
        fclose($handle);

        MA::Notice()->CommandReturn(($i-$j).'/'.$i);

        $data['end'] = microtime(TRUE);
        MA::Log()->flog($data);

        if (!$flag){
            $funcReturn = TRUE;
        }
        else {
            MA::Log()->log("Can't exec '" . $this->_name . "' command of '" . $currentTaskInfo['name'] . "' task.", LOG_WARNING);
            $funcReturn = FALSE;
        }

        return $funcReturn;
    }
}
?>
