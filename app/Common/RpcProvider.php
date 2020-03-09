<?php declare(strict_types=1);
/**
 * This file is part of Swoft.
 *
 * @link     https://swoft.org
 * @document https://swoft.org/docs
 * @contact  group@swoft.org
 * @license  https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace App\Common;

use ReflectionException;
use Swoft\Bean\Annotation\Mapping\Bean;
use Swoft\Bean\Annotation\Mapping\Inject;
use Swoft\Bean\Exception\ContainerException;
use Swoft\Consul\Agent;
use Swoft\Consul\Exception\ClientException;
use Swoft\Consul\Exception\ServerException;
use Swoft\Rpc\Client\Client;
use Swoft\Rpc\Client\Contract\ProviderInterface;

/**
 * Class RpcProvider
 *
 * @since 2.0
 *
 * @Bean()
 */
class RpcProvider implements ProviderInterface
{
    /**
     * @Inject()
     *
     * @var Agent
     */
    private $agent;

    /**
     * @param Client $client
     *
     * @return array
     * @throws ReflectionException
     * @throws ContainerException
     * @throws ClientException
     * @throws ServerException
     * @example
     * [
     *     'host:port',
     *     'host:port',
     *     'host:port',
     * ]
     */
    public function getList(Client $client): array
    {
        // Get health service from consul
        $services = $this->agent->services();
        $resut  = $services->getResult();

        $client_host = $client->getHost();
        $prot = $client->getPort();
//        var_dump($client_host);
//        var_dump($prot);
        //这里进行了服务发现 进行根据获取到对应的consul 中的服务节点进行处理了 并且进行节点的监康检查
        // rpc-接入了这里然后 提供的也是 1806客户端进行处理的方式 我们这里注册了很多的节点服务 这里可以进行分发获取到对应的标题的客户方
        // 因为rpc-client -连接参数-需要获取对应的 consule主键获取对应的服务列表-然后进行请求对应的数据
        // 我们这里进行了rpc的分发机制
        // 也就是 lib 接口我们都进行了对外提供远程调用
        // 内部的默认提供了一个getListServerProdiver 提供返回服务列表的提供商
        // rpcClient+RpcServerProdiver-提供处理
        // 我们这里进行了监听 就是 Listener全局的服务器监听进行了服务器的注册的信息
        // ，默认的方式我们是启动一个坚硬内部端口进行注册添加服务到 对应的 Consul 中心

        // 每个机器上的所欲的端口信息 服务信息-需要上报注册到-consul-server 节点之上 进行server 处理和监听
        // 服务监控检测 服务我们只需要获取对应的$this->angent 获取服务信息和监控信息
        // 因为在Bean 中默认注册了 consul 这样在客户端就可以进行使用了
        //这里是个客户端进行提供服务发现的处理
        // swoole_rpc 服务 swoole_http 服务 swoole_webscoket
        // 我们通过从consul etcd 中进行获取对应的rpc服务名称的ip:port 地址就可以了
        // 然后我们在客户端进行
        // 我们是来=话我们的客户端 go-rpcclient php-client swoole-phpclient
        // 然后启动之前进行 consul进行查询可用的服务列表sername 名称
        //获取返回的数组列表 然后
        //返回给客户端
        //客户端采用负载均衡的方式 从对应的服务中进行负载代理到 列表中的任意一个
        //按道理说应该 将请求-调用服务器中的路由网关-由Nginx 中间层进行负载代理到对应提供服务的列表中
        //请求到etcd consul
        //有consul etcd 这样的机制去下发到
        //对应的提供服务的rpc服务的进程中去
        // 我们这里模拟的不是网关的层的动态请求做处理的方式
        // 我们是rpcclient-serviceProvider-consul-获取可用的服务-servicename-[]数组ip:poft
        // 我们进行提供服务HTTP客户端集成的方式-rpcclient-consult-provider
        // rpclcient-send-hook-provider
        //rpclient(provider)
        // provider -读取信息 readServiceLists 读取列表
        // 然后读取数据-ProviderReadServiceLists- 处理完成 可读可写的接口实例
        // 写入到什么地方 有各自提供商提供服务
        return ['127.0.0.1:18307','127.0.0.1:18307'];


     //   return  $resut;

//        $services = [
//
//        ];

     //   return $services;
    }
}
