<?php declare(strict_types=1);
use App\Services\OrderService;
use Swoft\Aop\Annotation\Mapping\Aspect;
use Swoft\Aop\Annotation\Mapping\PointAnnotation;
use Swoft\Aop\Annotation\Mapping\PointBean;
use Swoft\Aop\Annotation\Mapping\PointExecution;
use Swoft\Http\Server\Annotation\Mapping\RequestMapping;
/**
 * @Aspect(order=1)
 * @PointBean(
 *     include={OrderService::class},
 *     exclude={}
 * )
 * @PointAnnotation(
 *     include={RequestMapping::class},
 *     exclude={}
 * )
 * @PointExecution(
 *     include={OrderService::genrateOrder}
 * )
 */
class DemoAspect {
// order 用于指定优先级 数字越小则优先执行
//声明切点 目标类 必须指定为携带 namespace 的完整路径 或如示例代码 在顶部use
// PointBean 定义目标类切点
// include 需被指定为切点的目标类的集合
// exclude 需排除为切点的目标类的集合
// PointAnnotation 定义注解类切点 所有使用对应注解的方法均会通过该切面类代理
// PointExecution 定义确切的目标类的方法
// include 需被植入的目标类方法集合 支持正则表达式
// exclude 需被排除的目标类方法集合 支持正则表达式
//使用正则表达式时 参数内容必须使用 双引号 " " 包裹 命名空间分隔符 必须使用 \ 转义
// 同时双引号内 必须是类的完整路径
// 以上注解定义的关系为并集 定义排除为并集后的结果 建议为了便于理解和使用 一个切面类尽量 只使用其中一个注解
// 我们进行切面的目标类 Target 对应的类 的结合 对应的方法 注解类 的动画给他要代理类
// 进行使用这个注入的类进行处理
    /**
     * 前置通知
     * @\Swoft\Aop\Annotation\Mapping\Before()
     */
public function beforeAdvice(){

}
    /**
     * @\Swoft\Aop\Annotation\Mapping\After()
     */
    public function afterAdvice(){

    }
    /**
     * 返回通知
     * @\Swoft\Aop\Annotation\Mapping\AfterReturning()
     * @param Joinpoint $joinPoint
     * @return mixed
     */
    public function afterReturnAdvice(\Swoft\Aop\Point\JoinPoint $joinPoint){
        $ret = $joinPoint->getReturn();
        return $ret;
    }

    /**
     * 异常通知
     * @\Swoft\Aop\Annotation\Mapping\AfterThrowing()
     *
     */
    public function afterThrowingAdvice(Throwable $throwable){

    }

    /**
     * @param \Swoft\Aop\Point\ProceedingJoinPoint $proceedingJoinPoint
     * @\Swoft\Aop\Annotation\Mapping\Around()
     * @throws Throwable
     */
    public function aroundAdvice(\Swoft\Aop\Point\ProceedingJoinPoint $proceedingJoinPoint){
        //前置通知
        $ret = $proceedingJoinPoint->proceed();
        return $ret;
    }
    // @Before 前置通知 在目标方法之前执行
    // @After 后置通知 在目标方法之后执行
    // @AfterReturing 返回通知
    // @AfterThrowing 异常通知 目标方法异常时执行
    // @Around 环绕通知 等同于前置通知+加上后置通知 在目标方法之前以及之后执行

}