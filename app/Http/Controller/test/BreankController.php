<?php
namespace App\Http\Controller\test;
use Swoft\Bean\Annotation\Mapping\Inject;
use Swoft\Http\Message\Request;
use Swoft\Http\Message\Response;
use Swoft\Http\Server\Annotation\Mapping\Controller;
use Swoft\Http\Server\Annotation\Mapping\RequestMapping;
use App\Model\Logic\BreakerLogic;
/**
 * Class BreakController
 * @package App\Http\Controller\test
 * @Controller(prefix="test/break")
 *
 */
class BreakController
{
    /**
     * @Inject()
     *
     * @var BreakerLogic
     */
    private $logic;
    /**
     * @RequestMapping(method={},route="te")
     */
   public function test(Request $request, Response $response){
         $response->withContent("ss");

   }
}