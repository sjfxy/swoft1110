<?php declare(strict_types=1);
/**
 * This file is part of Swoft.
 *
 * @link     https://swoft.org
 * @document https://swoft.org/docs
 * @contact  group@swoft.org
 * @license  https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace App\Http\Controller;

use App\Rpc\Lib\UserInterface;
use Exception;
use Swoft\Breaker\Annotation\Mapping\Breaker;
use Swoft\Co;
use Swoft\Http\Server\Annotation\Mapping\Controller;
use Swoft\Http\Server\Annotation\Mapping\RequestMapping;
use Swoft\Limiter\Annotation\Mapping\RateLimiter;
use Swoft\Log\Helper\CLog;
use Swoft\Rpc\Client\Annotation\Mapping\Reference;
use Swoft\Rpc\Exception\RpcException;
use Swoft\Rpc\Server\Exception\Handler\RpcErrorHandler;
use Swoft\Rpc\Server\Exception\RpcServerException;

/**
 * Class RpcController
 *
 * @since 2.0
 *
 * @Controller()
 */
class RpcController
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
    //这里测试一下如果对外服务器或者自己内部服务器提供的方法 但是启动了俩个进程 我的进程存在但是从服务器选择的没有

    /**
     * @RequestMapping(route="break")
     *
     * @RateLimiter(rate=20,fallback="routeFail")
     *
     *@Breaker(fallback="breakproc")
     */
    public function BreakProcess(){
       // @Breaker(fallback="breakproc")
              try{
                  //@Breaker服务熔断 定义在全局的时候 用来捕获 因为rpc调用时的异常我们可以判断对应的异常类型是什么
                    //throw new Exception("服务器出现异常");
                  $res = $this->userService->exception2();//因为这个是服务器抛出了异常 然后我们在调用的时候捕获了异常
                  var_dump($res);
              }catch (Exception $exception){
                      Co::create(function ()use ($exception){
                         CLog::info($exception->getMessage());
                      });
                     throw new Exception($exception->getMessage());
              }catch (\RuntimeException $exception){
                  var_dump($exception->getMessage());
              }catch (RpcServerException $exception){
                  var_dump($exception->getMessage());
              }catch (RpcException $rpcException){
                  var_dump($rpcException->getMessage());
              }
    }
    //前端服务限流处理
    public function routFail(){
         return ["前端每秒的测试次数太多"];
    }
    //服务器自己调用的时候 如果出现异常的熔断处理
    //这个用来进行补货异常比如请求第三方服务器的时候补货了异常
    public function breakproc(){
            return json_encode(array("code"=>300,"message"=>"服务器异常内部错误"),320);
    }

    /**
     * @RequestMapping(route="getOrderinfo",method={"GET","POST"},params={"id"="\d+"})
     *
     * @RateLimiter(rate=2000,default=1000,fallback="orderFengBacnk")
     *
     * @Breaker(fallback="orderback")
     */
    public function GetOrderInfo(){
               $redis = Co::readFile("222.txt");//这个都会被Breaker捕获到 不会导致问题差生
               try{

               }catch (Exception $exception){
                   var_dump($exception->getMessage());
               }
           //throw new \PDOException("22");
    }
    public function orderback(){
        return 222;
    }
    // @Breaker 可以捕获第三方调用过程的中的异常 比如 提供微服务的服务下线了 dump了 规定时间内没有
    //返回调用微服务延迟了 失败了次数超过三次同一个请求的fd在规定时间内客户端的请求ip在redis_pol
    //存在可三次则进行切换过去
    // @Breaker 也捕获我们本省的方法内部返回的错误

}
