<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 18.12.16
 * Time: 02:15
 */

namespace Util;

use Doctrine\DBAL\Driver\Connection;

class Logger
{
    /**
     * @param Integer $n
     * @param Integer $l
     * @param Integer $k
     * @param Double $t
     * @param String $machineId
     * @param String $message
     * @param Connection $conn
     */
    public function log($n,$l,$k,$t,$machineId,$message,$conn)
    {
        $conn->insert('log', array(
            "n"=>$n,
            "l"=>$l,
            "k"=>$k,
            "t"=>$t,
            "machine_id"=>$machineId,
            "message"=>$message
        ));
    }
}