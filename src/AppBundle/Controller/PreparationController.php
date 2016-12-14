<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Model\SchemaGenerator;
use Doctrine\DBAL\Schema\Comparator;
use Symfony\Component\HttpFoundation\JsonResponse;

class PreparationController extends BaseController
{
    /**
     * @Route("/", name="home")
     * @Route("/do/{n}")
     */
    public function doAction($n=self::N,$action="do")
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
     */
    public function mainAction($n=self::N,$l=self::L,$k=self::K,$k0=1)
    {
        $conn = $this->getDoctrine()->getConnection();
        if($k0==1) { $conn->delete('main_1',[1=>1]); }
        $conn->beginTransaction();
        try{
            for($i=$k0;$i<=$k;$i++){                // row in table main
                $content = ['id'=>$i];
                for($j=1;$j<=$n;$j++){            // foreign key of row
                    $content['minor_'.$j.'_id'] = rand(1,$l);
                }
                $conn->insert('main_1', $content);
            }
            $conn->commit();
        } catch(\Exception $e) {
            $conn->rollBack();
            throw $e;
        }
        return new  JsonResponse(['n'=>$n,'l'=>$l,'k0'=>$k0,'k'=>$k]);
    }
}
