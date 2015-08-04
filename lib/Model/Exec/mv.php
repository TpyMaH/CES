<?php
class MA_Model_Exec_mv extends MA_Model_Exec_cp{
    public function __construct($data) {
        parent::__construct($data);
        $this->_name = 'mv';
        $this->SetExecPath();
    }
}
?>
