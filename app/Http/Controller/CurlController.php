<?php declare(strict_types=1);
namespace App\Http\Controller;
use Co\Http\Client;
use Swoft\Co;
use Swoft\Http\Server\Annotation\Mapping\Controller;
use Swoft\Http\Server\Annotation\Mapping\RequestMapping;
use Swoole\Http\Response;

/**
 * Class CoTest
 *
 * @since 2.0
 * @Controller(prefix="curl")
 */
class CurlController
{
    /**
     * @RequestMapping(route="mu")
     */
    public function testMulti()
    {
        $requests = [
            'method' => [$this, 'requestMethod'],
            'staticMethod' => self::requestMehtodByStatic(),
            'closure' => function () {
                $cli = new Client('www.ais-car.com', 80);
                $cli->get('/index.php?g=Car&m=ScarSearch&a=selectCar');
                $result = $cli->body;
                $cli->close();
                return $result;
            }
        ];

        $response = Co::multi($requests);
        return $response;

    }

    public function requestMethod()
    {
        $cli = new Client('www.ais-car.com', 80);
        $cli->get('/index.php?g=Car&m=ScarSearch&a=selectCar');
        $result = $cli->body;
        $cli->close();

        return $result;
    }

    public static function requestMehtodByStatic()
    {
        $cli = new Client('www.ais-car.com', 80);
        $cli->get('/index.php?g=Car&m=ScarSearch&a=selectCar');
        $result = $cli->body;
        $cli->close();

        return $result;
    }
}