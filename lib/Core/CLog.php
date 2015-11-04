<?php
/**
 * CES - Cron Exec System
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright (c) 2015, TpyMaH (Vadims Bucinskis) <vadim.buchinsky@gmail.com>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Class CLog
 */
class CLog
{
    protected $_sid;
    protected $_flog;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->_sid = time();
        $this->_flog = BACKUP_ROOT . "/tmp/report/report-" . $this->_sid . ".txt";
        if (!is_dir(dirname($this->_flog))) {
            mkdir(dirname($this->_flog), 0777, true);
        }
        openlog("Exec_System", LOG_PID | LOG_PERROR, LOG_CRON);
    }

    /**
     * @param $message
     * @param int $pririty
     * @return bool
     */
    public function log($message, $pririty = LOG_DEBUG)
    {
        if (!DEBUG && $pririty == LOG_DEBUG) {
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
        $exec = new Model_Exec('', 'notice');
        if (is_file($this->_flog)) {
            $exec->DoExec("rm " . $this->_flog, TRUE, $r, false);
        }
    }
}

