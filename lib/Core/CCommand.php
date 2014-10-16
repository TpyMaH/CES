<?php
class MA_CCommand{
    protected $_commandList;
    protected $_currentCommand;
    protected $_currentCommandClass;
    protected $_currentCommandObj;
    protected $_finishedCommands;
            
    public function __construct($commandList) {
        if (!isset($commandList['command'])){
            $commandList['command'] = array();
        }
        $this->_commandList = $commandList['command'];
    }
    
    public function Next(){
        $currentTaskInfo = MA::Task()->CurrentTaskInfo();
        if (is_object($this->_currentCommandObj)){
            MA::Log()->Log("End '" . $this->_currentCommand[0] . "' command of '" . $currentTaskInfo['name'] . "' task.");
        }
        if (is_array($this->_commandList) && !empty($this->_commandList)){
            $command = array_shift($this->_commandList);
            if (is_array($command) && !empty($command)){
                $this->_currentCommand = $command;
                MA::Log()->log("Start '" . $this->_currentCommand[0] . "' command of '" . $currentTaskInfo['name'] . "' task.");
                $this->_currentCommandObj = $this->setCommandObj();
                return $this->_currentCommandObj;
            }
            else {
                MA::Log()->log("Params error in unknow command of '" . $currentTaskInfo['name'] . "' task.", LOG_WARNING);
            }
        }
        else {
            return false;
        }
    }
    
    public function CommandClass(){
        return $this->_currentCommandClass;
    }


    protected function setCommandObj(){
        $currentTaskInfo = MA::Task()->CurrentTaskInfo();
        $types = array(
            'tar' => 'MA_Model_Exec_tar',
            'cp' => 'MA_Model_Exec_cp',
            'mv' => 'MA_Model_Exec_mv',
            'rm' => 'MA_Model_Exec_rm',
            'killall' => 'MA_Model_Exec_killall',
            'mysqldump' => 'MA_Model_Exec_mysqldump',
            'exec' => 'MA_Model_Exec_exec',
            'bz2' => 'MA_Model_Exec_bz2',
            'df' => 'MA_Model_Exec_df',
            'raid' => 'MA_Model_Exec_raid',
            'ps' => 'MA_Model_Exec_ps',
            'httpstat' => 'MA_Model_Exec_httpstat',
            'du' => 'MA_Model_Exec_du',
            'ping' => 'MA_Model_Exec_ping',
        );
        $command = $this->_currentCommand;
        if (is_array($command) && !empty($command)){
            $commandClass = array_shift($command);
            $this->_currentCommandClass = $commandClass;
            if (isset($types[$commandClass])){
                $class = $types[$commandClass];
                return new $class($command);
            }
            else {
                MA::notice()->TaskError();
                MA::Log()->log("Unknow command - '" . $this->_currentCommand[0] . "' of '" . $currentTaskInfo['name'] . "' task.", LOG_WARNING);
                return false;
            }
        }
        else {
            MA::Log()->log("Params error in unknow command of '" . $currentTaskInfo['name'] . "' task.", LOG_WARNING);
            return false;
        }
    }
    
}
?>
