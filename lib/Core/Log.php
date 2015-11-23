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
 * @copyright (c) 2015, TpyMaH (Vadims Bucinskis) <v.buchinsky@etwebsolutions.com>
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace ces\core;

use \ces\models\Exec;

/**
 * Class Log
 * @package ces\core
 */
class Log
{
    protected $sid;
    protected $flog;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->sid = time();
        $this->flog = BACKUP_ROOT . "/tmp/report/report-" . $this->sid . ".txt";
        if (!is_dir(dirname($this->flog))) {
            mkdir(dirname($this->flog), 0777, true);
        }
        openlog("Exec_System", LOG_PID | LOG_PERROR, LOG_CRON);
    }

    /**
     * @param $message
     * @param int $pririty
     */
    public function log($message, $pririty = LOG_DEBUG)
    {
        if (!\DEBUG && $pririty == LOG_DEBUG) {
            return;
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

        $handle = fopen($this->flog, 'a+');
        fwrite($handle, $pack);
        fclose($handle);
    }

    /**
     * @return string
     */
    public function flogPath()
    {
        return $this->flog;
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        closelog();
        $exec = new Exec('', 'notice');
        if (is_file($this->flog)) {
            $exec->doExec("rm " . $this->flog, true, $return, false);
        }
    }
}

