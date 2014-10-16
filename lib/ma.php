<?php
define("MA_LIB_ROOT", dirname(__FILE__));

include MA_LIB_ROOT . "/Core/CModel.php";
include MA_LIB_ROOT . "/Core/CTask.php";
include MA_LIB_ROOT . "/Core/CCommand.php";
include MA_LIB_ROOT . "/Core/CLog.php";
include MA_LIB_ROOT . "/Model/Mail.php";
include MA_LIB_ROOT . "/Model/Notice.php";
include MA_LIB_ROOT . "/Model/Sms.php";
include MA_LIB_ROOT . "/Model/Exec.php";
include MA_LIB_ROOT . "/Model/Exec/tar.php";
include MA_LIB_ROOT . "/Model/Exec/cp.php";
include MA_LIB_ROOT . "/Model/Exec/mv.php";
include MA_LIB_ROOT . "/Model/Exec/rm.php";
include MA_LIB_ROOT . "/Model/Exec/killall.php";
include MA_LIB_ROOT . "/Model/Exec/mysqldump.php";
include MA_LIB_ROOT . "/Model/Exec/exec.php";
include MA_LIB_ROOT . "/Model/Exec/bz2.php";
include MA_LIB_ROOT . "/Model/Exec/df.php";
include MA_LIB_ROOT . "/Model/Exec/raid.php";
include MA_LIB_ROOT . "/Model/Exec/ps.php";
include MA_LIB_ROOT . "/Model/Exec/httpstat.php";
include MA_LIB_ROOT . "/Model/Exec/du.php";
include MA_LIB_ROOT . "/Model/Exec/ping.php";

class MA{
    static protected $_notice;
    static protected $_task;
    static protected $_log;
    
    static public $version = "0.0a2.0.0";

    static public function Notice(){
        if (is_object(self::$_notice)){
            return self::$_notice;
        }
        else {
            self::$_notice = new MA_Model_Notice();
            return self::$_notice;
        }
    }
    
    static public function Task(){
        if (is_object(self::$_task)){
            return self::$_task;
        }
        else {
            return false;
        }
    }
    
    static public function Log(){
        if (is_object(self::$_log)){
            return self::$_log;
        }
        else {
            self::$_log = new MA_CLog();
            return self::$_log;
        }
    }

    static public function Run(){
        self::$_log = new MA_Clog();
        self::$_task = new MA_CTask();
        while($taskCommands = self::$_task->Next()){
            self::notice()->OpenTask(self::$_task->CurrentTaskData());
            
            while($currentCommand = $taskCommands->Next()){
                self::notice()->StartCommand($taskCommands->CommandClass());
                if ($currentCommand->getHideStatus()){
                    self::notice()->CommandHide();
                }
                if ($currentCommand->Run()){
                    self::notice()->CommandStatus(TRUE);
                }
                else {
                    self::notice()->CommandStatus(FALSE);
                    self::notice()->EndCommand();
                    break;
                }
                self::notice()->EndCommand();
            }
        }
        self::notice()->Finish();
    }
    
    static public function showPeriod($time, $type = 'short'){
        $floortime = floor($time);
        if ($floortime < 60){
            return sprintf("%.2F",$time) . "s";
        }
        $hour = floor($floortime/3600);
        $sec = $floortime - ($hour*3600);
        $min = floor($sec/60);
        $sec = $sec - ($min*60);
        $msec = substr(sprintf("%.2F",$time), -3);
        
        $return = '';
        if ($hour > 0){
            if ($return == ""){
                $return .= $hour . ":";
            }
            else {
                $return .= sprintf('%02d:', $hour);
            }
        }
        if ($min > 0){
            if ($return == ""){
                $return .= $min . ":";
            }
            else {
                $return .= sprintf('%02d:', $min);
            }
        }
        $return .= ($return == '' ? $sec : sprintf('%02d', $sec)) . $msec . "s";
        return $return;
    }
    
    static public function pingDomain($domain){
        $starttime = microtime(true);
        $file      = fsockopen ($domain, 80, $errno, $errstr, 10);
        $stoptime  = microtime(true);
        $status    = 0;
        
        if (!$file)
            $status = -1;  // Site is down
        else {
            fclose($file);
            
            $status = ($stoptime - $starttime) * 1000;
            $status = floor($status);
        }
        return $status;   
    }        
}
?>
