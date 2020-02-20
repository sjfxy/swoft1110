<?php declare(strict_types=1);
namespace App\Http\Controller;
use Swoft\Bean\Annotation\Mapping\Inject;
use Swoft\Breaker\Annotation\Mapping\Breaker;
use Swoft\Co;
use Swoft\Http\Server\Annotation\Mapping\Controller;
use Swoft\Consul\Agent;
use Swoft\Http\Server\Annotation\Mapping\RequestMapping;
use Swoft\Limiter\Annotation\Mapping\RateLimiter;
use Swoft\Log\Debug;
use Swoole\Coroutine;
use Swoole\Exception;

/**
 * Class AgentController
 * @Controller(prefix="age")
 */
class AgentController
{
    const RPC_EOL = "\r\n\r\n";
    /**
     * @Inject()
     *
     * @var Agent
     */
    private $agent;

    /**
     * @RequestMapping(method={"GET","POST"},route="ge")
     *
     * @RateLimiter(rate=20)
     *
     * @Breaker(fallback="ce")
     */
    public function get()
    {
        try{
            $servers = $this->agent->members();
            return $servers->getResult();
        }catch (Exception $e){
            throw new Exception("22");
        }
    }
    public function ce(){
        $servers = $this->agent->members();
        return $servers->getResult();
    }

    /**
     * @RequestMapping(route="r")
     */
    public function getResults(){
        $request = [
              "member"=>[$this,"get"],
            "checks"=>[$this,"get2"],
            "fail_checks"=>[$this,"get3"],
            "deregiserCheck"=>[$this,"get4"],
            "passCheck"=>[$this,"get5"],
            "services"=>[$this,"se"]
        ];
     return   Co::multi($request,5.0);
    }
    public function se(){
        $s = $this->agent->services();
        return $s->getResult();
    }
    public function get2(){
        $s = $this->agent->checks();
        return $s->getResult();
    }
    public function get3(){
        $s = $this->agent->failCheck("swoft");
        return $s->getResult();
    }
    public function get4(){
        $s = $this->agent->deregisterCheck("swoft");
        return $s->getResult();
    }
    public function get5(){
        $s = $this->agent->passCheck("swoft");
        return $s->getResult();
    }
    function request($host, $class, $method, $param, $version = '1.0', $ext = []) {
        var_dump($host);
        var_dump($method);
        var_dump($param);
        $fp = stream_socket_client($host, $errno, $errstr);
        if (!$fp) {
            throw new Exception("stream_socket_client fail errno={$errno} errstr={$errstr}");
        }

        $req = [
            "jsonrpc" => '2.0',
            "method" => sprintf("%s::%s::%s", $version, $class, $method),
            'params' => $param,
            'id' => '',
            'ext' => $ext,
        ];
        $data = json_encode($req) . self::RPC_EOL;
        fwrite($fp, $data);

        $result = '';
        while (!feof($fp)) {
            $tmp = stream_socket_recvfrom($fp, 1024);

            if ($pos = strpos($tmp, self::RPC_EOL)) {
                $result .= substr($tmp, 0, $pos);
                break;
            } else {
                $result .= $tmp;
            }
        }

        fclose($fp);
        return json_decode($result, true);
    }

    /**
     * @RequestMapping(route="lis")
     */
     public function getLis(){
         $server = $this->agent->services();
         return $server->getResult();
     }
    /**
     * @return mixed
     * @throws Exception
     * @RequestMapping(route="rp")
     */
public function rp(){
     //获取 $host 列表
    Co::set(['hook_flags' => SWOOLE_HOOK_STREAM_FUNCTION]);
  //  \Swoole\Runtime::enableStrictMode();
    $hosts= $this->agent->services()->getResult();
    $host = [];
    foreach($hosts as $ke=>$item){
        if(!$ke=="swoft_rpc"){
            break;
        }
        if($ke == "swoft_rpc"){
            $host[] = $item['Address'].":".$item['Port'];
        }
    }
    $xieyi = "tcp";
    $len = count($host);
//    if($len == 1){
//        $ret = $
//    }
  // $ret =  Co::multi(['request'=>call_user_func([$this,"request"],$xieyi."://".$host[0],\App\Rpc\Lib\UserInterface::class, 'getList',  [1, 2], "1.0")]);
//    $request = [
//        "request"=>call_user_func([$this,"request"],$xieyi."://".$host[0],\App\Rpc\Lib\UserInterface::class, 'getList',  [1, 2], "1.0")
//    ];
    $ret = $this->request('tcp://127.0.0.1:18307', \App\Rpc\Lib\UserInterface::class, 'getList',  [1, 2], "1.0");
    return $ret;
    $chanel = new Coroutine\Channel(1);
    $class_path = \App\Rpc\Lib\UserInterface::class;
    $method = "getList";
    $params = [1,2];
    $version = "1.0";
  //  sgo([$this,"request"],$class_path,$method,$params,$version);
//    foreach($len as $i=>$ke){
//        sgo(function()use($chanel,$xieyi,$ke,$i,$class_path,$method,$params,$version){
//            try {
//                $re = $this->request($xieyi."://".$ke[$i],$class_path,$method,$params,$version);
//                $chanel->push($re);
//            }catch (Throwable $e ){
//                Debug::log('Co multi error(key=%s) is %s', $ke, $e->getMessage());
//                $chanel->push(false);
//            }
//
//    });
//    }
    $timeout = 5.0;
    $response = [];
     $response = $chanel->pop();
//    $re = Co::multi($request,5.0);
    return $response;
// 服务熔断测试完毕 服务注册完毕 服务取消注册监听完毕
// 服务提交到 consul 完毕
}

    /**
     * @RequestMapping(route="h")
     */
    public function hook(){
     //   Co::set(['hook_flags' => SWOOLE_HOOK_STREAM_FUNCTION]);

        \Co\run(function () {
            go(function () {
                $fp1 = stream_socket_client("tcp://www.baidu.com:80", $errno, $errstr, 30);
                $fp2 = stream_socket_client("tcp://www.qq.com:80", $errno, $errstr, 30);
                if (!$fp1) {
                    echo "$errstr ($errno) \n";
                } else {
                    fwrite($fp1, "GET / HTTP/1.0\r\nHost: www.baidu.com\r\nUser-Agent: curl/7.58.0\r\nAccept: */*\r\n\r\n");
                    $r_array = [$fp1, $fp2];
                    $w_array = $e_array = null;
                    $n = stream_select($r_array, $w_array, $e_array, 10);
                    $html = '';
                    while (!feof($fp1)) {
                        $html .= fgets($fp1, 1024);
                    }
                    fclose($fp1);
                }
            });
            echo "here" . PHP_EOL;
        });
    }

}

