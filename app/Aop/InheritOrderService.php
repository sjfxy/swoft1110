<?php declare(strict_types=1);
namespace App\Aop;
use Swoole\Exception;

class InheritOrderService extends OrderService{
    /**
     * @param string $product_name
     * @param int $quantity
     * @param int $user_id
     * @return mixed
     */
    public function genrateOrder($product_name, $quantity, $user_id):array
    {
       try{
           $result = parent::genrateOrder($product_name,$quantity,$user_id);
           //处理父类方法返回结果 这里使用的是 父类继承然后提供一个oop
           //然后重写调用父类生成 + process_+defal = data
           // 根据返回结果进行相关处理
           return $result;
       }catch (Exception $e){
      //这里采用异步的方式 启动一个写成进行异步的推送到队列+消费
           //采用事件驱动 触发EventHook 机制
           // 采用 listenSer->push-exception->exceptionDist[athcer
           // multi-topic-aop-eception hook error
           // 异常处理 日志处理 邮件处理 在异常发生这个代码连接点的代码片段
           // 通知 Advice
           // 明确了对应的目的
           //而切面 为 对应的确定对应的目的地 一个本身的功能点
           // 一个是切面对应确定的目的地
           // 异步tasker
           // 异常补货
           // 通知
           // 事件
           // EventHook
           // ListenrHook
           //
           throw $e;
       }
    }
    public function run(){
        //$order = new OrderService(); 注释掉就业网的
        //通过继承 或者继承重新对应的-方法 或者使用 Trait
        //进行覆盖对应的可以一个基础类 TraitDb TraitRedis TraitOrder
        // TraitTrade TraitUser TraitComments TraitAdvice TraitAsync
        // NewClass extends OldService -> use NewTypeT4ait 4
        // NewClass extens OldServiice  采用组合发方式
        // NewClass ProcessDealWith GRPC 模块
        $order = new InheritOrderService();
        $order->genrateOrder('MacBook Pro',1,10000);
        // 至此 经过上方的调整后 满足了我们的业务需求 现在我们回顾一下
        // 过程似乎非常繁琐 耦合严重 无法更新和替换 黑色调度 甚至污染了
        //genrateOrder 方法 如果项目中存在 100处 OrderService 类的调用 我们就得找到这100 个地方进行修改
        // 替换 这就是 oop 的思想
        //这时 你或许会想到 中间件 拦截器的类似的方法来解决 其实这些方法本身本质上也是基于 AOP思想而来
        //AOP 是基于 OOP 的补充和延伸
        // AOP 的主要作用是在不侵入原有代码的下添加新的功能
        //我们知道OOP 实际上 就是对我们功能属性 方法做一个抽象封装
       // 能够清晰的划分逻辑单元 但是 OOP 只能够进行纵向的抽象封装 无法很好的解决 横向的重复代码 而AOP 则很好的解决了这一问题
    }

    // 比如 订单类+检查权限+记录日志+相关的订单操作+结束
    //  用户类+检查权限+记录日志+相关用户操作+结束
    // 我们有俩个类 订单类 和 用户类 我们对其相关的功能做了封装 但是 权限检查 日志记录等功能就是在重复的编码
    // 而利用 AOP思想 就可以将这些功能 横向切出去 然后在适当的时候 再将这些功能植入进来
    // 将日志操作 权限检查 进行在合适的位置某个点进行植入即可
    // 这样的模块都可以随便的进行升级和降级 并且可以无感知的进行按照对应的植入的是一个接口约束
    // 并且在运行的时候 都可以随便的进行对其进行更新 或者 封装和继承 多态 组合的方式 进行柔和升级和封装 转义
    // HTTP GRPC RPC UDP 并且异步 协程 都可以不用关系
    // 其实这些本质上是 AOP 的思想 是对 OOP的补充和思想的升华 最精髓的思想是 AOP 进行对应的AS评测提 切面
    // 下面是相关的术语
    // 1. Advice 通知 通知就是植入到目标类连接点的代码一段代码 就是func func handler 叫做功能 也就连接 植入的目标点
    // 的一段代码 叫通知
    // 2. Aspcet 切面 切面由通知和切点组成 通知明确了目的 而切点明确目的地
    // 3. 引介 引介指向一个现有类增加方法或字段属性 引介还可以在不改变现有类代码的情况下实现新的接口
    // 4. 比如简单的上游模块的订单模块 需要对其对应的属性进行不改变对应的破坏类的代码的情况下 进行引入 就是引入对应的
    // 介点 叫做引介 对一个类增加方法或者属性的方式 并且可以在不改变原有的基础之上通过新增加接口新组合的方式 并且监听对应的事件情况下注入我们的通知和注入我们的切点
    // 5.连接点 程序执行的某个钉钉位置 比如 流 程一共 有 四个函数 执行的时候 1-3-2 方法掉赢钱初始化方法前
    // 方法调用前 方法调用后 返回 抛出异常等 允许使用通知的地方都可 称为连接点
    // 比如我们那些模块中那些方法 可以使用通知 使用这些代码连接点 点到点 hook-hook的机制
    // 我们叫连接点
    // 切点 切点 指的是 需要植入目标的方法 假设一个目标对象 类 中 拥有10个方法 需要在其中的三个方法进行植入通知 这个桑耳方法 称为切点
    //代理模式 Proxy 应用通知的对象 详细的内容参见设计模式里面的代理模式 代理实现了切面的业务 Swoft PHP-Praserr 实现 AOP
    // Target 目标对象 被通知的对象类 目标含有真正的业务逻辑 可被无感知的植入
    // Weaing 植入
    // 将切面应用=-目标对象以创建新代理的过程
    // daim opian -mubia -xine
    // 运用声明
    // 声明切面

}

