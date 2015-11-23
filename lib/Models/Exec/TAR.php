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
 * Class TAR
 * @package ces\models\exec
 */
class TAR extends Exec
{
    /**
     * @inheritdoc
     */
    public function __construct($data)
    {
        $this->_name = 'tar';
        $this->_requiredOptions = array(
            'c',
            'p',
            'l',
            'f',
        );

        $commandParams['file'] = array_shift($data);
        $commandParams['path'] = array_shift($data);
        if (is_array($data) && !empty($data)) {
            $commandParams['options'] = array_shift($data);
        }
        if (is_array($data) && !empty($data)) {
            $commandParams['comment'] = array_shift($data);
        }
        if (is_array($data) && !empty($data)) {
            $commandParams['userCommand'] = array_shift($data);
        }
        if (is_array($data) && !empty($data)) {
            $commandParams['ignoreCommand'] = array_shift($data);
        }
        if (is_array($data) && !empty($data)) {
            $commandParams['hide'] = array_shift($data);
            unset($data);
        }
        parent::__construct($commandParams);
    }

    /**
     * @inheritdoc
     */
    protected function implodePreparedOptions()
    {
        $options = "-";
        foreach ($this->prepareCommand['options'] as $option) {
            switch ($option) {
                case 'c':
                    $options .= 'c';
                    break;
                case 'z':
                    $options .= 'z';
                    break;
                case 'j':
                    $options .= 'j';
                    break;
                case 'p':
                    $options .= 'p';
                    break;
                case 'l':
                    $options .= 'l';
                    break;
                case 'f':
                    $options .= 'f';
                    break;
            }
        }
        if (array_search("f", $this->_requiredOptions)) {
            $options = str_replace("f", "", $options) . "f";
        }
        $this->prepareCommand['options'] = $options;
    }

    public function run()
    {
        $currentTaskInfo = Ces::task()->currentTaskInfo();

        $this->PrepareOptions();
        $this->implodePreparedOptions();

        $command = "cd " . $this->commandParams['path'] . " && ";
        if (isset($this->commandParams['userCommand']) && $this->commandParams['userCommand'] === true) {
            $command .= $this->execPath . " " . implode("", $this->commandParams['options']);
        } else {
            $command .= $this->execPath . " " . $this->prepareCommand['options']
                . " " . $this->commandParams['file'] . " .";
        }

        if ($this->doExec($command, false, $return, true, $code)) {
            $funcReturn = true;
        } else {
            $message = "Can't exec '" . $command . "' in 'tar' command of '" . $currentTaskInfo['name'] . "' task.";
            Ces::log()->log($message, LOG_WARNING);
            $funcReturn = false;
        }
        $return = "code: " . $code;
        if (isset($this->commandParams['comment'])) {
            $return .= " (" . $this->commandParams['comment'] . ")";
        }
        Ces::notice()->commandReturn($return);
        if (isset($this->commandParams['ignoreCommand']) && $this->commandParams['ignoreCommand'] === true) {
            $funcReturn = true;
        }
        return $funcReturn;
    }
}
