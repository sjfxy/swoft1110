<?php
namespace App\Http\Controller;
use App\Test\Event\Examples\ExamHandler;
use function foo\func;
use Inhere\Event\Event;
use Inhere\Event\EventInterface;
use Inhere\Event\EventManager;
use Inhere\Event\EventManagerAwareTrait;
use Swoft\Http\Server\Annotation\Mapping\Controller;
use Swoft\Http\Server\Annotation\Mapping\RequestMapping;

function exam_handler(EventInterface $event)
{
    $pos = __METHOD__;
    echo "handle the event '{$event->getName()}' on the: $pos \n";
}
class ExamListener1
{
    public function messageSent(EventInterface $event)
    {
        $pos = __METHOD__;

        echo "handle the event '{$event->getName()}' on the: $pos \n";
    }
}
class ExamListener2
{
    public function __invoke(EventInterface $event)
    {
        // $event->stopPropagation(true);
        $pos = __METHOD__;
        echo "handle the event '{$event->getName()}' on the: $pos\n";
    }
}
// create event class
class MessageEvent extends Event
{
    // append property ...
    public $message = 'oo a text';
}
class Mailer
{
    use EventManagerAwareTrait;

    const EVENT_MESSAGE_SENT = 'messageSent';

    public function send($message)
    {
        // ...发送 $message 的逻辑...

        $event          = new MessageEvent(self::EVENT_MESSAGE_SENT);
        $event->message = $message;

        // trigger event
        $this->eventManager->trigger($event);

        // var_dump($event);
    }
}
/**
 * Class AppController
 * @package App\Http\Controller
 * @Controller(prefix="app")
 */
class AppController {
    use EventManagerAwareTrait;
    const ON_START          = 'app.start';
    const ON_STOP           = 'app.stop';
    const ON_BEFORE_REQUEST = 'app.beforeRequest';
    const ON_AFTER_REQUEST = 'app.afterRequest';
    private $em;
    public function __construct()
    {
        $this->em = new EventManager();
        $this->setEventManager($this->em);
        $this->eventManager->trigger(new Event(self::ON_START, [
            'key' => 'val'
        ]));
    }
    public function run()
    {
        $sleep = 0;
        $this->eventManager->trigger(self::ON_BEFORE_REQUEST);

        echo 'request handling ';
        while ($sleep <= 3) {
            $sleep++;
            echo '.';
            sleep(1);
        }
        echo "\n";

        $this->eventManager->trigger(self::ON_AFTER_REQUEST);
    }

    public function __destruct()
    {
        $this->eventManager->trigger(new Event(self::ON_STOP, [
            'key1' => 'val1'
        ]));
    }

    /**
     * @RequestMapping(route="main")
     */
    public function main(){
        $em = $this->em;
     //   $em->attach(Mailer::EVENT_MESSAGE_SENT, 'exam_handler');
        $em->attach(Mailer::EVENT_MESSAGE_SENT, function (EventInterface $event) {
            $pos = __METHOD__;
            echo "handle the event '{$event->getName()}' on the: $pos\n";
        });
        $em->attach(Mailer::EVENT_MESSAGE_SENT, new ExamListener1(), 10);
        $em->attach(Mailer::EVENT_MESSAGE_SENT, new ExamListener2());
        $em->attach(Mailer::EVENT_MESSAGE_SENT, new ExamHandler());

        $em->attach('*', function (EventInterface $event) {
            echo "handle the event '{$event->getName()}' on the global listener.\n";
        });
        //这个lkogin我们进行Loginc方法 new Logic 方法治好 我们把ebemmaneh过去
        // send 里面会主动的进行事件的注册到evnamenger中即可
        //然后其他的进程可以对应的事件进行监听
        //如果监听到对应的事件 则执行脚本参数
        //我们可以lsutebe进行监听需要的事件
        //然后我们设置对应的生成消费者也可以
        //然后每次进行调用之后配置我们需要的执行的get返回依次执行的方式
        //返回对应的x,y 则进行更新对应的文章发不完之后的事件 然后监听到事件 则 进行处理额外的进程处理方式
        // 比如第三方的图片处理 授权的资源清理工作
        //采用异步tasker方式操作
        //采用事件监听的方式操作
        //采用队列消费的方式操作
        //采用推送到服务器的队列reids中操作即可
        //因为每秒中去读取mysql数据文章发布的 添加一个图片的宽和高的测试和数据
        // mysql处理
        //然后redis取读取对应的数据去消费即可 create->redis队列->命令处理器->事件处理器->
        //AMQ队列中去处理
        //事件处理
        //storge
        //去处理的方式
        //最新的查询出来即可
        //异步处理方式

        $mailer = new Mailer();
        $mailer->setEventManager($em);
        $mailer->send('hello, world!');

        //下面是进行
        $em->attach(self::ON_START,function (EventInterface $event){
            var_dump("------------");
           var_dump($event->getName());
        });
    }
}