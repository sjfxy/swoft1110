<?php declare(strict_types=1);
/**
 * This file is part of Swoft.
 *
 * @link     https://swoft.org
 * @document https://swoft.org/docs
 * @contact  group@swoft.org
 * @license  https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace App\Exception\Handler;

use Swoft\Error\Annotation\Mapping\ExceptionHandler;
use Swoft\Http\Message\Response;
use Swoft\Http\Server\Exception\Handler\AbstractHttpErrorHandler;
use Swoft\Log\Helper\CLog;
use Swoft\Log\Helper\Log;
use Throwable;
use function get_class;
use function sprintf;
use const APP_DEBUG;
use Swoft\Whoops\WhoopsHandler;

/**
 * Class HttpExceptionHandler
 *
 * @ExceptionHandler(\Throwable::class)
 */
class HttpExceptionHandler extends AbstractHttpErrorHandler
{
    /**
     * @param Throwable $e
     * @param Response  $response
     *
     * @return Response
     */
    public function handle2(Throwable $e, Response $response): Response
    {
        // Log error message
        Log::error($e->getMessage());
        CLog::error('%s. (At %s line %d)', $e->getMessage(), $e->getFile(), $e->getLine());

        // Debug is false
        if (!APP_DEBUG) {
            return $response->withStatus(500)->withContent($e->getMessage());
        }

        $data = [
            'code'  => $e->getCode(),
            'error' => sprintf('(%s) %s', get_class($e), $e->getMessage()),
            'file'  => sprintf('At %s line %d', $e->getFile(), $e->getLine()),
            'trace' => $e->getTraceAsString(),
        ];

        // Debug is true
        return $response->withData($data);
    }
    public function handle(Throwable $e, Response $response): Response
    {
        $request = context()->getRequest();
        if ($request->getUriPath() === '/favicon.ico') {
            return $response->withStatus(404);
        }
        // Log
        CLog::error($e->getMessage());
        // Debug is false
        if (!APP_DEBUG) {
            return $response
                ->withStatus(500)
                ->withContent($e->getMessage());
        }
        // Debug is true
        $whoops  = bean(WhoopsHandler::class);
        $content = $whoops->run($e, $request);
        return $response->withContent($content);
    }
}
