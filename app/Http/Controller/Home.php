<?php declare(strict_types=1);
namespace App\Http\Controller;
use Swoft\Http\Server\Annotation\Mapping\Controller;
use Swoft\Http\Server\Annotation\Mapping\RequestMapping;
use Swoft\Http\Message\Request;

/**
 * Class Home
 * @Controller(prefix="home")
 */
class Home {
    /**
     *该方法路由地址/home/index
     * @RequestMapping(route="/index",method="get")
     * @param Request $request
     */
    public function index(Request $request){

    }
}