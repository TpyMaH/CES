<?php

/**
 * Class MA_Model_Exec
 */
class MA_Model_Exec extends Ma_CModel
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
     * Run
     */
    public function Run()
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
            MA::Log()->flog($data);
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

