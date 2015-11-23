<?php
namespace ces\models\exec;

use \ces\Ces;
use \ces\models\Exec;

class Model_Exec_mv extends Model_Exec_cp{
    public function __construct($data) {
        parent::__construct($data);
        $this->_name = 'mv';
        $this->SetExecPath();
    }
}
?>
