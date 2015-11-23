<?php
namespace ces\models\exec;

use \ces\Ces;
use \ces\models\Exec;

class Model_Exec_raid extends Model_Exec
{
    public function __construct($data)
    {
        $this->_name = 'raid';

        $commandParams = array();
        if (is_array($data) && !empty($data)) {
            $commandParams['hide'] = array_shift($data);
            unset($data);
        }
        parent::__construct($commandParams);
    }

    public function run()
    {

        $data['command'] = $this->_name;
        $data['start'] = microtime(TRUE);


        $currentTaskInfo = Ces::task()->currentTaskInfo();

        if (!is_resource(($handle = @fopen("/proc/mdstat", "rb")))) {
            Ces::log()->log("Can't exec '" . $this->_name . "' command of '" . $currentTaskInfo['name'] . "' task.", LOG_WARNING);
            return false;
        }
        $flag = false;
        $i = 0;
        $j = 0;
        while (!feof($handle)) {
            $tmp = trim(fgets($handle, 8192));
            if (preg_match("/\[(\d+)\/(\d+)\]/", $tmp, $matches)) {
                $i++;
            }
            if (preg_match("/\[(\d+)\/(\d+)\]/", $tmp, $matches) && ($matches[1] != $matches[2])) {
                $flag = true;
                //break;
                $j++;
            }
        }
        fclose($handle);

        Ces::notice()->commandReturn(($i - $j) . '/' . $i);

        $data['end'] = microtime(TRUE);
        Ces::log()->flog($data);

        if (!$flag) {
            $funcReturn = TRUE;
        } else {
            Ces::log()->log("Can't exec '" . $this->_name . "' command of '" . $currentTaskInfo['name'] . "' task.", LOG_WARNING);
            $funcReturn = FALSE;
        }

        return $funcReturn;
    }
}

?>
