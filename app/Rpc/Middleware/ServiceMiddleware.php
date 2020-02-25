<?php declare(strict_types=1);
/**
 * This file is part of Swoft.
 *
 * @link     https://swoft.org
 * @document https://swoft.org/docs
 * @contact  group@swoft.org
 * @license  https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace App\Rpc\Middleware;

use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Rpc\Server\Contract\MiddlewareInterface;
use Swoft\Rpc\Server\Contract\RequestHandlerInterface;
use Swoft\Rpc\Server\Contract\RequestInterface;
use Swoft\Rpc\Server\Contract\ResponseInterface;
use Swoft\Swoole\Tracker\SwooleTracker;

/**
 * Class ServiceMiddleware
 *
 * @since 2.0
 *
 * @Bean()
 */
class ServiceMiddleware implements MiddlewareInterface
{
    /**
     * @param RequestInterface        $request
     * @param RequestHandlerInterface $requestHandler
     *
     * @return ResponseInterface
     */
    public function process(RequestInterface $request, RequestHandlerInterface $requestHandler): ResponseInterface
    {
//        $swooleTracker = bean(SwooleTracker::class);
//        $swooleTracker->startRpcAnalysis("/","rpc","127.0.0.1");
//这个可以需要使用到的地方单独的额外的设置 因为这个是请求的信息
      //  $swooleTracker->startRpcAnalysis($request->)
        return $requestHandler->handle($request);
    }
}
