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

use \ces\models\Exec;

/**
 * Class MV
 * @package ces\models\exec
 */
class MV extends CP
{
    /**
     * @inheritdoc
     */
    public function __construct($data)
    {
        parent::__construct($data);
        $this->_name = 'mv';
        $this->setExecPath();
    }
}
