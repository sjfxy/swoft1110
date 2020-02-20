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
        return ['127.0.0.1:18307','127.0.0.1:18307'];


     //   return  $resut;

//        $services = [
//
//        ];

     //   return $services;
    }
}
