<?php declare(strict_types=1);
/**
 * This file is part of Swoft.
 *
 * @link     https://swoft.org
 * @document https://swoft.org/docs
 * @contact  group@swoft.org
 * @license  https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 * @description 演示了如何使用熔断的控制器进行logic Moeel 熔断处理
 */

namespace App\Http\Controller;

use App\Model\Logic\BreakerLogic;
use Exception;
use Swoft\Bean\Annotation\Mapping\Inject;
use Swoft\Http\Server\Annotation\Mapping\Controller;
use Swoft\Http\Server\Annotation\Mapping\RequestMapping;

/**
 * Class BreakerController
 *
 * @since 2.0
 *
 * @Controller(prefix="breaker")
 */
class BreakerController
{
    /**
     * @Inject()
     *
     * @var BreakerLogic
     */
    private $logic;

    /**
     * @RequestMapping()
     *
     * @return string
     * @throws Exception
     */
    public function breaked(): string
    {
        //演示了如何属于 Break进行Loic Model Logic Data 的数据函数的熔断处理 @Controoler @RequestMapping @Inject() @var @Break()
        // 函垄断
        return $this->logic->func();
    }

    /**
     * @RequestMapping()
     *
     * @return array
     * @throws Exception
     */
    public function unbreak(): array
    {
        return [$this->logic->func2()];
    }

    /**
     * @RequestMapping()
     *
     * @return string
     * @throws Exception
     */
    public function loopBraker(): string
    {
        return $this->logic->loop();
    }

    /**
     * @RequestMapping()
     *
     * @return string
     * @throws Exception
     */
    public function unFallback(): string
    {
        return $this->logic->unFallback();
    }
    public function Onexception():Exception {
        return $this->logic->funcFallback();
    }

}
