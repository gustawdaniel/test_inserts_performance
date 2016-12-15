<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 14.12.16
 * Time: 11:35
 */

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class MeasureController extends BaseController
{
//    /**
//     * @Route("/main")
//     * @Route("/main/{n}/{l}/{k}")
//     * @Route("/main/{n}/{l}/{k0}/{k}")
//     * @Route("/main/{n}/{l}/{k0}/{k}/{main}/{transaction}")
//     */
//    public function mainAction($n=self::N,$l=self::L,$k=self::K,$k0=1,$main=1,$transaction=true)
//    {
//        $conn = $this->getDoctrine()->getConnection();
//        if($k0==1) { $conn->delete('main_'.$main,[1=>1]); }
//        if($transaction) {$conn->beginTransaction();}
//        try{
//            for($i=$k0;$i<=$k;$i++){                // row in table main
//                $content = ['id'=>$i];
//                for($j=1;$j<=$n;$j++){            // foreign key of row
//                    $content['minor_'.$j.'_id'] = rand(1,$l);
//                }
//                $conn->insert('main_'.$main, $content);
//            }
//            if($transaction) {$conn->commit();}
//        } catch(\Exception $e) {
//            if($transaction) {$conn->rollBack();}
//            throw $e;
//        }
//        return new  JsonResponse(['n'=>$n,'l'=>$l,'k0'=>$k0,'k'=>$k]);
//    }
}