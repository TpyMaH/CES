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
namespace ces\models;

use ces\Ces;
use ces\core\Model;
use ces\core\Task;

/**
 * Class Sms
 * @package ces\models
 */
class Sms extends Model
{

    protected $config;
    protected $data;
    protected $counter;
    protected $counterPath;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $sysSms;
        $this->counterPath = BACKUP_ROOT . "/tmp/sms/" . date("Ymd");
        if (!is_dir(dirname($this->counterPath))) {
            mkdir(dirname($this->counterPath), 0777, true);
        }
        if (is_file($this->counterPath)) {
            $this->counter = file_get_contents($this->counterPath);
        } else {
            $this->counter = 0;
        }

        $this->config['enabled'] = isset($sysSms['enabled']) ? $sysSms['enabled'] : false;
        $this->config['serverHost'] = isset($sysSms['serverHost']) ? $sysSms['serverHost'] : false;
        $this->config['sendPage'] = isset($sysSms['sendPage']) ? $sysSms['sendPage'] : false;

        $this->config['encoding'] = '1'; //UTF-8

        $this->config['number'] = isset($sysSms['number']) ? $sysSms['number'] : array();

        if (!empty($this->config['number']) && !is_array($this->config['number'])) {
            $this->config['number'] = array($this->config['number']);
        }

        $this->config['taskId'] = isset($sysSms['taskId']) ? $sysSms['taskId'] : false;

    }

    /**
     * @param $domain
     * @return float|int|mixed
     */
    public function pingDomain($domain)
    {
        $starttime = microtime(true);
        $file = fsockopen($domain, 80, $errno, $errstr, 10);
        $stoptime = microtime(true);
        $status = 0;

        if (!$file)
            $status = -1;  // Site is down
        else {
            fclose($file);

            $status = ($stoptime - $starttime) * 1000;
            $status = floor($status);
        }
        return $status;
    }

    /**
     * @return bool
     */
    protected function check()
    {
        if ($this->config['serverHost'] && $this->pingDomain($this->config['serverHost']) > -1) {
            foreach ($this->config['number'] as $number) {
                if (strlen($number) != 11) {
                    Ces::log()->log("Can't send SMS. Incorrect phone number - " . $number, LOG_WARNING);
                    return false;
                }
            }
        } else {
            Ces::log()->log("Can't send SMS. SMS server not responding.", LOG_WARNING);
            return false;
        }
        return true;
    }

    /*
     * Отсылает sms.
     */
    public function send($message)
    {
        if ($this->counter >= Task::$config['noticeconf']['smsperday']) {
            Ces::log()->log("Can't send SMS. daylimit", LOG_WARNING);
            return false;
        }
        if (!$this->config['enabled']) {
            return false;
        }

        if (isset(Task::$config['notice']) && Task::$config['notice'] == 3) {
            return false;
        }

        if ($this->check()) {
            foreach ($this->config['number'] as $number) {
                $url = $this->config['sendPage']
                    . "?smsphonenumber=" . $number
                    . "&smsmessage=" . str_replace(" ", "%20", $message)
                    . "&smstask=" . $this->config['taskId']
                    . "&encoding=" . $this->config['encoding'];

                if ($r = file_get_contents($url)) {
                    Ces::log()->log("Send SMS notice.");
                    file_put_contents($this->counterPath, ++$this->counter);
                    $return = true;
                } else {
                    Ces::log()->log("Can't send SMS. unknow SMS server error.", LOG_WARNING);
                    $return = false;
                }
            }
            return $return;
        } else {
            return false;
        }
    }
}
