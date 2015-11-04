<?php

/**
 * Class MA_CLog
 */
class MA_CLog
{
    protected $_sid;
    protected $_flog;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->_sid = time();
        $this->_flog = MA_BACKUP_ROOT . "/tmp/report/report-" . $this->_sid . ".txt";
        if (!is_dir(dirname($this->_flog))) {
            mkdir(dirname($this->_flog), 0777, true);
        }
        openlog("MA_Exec_System", LOG_PID | LOG_PERROR, LOG_CRON);
    }

    /**
     * @param $message
     * @param int $pririty
     * @return bool
     */
    public function log($message, $pririty = LOG_DEBUG)
    {
        if (!MA_DEBUG && $pririty == LOG_DEBUG) {
            return true;
        }
        syslog($pririty, $message);
    }

    /**
     * @param $data
     */
    public function flog($data)
    {
        $pack = "\r\n";
        $pack .= "Process:\r\n";
        $pack .= $data['command'] . "\r\n";
        $pack .= "Process take: " . sprintf("%.2F", ($data['end'] - $data['start'])) . "s\r\n";
        $pack .= str_repeat("-", 50) . "\r\n";

        $handle = fopen($this->_flog, 'a+');
        fwrite($handle, $pack);
        fclose($handle);
        //file_put_contents($this->_flog, $pack, FILE_APPEND);
    }

    /**
     * @return string
     */
    public function flogPath()
    {
        return $this->_flog;
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        closelog();
        $exec = new MA_Model_Exec('', 'notice');
        if (is_file($this->_flog)) {
            $exec->DoExec("rm " . $this->_flog, TRUE, $r, false);
        }
    }
}

