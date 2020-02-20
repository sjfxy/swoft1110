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

use App\Model\Logic\RequestBean;
use App\Model\Logic\RequestBeanTwo;
use Swoft\Bean\BeanFactory;
use Swoft\Co;
use Swoft\Http\Server\Annotation\Mapping\Controller;
use Swoft\Http\Server\Annotation\Mapping\RequestMapping;

/**
 * Class BeanController
 *
 * @since 2.0
 *
 * @Controller(prefix="bean")
 *
 */
class BeanController
{
    /**
     * @RequestMapping()
     *
     * @return array
     */
    public function request(): array
    {
        $id = (string)Co::tid();

        /** @var RequestBean $request */
        $request = BeanFactory::getRequestBean('requestBean', $id);
        return $request->getData();
    }

    /**
     * @return array
     *
     * @RequestMapping()
     */
    public function requestClass(): array
    {
        $id = (string)Co::tid();

        /* @var RequestBeanTwo $request */
        $request = BeanFactory::getRequestBean(RequestBeanTwo::class, $id);
        return $request->getData();
    }
    // 采用的是 Co::tid() 顶级协程id
    // Bean 的名称 Request 请求类型 name
    // class:path 进行注入需要的bean 即可
    // 这个是获取比如数据库Db 让第三方库进行注入Bean 的生命周期 注入tid 然后进行每次的携程的id 的包装释放内存 全局属性的静态属性的内存释放
    //演示了 BeanController+BeanFactory::getRequestBean name class_path params
    //
    // BeanFactory::getRequestBean()
}
