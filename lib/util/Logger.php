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
     * @param String $v
     * @param String $message
     * @param Connection $conn
     */
    public function log($n,$l,$k,$t,$v,$message,$conn)
    {
        $conn->insert('log', array(
            "n"=>$n,
            "l"=>$l,
            "k"=>$k,
            "t"=>$t,
            "v"=>$v,
            "message"=>$message
        ));
    }
}