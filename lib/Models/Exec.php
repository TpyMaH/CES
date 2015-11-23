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

/**
 * Class Model_Exec
 */
class Exec extends Model
{
    protected $execPath;
    protected $commandParams;
    protected $name;
    protected $prepareCommand;
    protected $requiredOptions = array();

    /**
     * @param $commandParams
     * @param string $execPath
     */
    public function __construct($commandParams, $execPath = '')
    {
        $this->setExecPath($execPath);
        $this->commandParams = $commandParams;
        if (isset($this->commandParams['options'])) {
            $this->parseOptions();
        }
    }

    /**
     * @return bool
     */
    public function getHideStatus()
    {
        if (isset($this->commandParams['hide'])) {
            return $this->commandParams['hide'];
        }
        return false;
    }

    /**
     * @return bool
     */
    protected function parseOptions()
    {
        if (isset($this->commandParams['options'])) {
            $options = $this->commandParams['options'];
            if (is_array($options)) {
                $options = array_shift($options);
            }
            $this->commandParams['options'] = str_split($options);
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    protected function prepareOptions()
    {
        if (isset($this->commandParams['options']) && is_array($this->commandParams['options'])) {
            $this->prepareCommand['options'] = array_unique(array_merge($this->requiredOptions, $this->commandParams['options']));
        } else {
            $this->prepareCommand['options'] = $this->requiredOptions;
        }
        return true;
    }

    /**
     * Prepare options
     */
    protected function implodePreparedOptions()
    {
        $this->prepareCommand['options'] = implode("", $this->prepareCommand['options']);
        $this->prepareCommand['options'] = empty($this->prepareCommand['options']) ? '' : "-" . $this->prepareCommand['options'];
    }

    /**
     * @param string $execPath
     */
    protected function setExecPath($execPath = '')
    {
        global $sysExec;
        if (is_array($sysExec) && isset($sysExec[$this->name]) && isset($sysExec[$this->name]['path'])) {
            $this->execPath = $sysExec[$this->name]['path'];
        } elseif (!empty($execPath)) {
            $this->execPath = $execPath;
        } else {
            $this->execPath = $this->name;
        }
    }

    /**
     * run
     */
    public function run()
    {
        $this->prepareOptions();
        $this->implodePreparedOptions();
    }

    /**
     * @param $command
     * @param bool|false $output
     * @param bool|false $return
     * @param bool|true $flog
     * @param bool|false $code
     * @return bool
     */
    public function doExec($command, $output = false, &$return = false, $flog = true, &$code = false)
    {
        if (!$output) {
            $command .= " > /dev/null";
        }
        $data['command'] = $command;
        $data['start'] = microtime(true);
        exec($command, $execOutput, $execReturn);
        $data['end'] = microtime(true);

        if ($flog) {
            Ces::log()->flog($data);
        }

        if ($return !== false) {
            $return = $execOutput;
        }
        if ($code !== false) {
            $code = $execReturn;
        }
        if ($execReturn > 0) {
            return false;
        } else {
            return true;
        }
    }
}

