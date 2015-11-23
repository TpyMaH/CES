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
 * Class Raid
 * @package ces\models\exec
 */
class Raid extends Exec
{
    /**
     * @inheritdoc
     */
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

    /**
     * @inheritdoc
     */
    public function run()
    {
        $data['command'] = $this->_name;
        $data['start'] = microtime(true);

        $currentTaskInfo = Ces::task()->currentTaskInfo();

        if (!is_resource(($handle = @fopen("/proc/mdstat", "rb")))) {
            $message = "Can't exec '" . $this->_name
                . "' command of '" . $currentTaskInfo['name'] . "' task.";
            Ces::log()->log($message, LOG_WARNING);
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

        $data['end'] = microtime(true);
        Ces::log()->flog($data);

        if (!$flag) {
            $funcReturn = true;
        } else {
            $message = "Can't exec '" . $this->_name . "' command of '" . $currentTaskInfo['name'] . "' task.";
            Ces::log()->log($message, LOG_WARNING);
            $funcReturn = false;
        }

        return $funcReturn;
    }
}
