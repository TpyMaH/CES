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
namespace ces\models\exec;

use \ces\Ces;
use \ces\models\Exec;

/**
 * Class Model_Exec_httpstat
 * @package ces\models\exec
 */
class HttpStat extends Exec
{
    /**
     * @inheritdoc
     */
    public function __construct($data)
    {
        $this->_name = 'httpstat';

        $commandParams['what'] = array_shift($data);

        if (is_array($data) && !empty($data)) {
            $commandParams['codes'] = $this->prepareCodes(array_shift($data));
        }
        if (is_array($data) && !empty($data)) {
            $commandParams['comment'] = array_shift($data);
        }
        if (is_array($data) && !empty($data)) {
            $commandParams['hide'] = array_shift($data);
            unset($data);
        }
        parent::__construct($commandParams, 'curl');
    }

    /**
     * @param $codes
     * @return array
     */
    protected function prepareCodes($codes)
    {
        $codecommand = array();
        $codelist = array();
        $commandlist = array();

        $codes = is_array($codes) ? $codes : array($codes);
        $i = 0;
        foreach ($codes as $value) {
            if (!is_array($value)) {
                break;
            }
            $i++;
            $commandlist[$i] = empty($value['command']) ? '' : $value['command'];
            foreach ($value['codes'] as $code) {
                if ($code == 'zero') {
                    $codelist[] = 'zero';
                    $codecommand['zero'] = $i;
                    continue;
                }
                $code = explode('-', $code);
                if (count($code) >= 2) {
                    $from = (int)trim($code[0]);
                    $to = (int)trim($code[1]);
                    if (($from >= 100 && $from <= 600) || ($to >= 100 && $to <= 600)) {
                        for ($j = $from; $j <= $to; $j++) {
                            $codelist[] = $j;
                            $codecommand[$j] = $i;
                        }
                    }
                    unset($from, $to);
                } elseif (count($code) == 1) {
                    $codelist[] = $code[0];
                    $codecommand[$code[0]] = $i;
                }
            }
        }
        return array(
            'codecommand' => $codecommand,
            'codelist' => $codelist,
            'commandlist' => $commandlist
        );
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        Ces::notice()->sms = false;

        $currentTaskInfo = Ces::task()->currentTaskInfo();

        $codes = $this->commandParams['codes'];
        $command = $this->execPath . " -I '" . $this->commandParams['what'] . "' |grep HTTP |awk '{print $2}'";
        $return = null;
        if ($this->doExec($command, true, $return)) {
            if (empty($return)) {
                $message = "Couldn't resolve host '" . $this->commandParams['what'] . "' in '"
                    . $this->_name . "' command of '" . $currentTaskInfo['name'] . "' task.";
                Ces::log()->log($message, LOG_WARNING);

                if (in_array('zero', $codes['codelist'])
                    && isset($codes['codecommand']['zero'])
                    && !empty($codes['commandlist'][$codes['codecommand']['zero']])
                ) {
                    $this->doExec($codes['commandlist'][$codes['codecommand']['zero']], true);
                }
                return false;
            } else {
                $return = $return[0];
            }
            if (in_array($return, $codes['codelist'])) {
                $message = "'" . $this->commandParams['what'] . "' url have problem. '" . $command . "' in '"
                    . $this->_name . "' command of '" . $currentTaskInfo['name'] . "' task.";
                Ces::log()->log($message, LOG_WARNING);

                if (isset($codes['codecommand'][$return])
                    && !empty($codes['commandlist'][$codes['codecommand'][$return]])
                ) {
                    $this->doExec($codes['commandlist'][$codes['codecommand'][$return]], true);
                }
                $funcReturn = false;
            } else {
                $funcReturn = true;
            }
        } else {
            $message = "Can't exec '" . $command . "' in '"
                . $this->_name . "' command of '" . $currentTaskInfo['name'] . "' task.";
            Ces::log()->log($message, LOG_WARNING);
            $funcReturn = false;
        }
        $return = "code: " . (is_array($return) ? 'array' : $return);
        if (isset($this->commandParams['comment'])) {
            $return .= " (" . $this->commandParams['comment'] . ")";
        }
        Ces::notice()->commandReturn($return);

        return $funcReturn;
    }
}
