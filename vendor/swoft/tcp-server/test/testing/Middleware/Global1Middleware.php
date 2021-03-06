<?php declare(strict_types=1);

namespace SwoftTest\Tcp\Server\Testing\Middleware;

use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Tcp\Server\Contract\MiddlewareInterface;
use Swoft\Tcp\Server\Contract\RequestHandlerInterface;
use Swoft\Tcp\Server\Contract\RequestInterface;
use Swoft\Tcp\Server\Contract\ResponseInterface;

/**
 * Class Global1Middleware
 *
 * @Bean()
 */
class Global1Middleware implements MiddlewareInterface
{
    /**
     * @param RequestInterface        $request
     * @param RequestHandlerInterface $handler
     *
     * @return ResponseInterface
     */
    public function process(RequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $start = '>global ';

        $resp = $handler->handle($request);
        $old  = $resp->getContent();

        $resp->setContent($start . $old . ' global>');

        return $resp;
    }
}
