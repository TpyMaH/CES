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

use \ces\Ces;
use \ces\core\Model;
use \ces\core\Task;

/**
 * Class Model_Mail
 * @package ces\models
 *
 * Usage Example:
 *   $mulmail = new Model_Mail();
 *   $cid = $mulmail->AddAttachment(file, "octet-stream");
 *   $mulmail->AddMessage("Message");
 *   $mulmail->Send();
 */
class Mail extends Model
{
    protected $config;
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
        $this->config = $sysMail;

        if (!is_array($this->config['to'])) {
            $this->config['to'] = array($this->config['to']);
        }
        if (!is_array($this->config['error_to'])) {
            $this->config['error_to'] = array($this->config['error_to']);
        }

        if (!isset(Task::$config['ignore_default_mails'])) {
            Task::$config['ignore_default_mails'] = false;
        }

        if (Task::$config['ignore_default_mails'] == true
            && isset(Task::$config['additional_mails']) && isset(Task::$config['additional_error_mails'])
        ) {
            $this->config['to'] = array();
            $this->config['error_to'] = array();
        }

        if (isset(Task::$config['additional_mails'])) {
            if (!is_array(Task::$config['additional_mails'])) {
                Task::$config['additional_mails'] = array(Task::$config['additional_mails']);
            }
        }

        if (isset(Task::$config['additional_error_mails'])) {
            if (!is_array(Task::$config['additional_error_mails'])) {
                Task::$config['additional_error_mails'] = array(Task::$config['additional_error_mails']);
            }
        }

        if (isset(Task::$config['subject'])) {
            $this->config['subject'] = Task::$config['subject'];
        }

        if (isset(Task::$config['error_subject'])) {
            $this->config['error_subject'] = Task::$config['error_subject'];
        }

        if (isset(Task::$config['additional_mails'])) {
            $this->config['to'] = array_merge($this->config['to'], Task::$config['additional_mails']);
        }

        if (isset(Task::$config['additional_error_mails'])) {
            $this->config['error_to'] = array_merge($this->config['error_to'], Task::$config['additional_error_mails']);
        }
        $this->parts = array("");
        $this->boundary = "--" . md5(uniqid(time()));
        $this->header =
            "MIME-Version: 1.0\r\n" .
            "Content-Type: multipart/mixed; " .
            " boundary=\"" . $this->boundary . "\"\r\n" .
            'Date: ' . date('r', $_SERVER['REQUEST_TIME']) . "\r\n" .
            'From: ' . $this->config['from'] . "\r\n" .
            'Reply-To: ' . $this->config['from'] . "\r\n" .
            'Return-Path: ' . $this->config['from'] . "\r\n" .
            'X-Mailer: PHP v' . phpversion();
    }

    /**
     * @param string $msg
     */
    public function addMessage($msg = "")
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
    public function addAttachment($file, $ctype)
    {
        $fname = substr(strrchr(str_replace('\\', '/', $file), '/'), 1);
        $data = file_get_contents($file);
        $i = count($this->parts);
        $content_id = "part$i." . sprintf("%09d", crc32($fname)) . strrchr($this->config['to'][0], "@");
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
    public function buildMessage()
    {
        $cnt = count($this->parts);
        for ($i = 0; $i < $cnt; $i++) {
            $this->message .= "--" . $this->boundary . "\n" . $this->parts[$i];
        }
    }

    /**
     * Send
     */
    public function send()
    {
        foreach ($this->config['to'] as $to) {
            Ces::log()->Log("Start sending mail to '" . $to . " from " . $this->config['from']);
            mail($to, '=?UTF-8?B?' . base64_encode($this->config['subject']) . '?=', $this->message, $this->header);
            Ces::log()->Log("End sending mail to '" . $to . " from " . $this->config['from']);
        }
    }

    /**
     * Send Error
     */
    public function sendError()
    {
        global $sysMail;
        $to = $this->config['to'];
        $this->config['to'] = $this->config['error_to'];
        $this->config['from'] = $sysMail['error_from'];
        $subject = $this->config['subject'];
        $this->config['subject'] = $this->config['error_subject'];

        $this->send();

        $this->config['to'] = $to;
        $this->config['from'] = $sysMail['from'];
        $this->config['subject'] = $subject;
    }
}
