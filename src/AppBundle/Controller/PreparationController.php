<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Model\SchemaGenerator;
use Doctrine\DBAL\Schema\Comparator;
use Symfony\Component\HttpFoundation\JsonResponse;

class PreparationController
{
    /**
     * @Route("/", name="home")
     * @Route("/do/{n}")
     */
    public function doAction($action="do")
    {
        $conn = $this->getDoctrine()->getConnection();
        $schema = (new SchemaGenerator($n))->generate();

        $comparator = new Comparator();
        $queries = $comparator->compare($conn->getSchemaManager()->createSchema(), $schema)->toSql($conn->getDatabasePlatform());

        if($action=="do"){
            foreach($queries as $query) {
                $conn->prepare($query)->execute();
            }
        }

        return new JsonResponse(
            [
                "alter"=>$queries,
            ]
        );
    }

    /**
     * @Route("/show")
     * @Route("/show/{n}")
    */
    public function showAction($n=self::N)
    {
        return $this->doAction($n,"show");
    }

    /**
     * @Route("/minor")
     * @Route("/minor/{n}/{l}")
     */
    public function minorAction($n=self::N,$l=self::L)
    {
        $conn = $this->getDoctrine()->getConnection();
        for($i=1;$i<=$n;$i++){
            $conn->delete('minor_'.$i,[1=>1]);
            for($j=1;$j<=$l;$j++){
                $conn->insert('minor_'.$i, array('id' => $j));
            }
        }
        return new  JsonResponse(['n'=>$n, 'l'=>$l]);
    }

    /**
     * @Route("/main")
     * @Route("/main/{n}/{l}/{k}")
     * @Route("/main/{n}/{l}/{k0}/{k}")
     * @Route("/main/{n}/{l}/{k0}/{k}/{main}/{transaction}")
     */
    public function mainAction($n=self::N,$l=self::L,$k=self::K,$k0=1,$main=1,$transaction=true)
    {
        file_put_contents("ok.log","");

        $conn = $this->getDoctrine()->getConnection();
        file_put_contents("ok.log","Before delete\n", FILE_APPEND | LOCK_EX);
        if($k0==1) { $conn->delete('main_'.$main,[1=>1]); }
        file_put_contents("ok.log","After delete\n", FILE_APPEND | LOCK_EX);
        if($k>1e4) {
//            set_time_limit(0);
//            ini_set("max_execution_time", 0);
        }
        if($transaction) {$conn->beginTransaction();}
//        try{
            for($i=$k0;$i<=$k;$i++){                // row in table main
                $content = ['id'=>$i];
                for($j=1;$j<=$n;$j++){            // foreign key of row
                    $content['minor_'.$j.'_id'] = rand(1,$l);
                }
                $conn->insert('main_'.$main, $content);
                if($i%1e2==0){
                    file_put_contents("ok.log",".", FILE_APPEND | LOCK_EX);
//                    echo ".";
                    if($i%5e3==0){
                        file_put_contents("ok.log"," | ".$i."\n", FILE_APPEND | LOCK_EX);
//                        echo ;
                    }
                }
            }
            file_put_contents("ok.log","Before commit\n", FILE_APPEND | LOCK_EX);
            if($transaction) {$conn->commit();}
            file_put_contents("ok.log","After commit\n", FILE_APPEND | LOCK_EX);
//        } catch(\Exception $e) {
//            if($transaction) {$conn->rollBack();}
//            throw $e;
//        }
        file_put_contents("ok.log","Before Return\n", FILE_APPEND | LOCK_EX);
//        echo ;
//        die("ok");
//        return new \HttpResponse("oko");
        return new JsonResponse(['n'=>$n,'l'=>$l,'k0'=>$k0,'k'=>$k]);
    }

    /**
     * @Route("/file")
     */
    public function fileAction()
    {
        file_put_contents("ok.log","");
        for($i=1;$i<=10;$i++)
        {
            file_put_contents("ok.log","ok".$i."\n", FILE_APPEND | LOCK_EX);
            sleep(2);
        }

        return new JsonResponse([]);
    }
}
