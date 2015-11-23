<?php
namespace ces\models\exec;

use \ces\Ces;
use \ces\models\Exec;

class Model_Exec_httpstat extends Model_Exec
{
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


    public function run()
    {
        Ces::notice()->sms = false;

        $currentTaskInfo = Ces::task()->currentTaskInfo();

        $codes = $this->_commandParams['codes'];
        $command = $this->_execPath . " -I '" . $this->_commandParams['what'] . "' |grep HTTP |awk '{print $2}'";
        if ($this->DoExec($command, true, $return)) {
            if (empty($return)) {
                Ces::log()->log("Couldn't resolve host '" . $this->_commandParams['what'] . "' in '" . $this->_name . "' command of '" . $currentTaskInfo['name'] . "' task.", LOG_WARNING);

                if (in_array('zero', $codes['codelist']) && isset($codes['codecommand']['zero']) && !empty($codes['commandlist'][$codes['codecommand']['zero']])) {
                    $this->DoExec($codes['commandlist'][$codes['codecommand']['zero']], true);
                }
                $return = 'zero';
                return FALSE;
            } else {
                $return = $return[0];

            }
            if (in_array($return, $codes['codelist'])) {

                Ces::log()->log("'" . $this->_commandParams['what'] . "' url have problem. '" . $command . "' in '" . $this->_name . "' command of '" . $currentTaskInfo['name'] . "' task.", LOG_WARNING);

                if (isset($codes['codecommand'][$return]) && !empty($codes['commandlist'][$codes['codecommand'][$return]])) {
                    $this->DoExec($codes['commandlist'][$codes['codecommand'][$return]], true);
                }
                $funcReturn = FALSE;
            } else {
                $funcReturn = TRUE;
            }
        } else {
            Ces::log()->log("Can't exec '" . $command . "' in '" . $this->_name . "' command of '" . $currentTaskInfo['name'] . "' task.", LOG_WARNING);
            $funcReturn = FALSE;
        }
        $return = "code: " . (is_array($return) ? 'array' : $return);
        if (isset($this->_commandParams['comment'])) {
            $return .= " (" . $this->_commandParams['comment'] . ")";
        }
        Ces::notice()->commandReturn($return);

        return $funcReturn;
    }
}

?>
