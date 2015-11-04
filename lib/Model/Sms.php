<?php

/**
 * Class MA_Model_Sms
 */
class MA_Model_Sms extends MA_CModel
{

    protected $_config;
    protected $_data;
    protected $_counter;
    protected $_counterPath;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $sysSms;
        $this->_counterPath = MA_BACKUP_ROOT . "/tmp/sms/" . date("Ymd");
        if (!is_dir(dirname($this->_counterPath))) {
            mkdir(dirname($this->_counterPath), 0777, true);
        }
        if (is_file($this->_counterPath)) {
            $this->_counter = file_get_contents($this->_counterPath);
        } else {
            $this->_counter = 0;
        }

        $this->_config['enabled'] = isset($sysSms['enabled']) ? $sysSms['enabled'] : false;
        $this->_config['serverHost'] = isset($sysSms['serverHost']) ? $sysSms['serverHost'] : false;
        $this->_config['sendPage'] = isset($sysSms['sendPage']) ? $sysSms['sendPage'] : false;

        $this->_config['encoding'] = '1'; //UTF-8

        $this->_config['number'] = isset($sysSms['number']) ? $sysSms['number'] : array();

        if (!empty($this->_config['number']) && !is_array($this->_config['number'])) {
            $this->_config['number'] = array($this->_config['number']);
        }

        $this->_config['taskId'] = isset($sysSms['taskId']) ? $sysSms['taskId'] : false;

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
        if ($this->_config['serverHost'] && $this->pingDomain($this->_config['serverHost']) > -1) {
            foreach ($this->_config['number'] as $number) {
                if (strlen($number) != 11) {
                    MA::Log()->log("Can't send SMS. Incorrect phone number - " . $number, LOG_WARNING);
                    return false;
                }
            }
        } else {
            MA::Log()->log("Can't send SMS. SMS server not responding.", LOG_WARNING);
            return false;
        }
        return true;
    }

    /*
     * Отсылает sms.
     */
    public function Send($message)
    {
        if ($this->_counter >= MA_CTask::$config['noticeconf']['smsperday']) {
            MA::Log()->log("Can't send SMS. daylimit", LOG_WARNING);
            return false;
        }
        if (!$this->_config['enabled']) {
            return false;
        }

        if (isset(MA_CTask::$config['notice']) && MA_CTask::$config['notice'] == 3) {
            return false;
        }

        if ($this->check()) {
            foreach ($this->_config['number'] as $number) {
                $url = $this->_config['sendPage']
                    . "?smsphonenumber=" . $number
                    . "&smsmessage=" . str_replace(" ", "%20", $message)
                    . "&smstask=" . $this->_config['taskId']
                    . "&encoding=" . $this->_config['encoding'];

                if ($r = file_get_contents($url)) {
                    MA::Log()->log("Send SMS notice.");
                    file_put_contents($this->_counterPath, ++$this->_counter);
                    $return = TRUE;
                } else {
                    MA::Log()->log("Can't send SMS. unknow SMS server error.", LOG_WARNING);
                    $return = FALSE;
                }
            }
            return $return;
        } else {
            return FALSE;
        }
    }
}

?>
