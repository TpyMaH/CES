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
 * Class Notice
 * @package ces\models
 */
class Notice extends Model
{
    protected $task;
    protected $taskList = array();
    protected $reportData;
    protected $command;
    protected $error = false;

    public $sms = true;

    /**
     * @param $task
     */
    public function openTask($task)
    {
        if (!empty($this->task)) {
            $this->task['mend'] = microtime(true);
            $this->task['end'] = date("H:i:s");
            $this->taskList[] = $this->task;
            unset($this->task);
        }
        $this->task['name'] = $task['info']['name'];
        $this->task['total'] = isset($task['command']) ? count($task['command']) : 0;
        $this->task['commandList'] = isset($task['command']) ? $task['command'] : array();
        $this->task['completed'] = 0;
        $this->task['mstart'] = microtime(true);
        $this->task['start'] = date("H:i:s");
    }

    /**
     * @param $name
     */
    public function startCommand($name)
    {
        if (is_array($this->task['commandList'])) {
            array_shift($this->task['commandList']);
        }
        $data['name'] = $name;
        $data['mstart'] = microtime(true);
        $data['start'] = date("H:i:s");
        $data['hide'] = false;
        $this->command = $data;
    }

    /**
     * Hide command
     */
    public function commandHide()
    {
        $this->command['hide'] = true;
    }

    /**
     * @param $status
     */
    public function commandStatus($status)
    {
        if ($status) {
            $this->task['completed'] += 1;
        }
        if (!$status) {
            $this->error = true;
        }
        $this->command['status'] = $status ? 1 : 0;
    }

    /**
     * set error of task
     */
    public function taskError()
    {
        $this->error = true;
    }

    /**
     * @param $return
     */
    public function commandReturn($return)
    {
        $this->command['return'] = $return;
    }

    /**
     * End of Command
     */
    public function endCommand()
    {
        $this->command['mend'] = microtime(true);
        $this->command['end'] = date("H:i:s");
        $this->task['commands'][] = $this->command;
        unset($this->command);
    }

    /**
     * Finish
     */
    public function finish()
    {
        if (empty($this->task) && empty($this->taskList)) {
            return;
        }

        if (isset($this->command) && !isset($this->command['status'])) {
            $this->error = true;
        }

        if (isset($this->command)) {
            $this->endCommand();
        }

        $this->task['mend'] = microtime(true);
        $this->task['end'] = date("H:i:s");
        $this->taskList[] = $this->task;

        if (!isset(Task::$config['notice'])) {
            Task::$config['notice'] = 0;
        }

        if (isset(Task::$config['notice']) && Task::$config['notice'] == 4) {
            return;
        }

        $this->prepareMessegeHeader();
        $this->prepareReportData();

        if ($this->error) {
            $log = $this->log();
        } else {
            $log = array('email' => true, 'sms' => true);
        }

        $mail = new Mail();

        $message = $this->messegeTemplate();
        $mail->addMessage($message);
        $mail->addAttachment(Ces::log()->flogPath(), "application/txt");
        $mail->buildMessage();

        if (isset(Task::$config['notice']) && Task::$config['notice'] != 1) {
            $mail->send();
        }

        if (isset(Task::$config['notice']) && Task::$config['notice'] != 2) {
            if ($this->error) {
                if ($log['email']) {
                    $mail->sendError();
                }
                $sms = new Sms();
                $smsMessage = 'CES error on - ' . $this->reportData['header']['hostname']
                    . ' (' . implode(" ", $this->reportData['header']['ip']) . ')';
                if ($log['sms']) {
                    $sms->send($smsMessage);
                }
            }
        }
    }

    /**
     * @return array
     */
    public function log()
    {
        $report = $this->reportData;
        unset($report["header"]);

        foreach ($report['tasks'] as $k => $v) {
            unset(
                $report['tasks'][$k]['start'],
                $report['tasks'][$k]['mstart'],
                $report['tasks'][$k]['end'],
                $report['tasks'][$k]['mend']
            );
            foreach ($v['commands'] as $k1 => $v1) {
                unset(
                    $report['tasks'][$k]['commands'][$k1]['start'],
                    $report['tasks'][$k]['commands'][$k1]['mstart'],
                    $report['tasks'][$k]['commands'][$k1]['end'],
                    $report['tasks'][$k]['commands'][$k1]['mend']
                );
            }
        }
        $report = serialize($report);

        $filename = Ces::$basePath . "/tmp/notice/" . md5($report . date("Ymd"));
        if (!is_dir(dirname($filename))) {
            mkdir(dirname($filename), 0777, true);
        }

        if (is_file($filename)) {
            $data = file_get_contents($filename);
            $data = unserialize($data);
        } else {
            $data = array();
        }

        if (isset($data['global']['first'])) {
            $first = false;
        } else {
            $first = true;
        }

        $data['global']['first'] = isset($data['global']['first']) ? $data['global']['first'] : time();

        if (!isset(Task::$config['noticeconf'])) {
            Task::$config['noticeconf'] = array('repeat' => true, 'resetinterval' => 2, 'smsperday' => 5);

        }
        $config = Task::$config['noticeconf'];

        if ($config['repeat']) {
            $email = true;
        } else {
            if ($first) {
                $email = true;
            } elseif (($data['global']['first'] + ($config['resetinterval'] * 3600)) < time()) {
                $email = true;
                $data['global']['sms'] = false;
            } else {
                $email = false;
                if (isset($data['global']['sms']) && $data['global']['sms']) {
                    $this->sms = false;
                }
            }
        }
        if ($this->sms) {
            $data['global']['sms'] = true;
        }

        $data['error'][] = serialize($this->reportData);

        $data = serialize($data);

        file_put_contents($filename, $data);

        return array(
            'email' => $email,
            'sms' => $this->sms,
        );
    }

    /**
     * Prepare the report data
     */
    public function prepareReportData()
    {
        $report['global']['total'] = count($this->taskList);
        $report['global']['completed'] = 0;
        $report['tasks'] = array();
        foreach ($this->taskList as $key => $value) {
            if (($value['total'] - $value['completed']) == 0) {
                $report['global']['completed'] += 1;
            }
            $report['tasks'][$key] = $value;
        }
        $this->reportData['global'] = $report['global'];
        $this->reportData['tasks'] = $report['tasks'];
    }

    /**
     * Prepare headers
     */
    public function prepareMessegeHeader()
    {
        $exec = new Exec('', 'notice');

        $data = array();

        $exec->doExec("hostname", true, $data['hostname'], false);
        $data['hostname'] = $data['hostname'][0];


        $command = "/sbin/ifconfig -a | grep inet | grep -v '127.0.0.1' |"
            . " egrep '[[:digit:]]{1,3}\.[[:digit:]]{1,3}\.[[:digit:]]{1,3}\.[[:digit:]]{1,3}' | awk '{print $2}'";
        $exec->doExec($command, true, $data['ip'], false);
        $key = array_search('127.0.0.1', $data['ip']);
        if ($key !== false) {
            unset($data['ip'][$key], $key);
        }

        $exec->doExec("uptime", true, $data['uptime'], false);
        $data['uptime'] = $data['uptime'][0];

        $this->reportData['header'] = $data;
    }

    /**
     * @param $time
     * @return string
     */
    public function showPeriod($time)
    {
        $floortime = floor($time);
        if ($floortime < 60) {
            return sprintf("%.2F", $time) . "s";
        }
        $hour = floor($floortime / 3600);
        $sec = $floortime - ($hour * 3600);
        $min = floor($sec / 60);
        $sec = $sec - ($min * 60);
        $msec = substr(sprintf("%.2F", $time), -3);

        $return = '';
        if ($hour > 0) {
            if ($return == "") {
                $return .= $hour . ":";
            } else {
                $return .= sprintf('%02d:', $hour);
            }
        }
        if ($min > 0) {
            if ($return == "") {
                $return .= $min . ":";
            } else {
                $return .= sprintf('%02d:', $min);
            }
        }
        $return .= ($return == '' ? $sec : sprintf('%02d', $sec)) . $msec . "s";
        return $return;
    }

    /**
     * @return string
     */
    public function messegeTemplate()
    {
        ob_start();
        require __DIR__ . '/messageTemplate.phtml';
        $message = ob_get_contents();
        ob_end_clean();
        return $message;
    }
}
