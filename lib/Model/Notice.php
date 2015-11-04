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
 * Class Model_Notice
 */
class Model_Notice extends CModel
{
    protected $_task;
    protected $_taskList = array();
    protected $_reportData;
    protected $_command;
    protected $_error = false;
    public $sms = true;


    /**
     * @param $task
     */
    public function openTask($task)
    {
        if (!empty($this->_task)) {
            $this->_task['mend'] = microtime(TRUE);
            $this->_task['end'] = date("H:i:s");
            $this->_taskList[] = $this->_task;
            unset($this->_task);
        }
        $this->_task['name'] = $task['info']['name'];
        $this->_task['total'] = isset($task['command']) ? count($task['command']) : 0;
        $this->_task['commandList'] = isset($task['command']) ? $task['command'] : array();
        $this->_task['completed'] = 0;
        $this->_task['mstart'] = microtime(TRUE);
        $this->_task['start'] = date("H:i:s");
    }

    /**
     * @param $name
     */
    public function startCommand($name)
    {
        if (is_array($this->_task['commandList'])) {
            array_shift($this->_task['commandList']);
        }
        $data['name'] = $name;
        $data['mstart'] = microtime(TRUE);
        $data['start'] = date("H:i:s");
        $data['hide'] = false;
        $this->_command = $data;
    }

    /**
     * Hide command
     */
    public function commandHide()
    {
        $this->_command['hide'] = true;
    }

    /**
     * @param $status
     */
    public function commandStatus($status)
    {
        if ($status) {
            $this->_task['completed'] += 1;
        }
        if (!$status) {
            $this->_error = true;
        }
        $this->_command['status'] = $status ? 1 : 0;
    }

    /**
     * set error of task
     */
    public function TaskError()
    {
        $this->_error = true;
    }

    /**
     * @param $return
     */
    public function CommandReturn($return)
    {
        $this->_command['return'] = $return;
    }

    /**
     * End of Command
     */
    public function endCommand()
    {
        $this->_command['mend'] = microtime(TRUE);
        $this->_command['end'] = date("H:i:s");
        $this->_task['commands'][] = $this->_command;
        unset($this->_command);
    }

    /**
     * @return bool
     */
    public function finish()
    {
        if (empty($this->_task) && empty($this->_taskList)) {
            return false;
        }

        if (isset($this->_command) && !isset($this->_command['status'])) {
            $this->_error = true;
        }

        if (isset($this->_command)) {
            $this->endCommand();
        }

        $this->_task['mend'] = microtime(TRUE);
        $this->_task['end'] = date("H:i:s");
        $this->_taskList[] = $this->_task;

        if (!isset(CTask::$config['notice'])) {
            CTask::$config['notice'] = 0;
        }

        if (isset(CTask::$config['notice']) && CTask::$config['notice'] == 4) {
            return false;
        }

        $this->PrepareMessegeHeader();
        $this->PrepareReportData();

        if ($this->_error) {
            $log = $this->log();
        } else {
            $log = array('email' => TRUE, 'sms' => TRUE);
        }

        $mail = new Model_Mail();

        $message = $this->MessegeTemplate();
        $mail->AddMessage($message);
        $mail->AddAttachment(Ces::log()->flogPath(), "application/txt");
        $mail->BuildMessage();

        if (isset(CTask::$config['notice']) && CTask::$config['notice'] != 1) {
            $mail->Send();
        }

        if (isset(CTask::$config['notice']) && CTask::$config['notice'] != 2) {
            if ($this->_error) {
                if ($log['email']) {
                    $mail->SendError();
                }
                $sms = new Model_Sms();
                $smsMessage = 'CES error on - ' . $this->_reportData['header']['hostname'] . ' (' . implode(" ", $this->_reportData['header']['ip']) . ')';
                if ($log['sms']) {
                    $sms->Send($smsMessage);
                }
            }
        }
    }

    /**
     * @return array
     */
    public function log()
    {
        $report = $this->_reportData;
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

        $filename = BACKUP_ROOT . "/tmp/notice/" . md5($report . date("Ymd"));
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
            $first = FALSE;
        } else {
            $first = TRUE;
        }

        $data['global']['first'] = isset($data['global']['first']) ? $data['global']['first'] : time();

        CTask::$config['noticeconf'] = isset(CTask::$config['noticeconf']) ? CTask::$config['noticeconf'] : array('repeat' => true, 'resetinterval' => 2, 'smsperday' => 5);
        $config = CTask::$config['noticeconf'];

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
                    $this->sms = FALSE;
                }
            }
        }
        if ($this->sms) {
            $data['global']['sms'] = true;
        }

        $data['error'][] = serialize($this->_reportData);

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
    public function PrepareReportData()
    {
        $report['global']['total'] = count($this->_taskList);
        $report['global']['completed'] = 0;
        $report['tasks'] = array();
        foreach ($this->_taskList as $key => $value) {
            if (($value['total'] - $value['completed']) == 0) {
                $report['global']['completed'] += 1;
            }
            $report['tasks'][$key] = $value;
        }
        $this->_reportData['global'] = $report['global'];
        $this->_reportData['tasks'] = $report['tasks'];
    }

    /**
     * Prepare headers
     */
    public function PrepareMessegeHeader()
    {
        $exec = new Model_Exec('', 'notice');

        $data = array();

        $exec->DoExec("hostname", TRUE, $data['hostname'], false);
        $data['hostname'] = $data['hostname'][0];

        //$exec->DoExec("ifconfig | grep -B1 \"inet addr\" | awk '{ if ( $1 == \"inet\" ) { print $2 } else if ( $2 == \"Link\" ) { printf \"%s:\" ,$1 } }' | awk -F: '{ print $3 }'", TRUE, $data['ip'], false);
        $exec->DoExec("/sbin/ifconfig -a | grep inet | grep -v '127.0.0.1' | egrep '[[:digit:]]{1,3}\.[[:digit:]]{1,3}\.[[:digit:]]{1,3}\.[[:digit:]]{1,3}' | awk '{print $2}'", TRUE, $data['ip'], false);
        $key = array_search('127.0.0.1', $data['ip']);
        if ($key !== FALSE) {
            unset($data['ip'][$key], $key);
        }

        $exec->DoExec("uptime", TRUE, $data['uptime'], false);
        $data['uptime'] = $data['uptime'][0];

        $this->_reportData['header'] = $data;
    }

    /**
     * @param $time
     * @param string $type
     * @return string
     */
    public function showPeriod($time, $type = 'short')
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
    public function MessegeTemplate()
    {
        $data = $this->_reportData;
        $header = $data['header'];
        $global = $data['global'];
        $tasks = $data['tasks'];
        $messege = "
<html>
    <head>
    </head>
    <body>
        <center>
            <br />
            <br />
            <p><b><span style='color: #660000;'>" . $header['hostname'] . " (" . implode(" ", $header['ip']) . ")</span></b></p>
            <p><b><span style='color: #660000;'>Статиcтика " . date("H:i:s d/m/Y") . "</span></b></p>
            <p><b><span style='color: #660000;'>Uptime " . $header['uptime'] . "</span></b></p>
            <p><b><span style='color: " . ($global['completed'] == $global['total'] ? "green" : "red") . ";'>Выполнено " . $global['completed'] . " из " . $global['total'] . " задач.</span></b></p>
        </center>
        <table border='1' cellspacing='0' cellpadding='0' width='100%' style='width: 100.0%; border-collapse: collapse; border: none;'>
            <thead>
                <tr style='height: 25px;'>
                    <th style='text-align: center;'>№</th>
                    <th style='text-align: center;'>Name</th>
                    <th style='text-align: center;'>Start time</th>
                    <th style='text-align: center;'>End time</th>
                    <th style='text-align: center;'>Performed</th>
                    <th style='text-align: center;'>Total</th>
                    <th style='text-align: center;'>Complited</th>
                    <th style='text-align: center;'>Status</th>
                </tr>
            </thead>
            <tbody>";
        $i = 1;
        foreach ($tasks as $task) {
            $messege .= "<tr style='height: 25px;'>\n";
            $messege .= "<td style='text-align: center;'>" . $i . "</td>\n";
            $messege .= "<td style='text-align: center;'>" . $task['name'] . "</td>\n";
            $messege .= "<td style='text-align: center;'>" . $task['start'] . "</td>\n";
            $messege .= "<td style='text-align: center;'>" . $task['end'] . "</td>\n";
            $messege .= "<td style='text-align: center;'>" . $this->showPeriod($task['mend'] - $task['mstart']) . "</td>\n";
            $messege .= "<td style='text-align: center;'>" . $task['total'] . "</td>\n";
            $messege .= "<td style='text-align: center;'>" . $task['completed'] . "</td>\n";
            $messege .= "<td style='text-align: center;"
                . ($task['total'] != $task['completed'] ? " background:red; color: white;" : "")
                . "'>"
                . ($task['total'] == $task['completed'] ? "OK" : "<b>ERROR</b>") . "</td>\n";
            $messege .= "</tr>\n";
            $i++;
        }

        $messege .= "
            </tbody>
        </table>";
        foreach ($tasks as $task) {
            $messege .= "
            <br />
            <br />
            <table border='1' cellspacing='0' cellpadding='0' width='100%' style='width: 100.0%; border-collapse: collapse; border: none;'>
                <thead>
                    <caption style='line-height:25px; color: white;"
                . ($task['total'] == $task['completed'] ? " background: silver;" : " background: red;")
                . "'><b>task: \""
                . $task['name']
                . "\" ("
                . $task['completed'] . " completed commands of " . $task['total']
                . ")<b></caption>
                    <tr style='height: 25px;'>
                        <th style='text-align: center;'>№</th>
                        <th style='text-align: center;'>Name</th>
                        <th style='text-align: center;'>Start time</th>
                        <th style='text-align: center;'>End time</th>
                        <th style='text-align: center;'>Performed</th>
                        <th style='text-align: center;'>Return</th>
                        <th style='text-align: center;'>Status</th>
                    </tr>
                </thead>
                <tbody>";
            $i = 1;
            foreach ($task['commands'] as $command) {
                if ($command['status'] == 1 && $command['hide'] == true) {
                    $i++;
                    continue;
                } else {
                    $messege .= "<tr style='height: 25px;'>\n";
                    $messege .= "<td style='text-align: center;'>" . $i . "</td>\n";
                    $messege .= "<td style='text-align: center;'>" . $command['name'] . "</td>\n";
                    $messege .= "<td style='text-align: center;'>" . $command['start'] . "</td>\n";
                    $messege .= "<td style='text-align: center;'>" . $command['end'] . "</td>\n";
                    $messege .= "<td style='text-align: center;'>" . $this->showPeriod($command['mend'] - $command['mstart']) . "</td>\n";
                    $messege .= "<td style='text-align: center;'>" . (isset($command['return']) ? $command['return'] : "") . "</td>\n";
                    $messege .= "<td style='text-align: center;" . ($command['status'] ? "" : " background:red; color: white;")
                        . "'>"
                        . ($command['status'] ? "OK" : "<b>ERROR</b>")
                        . "</td>\n";
                    $messege .= "</tr>\n";
                    $i++;
                }
            }
            if (!empty($task['commandList'])) {
                foreach ($task['commandList'] as $command) {
                    $messege .= "<tr style='height: 25px;'>\n";
                    $messege .= "<td style='text-align: center;'>" . $i . "</td>\n";
                    $messege .= "<td style='text-align: center;'>" . $command[0] . "</td>\n";
                    $messege .= "<td style='text-align: center;'></td>\n";
                    $messege .= "<td style='text-align: center;'></td>\n";
                    $messege .= "<td style='text-align: center;'></td>\n";
                    $messege .= "<td style='text-align: center;'></td>\n";
                    $messege .= "<td style='text-align: center; background:red; color: white;'><b>ERROR</b></td>\n";
                    $messege .= "</tr>\n";
                    $i++;
                }
            }
            $messege .= "
                </tbody>
            </table>
            <br />
            <br />";
        }
        $messege .= "
    </body>
</html>";
        return $messege;
    }
}

