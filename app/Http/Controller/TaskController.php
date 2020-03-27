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

use Inhere\Event\Event;
use Inhere\Event\EventManager;
use Swoft\Breaker\Annotation\Mapping\Breaker;
use Swoft\Http\Message\Request;
use Swoft\Http\Message\Response;
use Swoft\Http\Server\Annotation\Mapping\Controller;
use Swoft\Http\Server\Annotation\Mapping\RequestMapping;
use Swoft\Task\Exception\TaskException;
use Swoft\Task\Task;
use MongoDB;
use SwoftTest\Event\Testing\TestHandler;
use Swoole\IDEHelper\StubGenerators\Swoole;

/**
 * Class TaskController
 *
 * @since 2.0
 *
 * @Controller(prefix="task")
 */
class TaskController
{
    /**
     * @RequestMapping()
     *
     * @return array
     * @throws TaskException
     */
    public function getListByCo(): array
    {
        return Task::co('testTask', 'list', [12]);
    }

    /**
     * @RequestMapping(route="deleteByCo")
     *
     * @return array
     * @throws TaskException
     */
    public function deleteByCo(): array
    {
        $data = Task::co('testTask', 'delete', [12]);
        if (is_bool($data)) {
            return ['bool'];
        }

        return ['notBool'];
    }

    /**
     * @RequestMapping()
     *
     * @return array
     * @throws TaskException
     */
    public function getListByAsync(): array
    {
        $data = Task::async('testTask', 'list', [12]);

        return [$data];
    }

    /**
     * @RequestMapping(route="deleteByAsync")
     *
     * @return array
     * @throws TaskException
     */
    public function deleteByAsync(): array
    {
        $data = Task::async('testTask', 'delete', [12]);

        return [$data];
    }

    /**
     * @RequestMapping()
     *
     * @return array
     * @throws TaskException
     */
    public function returnNull(): array
    {
        $result = Task::co('testTask', 'returnNull', ['name']);
        return [$result];
    }

    /**
     * @RequestMapping()
     *
     * @return array
     * @throws TaskException
     */
    public function returnVoid(): array
    {
        $result = Task::co('testTask', 'returnVoid', ['name']);
        return [$result];
    }

    /**
     * @RequestMapping()
     *
     * @return array
     * @throws TaskException
     */
    public function syncTask(): array
    {
        $result  = Task::co('sync', 'test', ['name']);
        $result2 = Task::co('sync', 'testBool');
        $result3 = Task::co('sync', 'testNull');

        $data[] = $result;
        $data[] = $result2;
        $data[] = $result3;
        return $data;
    }

    /**
     * @RequestMapping(route="yii")
     * @Breaker(fallback="bac")
     */
    public function Ce(){
        //此方法提供了服务熔断的过程 就是 因为Mogodb一旦底层的shut_register_shut_down捕获了异常则都可进行捕获
        //还需要测试的是挂载
        //OrderController UserControoler 都是属于 namespace下面的如果使用拦截器我们看会怎么处理方式
       //require_once "../../../vendor/autoload.php";
       $client = new  MongoDB\Client();
       $client->insertMany();
       //测试对应的composer机制

    }
    public function bac(){
        //
      return "服务出现异常";
    }

    /**
     * @RequestMapping(route="sin")
     */
    public function sin(){
        return "22";
    }

    /**
     * @RequestMapping(route="ev")
     *
     */
    public function Event(){
       //测试事件监听器处理方式
        try{
            $em = new EventManager();//事件调度器进行事件的管理器进管理的方式
            //所有事件的注册 监听器的注册 事件就是生产者 监听器就是消费者 进行对应的感兴趣的事件进行消费处理
            //然后进行调度底层的调度都是调取器去管理事件 去路由和异常处理的事件网关的层
            // 我们进行设置一下监听器
            //监听器 允许是 1.funtion 函数 2.闭包 [$this,$method] [$classs_path,$method] 3.一个监听器的类
            //1 .function 监听的事件名称是 发送邮件
            //监听了 登录成功 登录失败 第三方授权登录成功 TOKEN认证之后 session过期之后
            //上传本地图片成功之后
            //查询订单的交易之后
            //很多的事件进行进行触发对应的事件
            //比如 我触发这个事件即可
            //我这个因为对应的事件进行触发也就是当查询按到数组的key我们调用这个回调函数的调度由调度器处理
            sgo(function ()use ($em){
                 $em->attach("LoginSuces",function (Event $event){
                    var_dump($event->getParams());
                    var_dump($event->getTarget());
                 });
            });
            //发送邮件的事件处理器
            sgo(function ()use ($em){
                $em->attach("messageSent",function (Event $event){
                    $message= $event->getParams();
                    var_dump($message);
                });
            });
            //这里进行了注册对应的事件管理器了
            //闭包

        }catch (\Exception $exception){
            var_dump($exception->getMessage());
        }

    }
    public function Myfunction(Event $event){
       $message= $event->getParams();
       var_dump($message);
    }
    //内部捕获如果上面监听器出错 进行服务熔断和服务降级处理

    public function EventBack(){
        try{
            var_dump(context()->getServer()->getLastError());
            //这里进行从服务降级去进行调用队列的事件处理器触发本地的方式去触发
            //如果出现异常则进行服务降级 服务熔断和服务转接的策略方式处理
        }catch (\Exception $exception){

        }
    }
}
