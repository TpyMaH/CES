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
 * Class Model_Exec
 */
class Model_Exec extends CModel
{
    protected $_execPath;
    protected $_commandParams;
    protected $_name;
    protected $_prepareCommand;
    protected $_requiredOptions = array();

    /**
     * @param $commandParams
     * @param string $execPath
     */
    public function __construct($commandParams, $execPath = '')
    {
        $this->SetExecPath($execPath);
        $this->_commandParams = $commandParams;
        if (isset($this->_commandParams['options'])) {
            $this->ParseOptions();
        }
    }

    /**
     * @return bool
     */
    public function getHideStatus()
    {
        if (isset($this->_commandParams['hide'])) {
            return $this->_commandParams['hide'];
        }
        return false;
    }

    /**
     * @return bool
     */
    protected function ParseOptions()
    {
        if (isset($this->_commandParams['options'])) {
            $options = $this->_commandParams['options'];
            if (is_array($options)) {
                $options = array_shift($options);
            }
            $this->_commandParams['options'] = str_split($options);
            return true;
        }
        return false;
    }

    /**
     * @return bool
     */
    protected function PrepareOptions()
    {
        if (isset($this->_commandParams['options']) && is_array($this->_commandParams['options'])) {
            $this->_prepareCommand['options'] = array_unique(array_merge($this->_requiredOptions, $this->_commandParams['options']));
        } else {
            $this->_prepareCommand['options'] = $this->_requiredOptions;
        }
        return true;
    }

    /**
     * Prepare options
     */
    protected function ImplodePreparedOptions()
    {
        $this->_prepareCommand['options'] = implode("", $this->_prepareCommand['options']);
        $this->_prepareCommand['options'] = empty($this->_prepareCommand['options']) ? '' : "-" . $this->_prepareCommand['options'];
    }

    /**
     * @param string $execPath
     */
    protected function SetExecPath($execPath = '')
    {
        global $sysExec;
        if (is_array($sysExec) && isset($sysExec[$this->_name]) && isset($sysExec[$this->_name]['path'])) {
            $this->_execPath = $sysExec[$this->_name]['path'];
        } elseif (!empty($execPath)) {
            $this->_execPath = $execPath;
        } else {
            $this->_execPath = $this->_name;
        }
    }

    /**
     * run
     */
    public function run()
    {
        $this->PrepareOptions();
        $this->ImplodePreparedOptions();
    }

    /**
     * @param $command
     * @param bool|false $output
     * @param bool|false $return
     * @param bool|true $flog
     * @param bool|false $code
     * @return bool
     */
    public function DoExec($command, $output = false, &$return = false, $flog = true, &$code = false)
    {
        if (!$output) {
            $command .= " > /dev/null";
        }
        $data['command'] = $command;
        $data['start'] = microtime(TRUE);
        exec($command, $opu, $rv);
        $data['end'] = microtime(TRUE);

        if ($flog) {
            Ces::log()->flog($data);
        }

        if ($return !== FALSE) {
            $return = $opu;
        }
        if ($code !== FALSE) {
            $code = $rv;
        }
        if ($rv > 0) {
            return FALSE;
        } else {
            return TRUE;
        }
    }
}

