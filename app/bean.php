<?php
/**
 * This file is part of Swoft.
 *
 * @link     https://swoft.org
 * @document https://swoft.org/docs
 * @contact  group@swoft.org
 * @license  https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */
use App\Common\DbSelector;
use App\Process\MonitorProcess;
use Swoft\Crontab\Process\CrontabProcess;
use Swoft\Db\Pool;
use Swoft\Http\Server\HttpServer;
use Swoft\Task\Swoole\SyncTaskListener;
use Swoft\Task\Swoole\TaskListener;
use Swoft\Task\Swoole\FinishListener;
use Swoft\Rpc\Client\Client as ServiceClient;
use Swoft\Rpc\Client\Pool as ServicePool;
use Swoft\Rpc\Server\ServiceServer;
use Swoft\Http\Server\Swoole\RequestListener;
use Swoft\WebSocket\Server\WebSocketServer;
use Swoft\Server\SwooleEvent;
use Swoft\Db\Database;
use Swoft\Redis\RedisDb;
use App\Common\RpcProvider;

return [
    'noticeHandler'      => [
        'logFile' => '@runtime/logs/notice-%d{Y-m-d-H}.log',
    ],
    'applicationHandler' => [
        'logFile' => '@runtime/logs/error-%d{Y-m-d}.log',
    ],
    'logger'            => [
        'flushRequest' => false,
        'enable'       => false,
        'json'         => false,
    ],
    'httpServer'        => [
        'class'    => HttpServer::class,
        'port'     => 18306,
        'listener' => [
             'rpc' => bean('rpcServer'),
            // 'tcp' => bean('tcpServer'),
            // 'ws' => bean('wsServer')
        ],
        'process'  => [
//            'monitor' => bean(MonitorProcess::class)
//            'crontab' => bean(CrontabProcess::class)
        ],
        'on'       => [
//            SwooleEvent::TASK   => bean(SyncTaskListener::class),  // Enable sync task
            SwooleEvent::TASK   => bean(TaskListener::class),  // Enable task must task and finish event
            SwooleEvent::FINISH => bean(FinishListener::class)
        ],
        /* @see HttpServer::$setting */
        'setting' => [
            'task_worker_num'       => 12,
            'task_enable_coroutine' => true,
            'worker_num'            => 6
        ]
    ],
    'httpDispatcher'    => [
        // Add global http middleware
        // 这里可以进行添加对请求进行拦截和过滤的器 也就是切面面向接口中间层 的开发方式
        'middlewares'      => [
            \Swoft\Whoops\WhoopsMiddleware::class,
            \App\Http\Middleware\FavIconMiddleware::class,
            \Swoft\Http\Session\SessionMiddleware::class,
            \Swoft\Swoole\Tracker\Middleware\SwooleTrackerMiddleware::class,
            // \Swoft\Whoops\WhoopsMiddleware::class,
            // Allow use @View tag
            \Swoft\View\Middleware\ViewMiddleware::class,
        ],
        //这里定义了 HeaderFilter 的阶段处理的过程对进行响应头 响应体进行过滤和重定向处理都可以在这个地方处理
        // 上面的是 rewrite access 阶段 重定向和权限的控制阶段
        // onRequest 预定义于都的阶段 可以在 Nginx 这一层进行 负载均衡-提供一个域名-域名注册服务中心-负载管理-到
        // Ec2 服务实例Bean  Ec2QBean  Ec2Bean进行管理即可 但是这个是一个实例 需要一个实例可以运行很多的服务
        // Ext->Ec2-Server-Cluder->NginxIngressControoler->Ingress
        // K8s-Dokcer
        // ElatciSerBancler->进行获取定时的将信息->监控中心->数据分析->通知ebs->进行扩展ec2
        'afterMiddlewares' => [
            \Swoft\Http\Server\Middleware\ValidatorMiddleware::class
        ]
    ],
    'db'                => [
        'class'    => Database::class,
        'dsn'      => 'mysql:dbname=ais;host=47.103.120.221',
        'username' => 'sin',
        'password' => 'sj930826',
        'charset' => 'utf8',
        'prefix'=>'lanhai_',
//        'options'=>[
//            PDO::ATTR_CASE=>PDO::CASE_NATURAL
//        ]
    ],
    'db.pool' => [
        'class'    => Pool::class,
        'database' => bean('db'),
    ],
    'db2'               => [
        'class'      => Database::class,
        'dsn'      => 'mysql:dbname=ais;host=47.103.120.221',
        'username' => 'sin',
        'password' => 'sj930826',
//        'dbSelector' => bean(DbSelector::class)
    ],
    'db2.pool' => [
        'class'    => Pool::class,
        'database' => bean('db2'),
    ],
    'db3'               => [
        'class'    => Database::class,
        'dsn'      => 'mysql:dbname=ais;host=47.103.120.221',
        'username' => 'sin',
        'password' => 'sj930826'
    ],
    'db3.pool'          => [
        'class'    => Pool::class,
        'database' => bean('db3')
    ],
    'migrationManager'  => [
        'migrationPath' => '@database/Migration',
    ],
    'redis'             => [
        'class'    => RedisDb::class,
        'host'     => '127.0.0.1',
        'port'     => 6379,
        'database' => 0,
        'option'   => [
            'prefix' => 'swoft:'
        ]
    ],
    'user'              => [
        'class'   => ServiceClient::class,
        'host'    => '127.0.0.1',
        'port'    => 18307,
        'setting' => [
            'timeout'         => 0.5,
            'connect_timeout' => 1.0,
            'write_timeout'   => 10.0,
            'read_timeout'    => 0.5,
        ],
        'packet'  => bean('rpcClientPacket'),
        'provider' => bean(RpcProvider::class)
    ],
    'user.pool'         => [
        'class'  => ServicePool::class,
        'client' => bean('user'),
    ],
    'rpcServer'         => [
        'class' => ServiceServer::class,
    ],
    'wsServer'          => [
        'class'   => WebSocketServer::class,
        'port'    => 18308,
        'listener' => [
            'rpc' => bean('rpcServer'),
            // 'tcp' => bean('tcpServer'),
        ],
        'on'      => [
            // Enable http handle
            SwooleEvent::REQUEST => bean(RequestListener::class),
            // Enable task must add task and finish event
            SwooleEvent::TASK   => bean(TaskListener::class),
            SwooleEvent::FINISH => bean(FinishListener::class)
        ],
        'debug'   => 1,
        // 'debug'   => env('SWOFT_DEBUG', 0),
        /* @see WebSocketServer::$setting */
        'setting' => [
            'task_worker_num'       => 6,
            'task_enable_coroutine' => true,
            'worker_num'            => 6,
            'log_file' => alias('@runtime/swoole.log'),
        ],
    ],
    // 'wsConnectionManager' => [
    //     'storage' => bean('wsConnectionStorage')
    // ],
    // 'wsConnectionStorage' => [
    //     'class' => \Swoft\Session\SwooleStorage::class,
    // ],
    /** @see \Swoft\WebSocket\Server\WsMessageDispatcher */
    'wsMsgDispatcher' => [
        'middlewares' => [
            \App\WebSocket\Middleware\GlobalWsMiddleware::class
        ],
    ],
    /** @see \Swoft\Tcp\Server\TcpServer */
    'tcpServer'         => [
        'port'  => 18309,
        'debug' => 1,
    ],
    /** @see \Swoft\Tcp\Protocol */
    'tcpServerProtocol' => [
        // 'type' => \Swoft\Tcp\Packer\JsonPacker::TYPE,
        'type' => \Swoft\Tcp\Packer\SimpleTokenPacker::TYPE,
        // 'openLengthCheck' => true,
    ],
    /** @see \Swoft\Tcp\Server\TcpDispatcher */
    'tcpDispatcher' => [
        'middlewares' => [
            \App\Tcp\Middleware\GlobalTcpMiddleware::class
        ],
    ],
    'cliRouter'         => [
        // 'disabledGroups' => ['demo', 'test'],
    ],
    'consul'=>[
        'host'=>'127.0.0.1',
    ],
    'breaker' => [
        'timeout' => 3,
    ]
];
