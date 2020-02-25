<?php declare(strict_types=1);
namespace App\Http\Controller\ad;
use App\Rpc\Lib\Ad\AdInterface;
use App\Rpc\Lib\Ad\CeInterface;
use Exception;
use Swoft\Co;
use Swoft\Http\Message\Request;
use Swoft\Http\Message\Response;
use Swoft\Http\Server\Annotation\Mapping\Controller;
use Swoft\Http\Server\Annotation\Mapping\RequestMapping;
use Swoft\Limiter\Annotation\Mapping\RateLimiter;
use Swoft\Rpc\Client\Annotation\Mapping\Reference;
use App\Rpc\Lib\UserInterface;
use Swoft\Validator\Annotation\Mapping\Validate;

/**
 * Class AdController
 *
 * @since 2.0
 *
 * @Controller(prefix="ad/ad")
 */
class AdController
{



    /**
     * @Reference(pool="user.pool")
     *
     * @var UserInterface
     */
    private $userService;

    /**
     * @Reference(pool="user.pool", version="1.2")
     *
     * @var UserInterface
     */
    private $userService2;

    /**@Reference(pool="user.pool",version="1.2")
     *
     * @var CeInterface
     */
    private $ceService;

    /**
     * @Reference(pool="user.pool")
     *
     * @var AdInterface
     */
    private $adService;

    /**
     * @Reference(pool="user.pool",version="2.1")
     *
     * @var AdInterface
     */

    private $adService2;

    /**
     * @RequestMapping("getList")
     *
     * @return array
     */
    public function getList(): array
    {
        $result  = $this->userService->getList(12, 'type');
        $result2 = $this->userService2->getList(12, 'type');

        return [$result, $result2];
    }

    /**
     * @RequestMapping("returnBool")
     *
     * @return array
     */
    public function returnBool(): array
    {
        $result = $this->userService->delete(12);

        if (is_bool($result)) {
            return ['bool'];
        }

        return ['notBool'];
    }

    /**
     * @RequestMapping()
     *
     * @return array
     */
    public function bigString(): array
    {
        $string = $this->userService->getBigContent();

        return ['string', strlen($string)];
    }

    /**
     * @RequestMapping()
     *
     * @return array
     */
    public function sendBigString(): array
    {
        $content = Co::readFile(__DIR__ . '/../../Rpc/Service/big.data');

        $len    = strlen($content);
        $result = $this->userService->sendBigContent($content);
        return [$len, $result];
    }

    /**
     * @RequestMapping()
     *
     * @return array
     */
    public function returnNull(): array
    {
        $this->userService->returnNull();
        return [null];
    }

    /**
     * @RequestMapping()
     *
     * @return array
     *
     * @throws Exception
     */
    public function exception(): array
    {
        $this->userService->exception();

        return ['exception'];
    }

    /**
     * @RequestMapping("getFinance")
     *
     *
     * @RateLimiter(rate=20,fallback="getFinaceFall")
     *
     * @return array
     */
    public function getFinance():array {
        $status = context()->getRequest()->get("status",1);
        $status=  intval($status);
        $data = $this->adService->getFinance($status);
        return $data;
    }

    public function getFinanceFall(){
        return [
            'fallRate'=>context()->getRequest()->withHeader("Content-Type","text/html")
        ];
    }

    /**
     * @RequestMapping(route="getIndexAdd",method={"GET","POST"})
     *
     * @RateLimiter(rate=20,fallback="getRateFail")
     *
     * @return array|null
     */
    public function getIndexAd():?array {
        return $this->adService->getIndexAd(1,"id,img");
    }
    public function getRateFail():array {
        return ["exception"=>"rate is 20 qps"];
    }

    /**
     * @RequestMapping()
     *
     * @RateLimiter(rate=10,fallback="FailBack")
     *
     * @return array|null
     */
    public function getIndex():?array {
        return $this->adService->getIndex([],"fangfa,img",3);
    }
    public function FailBack(){
        return [];
    }

    /**
     * @RequestMapping(route="getlistold")
     *
     * @RateLimiter(rate=100,fallback="getListOdlFal")
     *
     * @return array|null
     */
    public function getListOld(Request $request,Response $response):?Response
    {
        $type = $request->input("type");
        $type = intval($type);
        // charset=utf-8
        $response->withHeader("charset","utf-8");
        if (empty($type)) {
            $response->withStatus(-1,"type 必须传递");
            return  $response->withContent("type 类型必须传递");

        }
          //return $this->adService->getHandlerold($type,1);
        // 不允许返回 data 所有对象必须返回 Response 根节点对象
        return $response->withData($this->adService->getHandlerold($type));
    }


    /**@RateLimiter(rate=40)
     *
     * @return array
     */
    public function getListOdlFal():array {
        return [];
    }

    /**
     * @RequestMapping(route="common",method={"GET","POST"})
     *
     *
     * @RateLimiter(rate=100,fallback="commonfa")
     *
     * @param Request $request
     * @param Response $response
     * @return null|Response
     */
    public function getCommonAd(Request $request,Response $response):?Response
    {
      $post_data =$request->input();
      if(empty($post_data['type'])){
          $content = json(-1,"type 必须传递");
        //  return $content;
         return $response->withContent($content);
      }
      $typeMapping = [24,132,134,133,132];
      $turnDescription = ["车金融IOS","道路救援","车金融","车生活","车服务"];
      $turnDescription = array_combine($typeMapping,$turnDescription);
      if(!in_array($post_data['type'],$typeMapping)){
        return  $response->withContent(json(-1,"type 传递错误",$turnDescription));
      }
     return $response->withContent(json(1,'ok',$this->adService->getCommon(intval($post_data['type']))));
    }
    public function commonfa():?Response{
        $error  = context()->getSwooleServer()->getLastError();
        $data = ['code'=>10010,"rate"=>context()->getRequest()->getRequestTarget(),"error"=>$error];
        return context()->getResponse()->withData($data);
    }

}
