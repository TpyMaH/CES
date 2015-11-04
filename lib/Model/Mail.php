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
 * Class Model_Mail
 *
 * Usage Example:
 *   $mulmail = new Model_Mail();
 *   $cid = $mulmail->AddAttachment(file, "octet-stream");
 *   $mulmail->AddMessage("Message");
 *   $mulmail->Send();
 */
class Model_Mail extends CModel
{
    protected $_config;
    protected $header;
    protected $parts;
    protected $message;
    protected $boundary;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $sysMail;
        $this->_config = $sysMail;

        if (!is_array($this->_config['to'])) {
            $this->_config['to'] = array($this->_config['to']);
        }
        if (!is_array($this->_config['error_to'])) {
            $this->_config['error_to'] = array($this->_config['error_to']);
        }

        if (!isset(CTask::$config['ignore_default_mails'])) {
            CTask::$config['ignore_default_mails'] = false;
        }

        if (CTask::$config['ignore_default_mails'] == true && isset(CTask::$config['additional_mails']) && isset(CTask::$config['additional_error_mails'])) {
            $this->_config['to'] = array();
            $this->_config['error_to'] = array();
        }

        if (isset(CTask::$config['additional_mails'])) {
            if (!is_array(CTask::$config['additional_mails'])) {
                CTask::$config['additional_mails'] = array(CTask::$config['additional_mails']);
            }
        }

        if (isset(CTask::$config['additional_error_mails'])) {
            if (!is_array(CTask::$config['additional_error_mails'])) {
                CTask::$config['additional_error_mails'] = array(CTask::$config['additional_error_mails']);
            }
        }

        if (isset(CTask::$config['subject'])) {
            $this->_config['subject'] = CTask::$config['subject'];
        }

        if (isset(CTask::$config['error_subject'])) {
            $this->_config['error_subject'] = CTask::$config['error_subject'];
        }

        if (isset(CTask::$config['additional_mails'])) {
            $this->_config['to'] = array_merge($this->_config['to'], CTask::$config['additional_mails']);
        }

        if (isset(CTask::$config['additional_error_mails'])) {
            $this->_config['error_to'] = array_merge($this->_config['error_to'], CTask::$config['additional_error_mails']);
        }
        $this->parts = array("");
        $this->boundary = "--" . md5(uniqid(time()));
        $this->header =
            "MIME-Version: 1.0\r\n" .
            "Content-Type: multipart/mixed; " .
            " boundary=\"" . $this->boundary . "\"\r\n" .
            'Date: ' . date('r', $_SERVER['REQUEST_TIME']) . "\r\n" .
            'From: ' . $this->_config['from'] . "\r\n" .
            'Reply-To: ' . $this->_config['from'] . "\r\n" .
            'Return-Path: ' . $this->_config['from'] . "\r\n" .
            'X-Mailer: PHP v' . phpversion();
    }

    /**
     * @param string $msg
     */
    public function AddMessage($msg = "")
    {
        $this->parts[0] =
            "Content-Type: text/html; charset=UTF-8\r\n" .
            "Content-Transfer-Encoding: 7bit\r\n" .
            "\n" . $msg . "\r\n";
    }

    /**
     * @param $file
     * @param $ctype
     * @return string
     */
    public function AddAttachment($file, $ctype)
    {
        $fname = substr(strrchr(str_replace('\\', '/', $file), '/'), 1);
        $data = file_get_contents($file);
        $i = count($this->parts);
        $content_id = "part$i." . sprintf("%09d", crc32($fname)) . strrchr($this->_config['to'][0], "@");
        $this->parts[$i] =
            "Content-Type: $ctype; name=\"$fname\"\r\n" .
            "Content-Transfer-Encoding: base64\r\n" .
            "Content-ID: <$content_id>\r\n" .
            "Content-Disposition: attachment; " .
            " filename=\"$fname\"\r\n" .
            "\n" . chunk_split(base64_encode($data), 68, "\n");
        return $content_id;
    }

    /**
     * Build
     */
    public function BuildMessage()
    {
        $cnt = count($this->parts);
        for ($i = 0; $i < $cnt; $i++) {
            $this->message .= "--" . $this->boundary . "\n" . $this->parts[$i];
        }
    }

    /**
     * Send
     */
    public function Send()
    {
        foreach ($this->_config['to'] as $to) {
            Ces::log()->Log("Start sending mail to '" . $to . " from " . $this->_config['from']);
            mail($to, '=?UTF-8?B?' . base64_encode($this->_config['subject']) . '?=', $this->message, $this->header);
            Ces::log()->Log("End sending mail to '" . $to . " from " . $this->_config['from']);
        }
    }

    /**
     * Send Error
     */
    public function SendError()
    {
        global $sysMail;
        $to = $this->_config['to'];
        $this->_config['to'] = $this->_config['error_to'];
        $this->_config['from'] = $sysMail['error_from'];
        $subject = $this->_config['subject'];
        $this->_config['subject'] = $this->_config['error_subject'];

        $this->Send();

        $this->_config['to'] = $to;
        $this->_config['from'] = $sysMail['from'];
        $this->_config['subject'] = $subject;
    }
}

?>
