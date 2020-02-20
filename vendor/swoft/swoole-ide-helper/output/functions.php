<?php /** @noinspection ALL - For disable PhpStorm check */

namespace Swoole\Coroutine {

    /**
     * @param callable $fn
     * @param ...$args
     * @return mixed
     */
    function run(callable $fn, ...$args){}
    
    
}

namespace Co {

    /**
     * @param callable $fn
     * @param ...$args
     * @return mixed
     */
    function run(callable $fn, ...$args){}
    
    
}

/**
 * @return string
 */
function swoole_version(): string{}

/**
 * @return int
 */
function swoole_cpu_num(): int{}

/**
 * @return string
 */
function swoole_last_error(): string{}

/**
 * 协程DNS查询
 * @param string $domain_name 域名
 * @param float $timeout 设置超时, 单位为秒
 * @return string|false 失败返回false，可使用swoole_errno和swoole_last_error得到错误信息
 */
function swoole_async_dns_lookup_coro(string $domain_name, float $timeout = null){}

/**
 * @param array $settings
 * @return mixed
 */
function swoole_async_set(array $settings){}

/**
 * @param callable $func
 * @param ...$params
 * @return mixed
 */
function swoole_coroutine_create(callable $func, ...$params){}

/**
 * @param callable $callback
 * @return mixed
 */
function swoole_coroutine_defer(callable $callback){}

/**
 * @param callable $func
 * @param ...$params
 * @return mixed
 */
function go(callable $func, ...$params){}

/**
 * @param callable $callback
 * @return mixed
 */
function defer(callable $callback){}

/**
 * @param array $read_array
 * @param array $write_array
 * @param array $error_array
 * @param float $timeout
 * @return mixed
 */
function swoole_client_select(array $read_array, array $write_array, array $error_array, float $timeout = null){}

/**
 * @param array $read_array
 * @param array $write_array
 * @param array $error_array
 * @param float $timeout
 * @return mixed
 */
function swoole_select(array $read_array, array $write_array, array $error_array, float $timeout = null){}

/**
 * @param string $process_name
 * @return mixed
 */
function swoole_set_process_name(string $process_name){}

/**
 * @return mixed
 */
function swoole_get_local_ip(){}

/**
 * @return mixed
 */
function swoole_get_local_mac(){}

/**
 * @param int $errno
 * @param int $error_type
 * @return string
 */
function swoole_strerror(int $errno, int $error_type = 1): string{}

/**
 * @return mixed
 */
function swoole_errno(){}

/**
 * @param mixed $data
 * @param $type
 * @return mixed
 */
function swoole_hashcode($data, $type = null){}

/**
 * @param string $filename
 * @return mixed
 */
function swoole_get_mime_type(string $filename){}

/**
 * @return mixed
 */
function swoole_clear_dns_cache(){}

/**
 * @return mixed
 */
function swoole_internal_call_user_shutdown_begin(){}

/**
 * @param int $fd
 * @param callable $read_callback
 * @param callable $write_callback
 * @param $events
 * @return mixed
 */
function swoole_event_add(int $fd, callable $read_callback, callable $write_callback = null, $events = null){}

/**
 * @param int $fd
 * @return mixed
 */
function swoole_event_del(int $fd){}

/**
 * @param int $fd
 * @param callable $read_callback
 * @param callable $write_callback
 * @param $events
 * @return mixed
 */
function swoole_event_set(int $fd, callable $read_callback = null, callable $write_callback = null, $events = null){}

/**
 * @param int $fd
 * @param $events
 * @return mixed
 */
function swoole_event_isset(int $fd, $events = null){}

/**
 * @return mixed
 */
function swoole_event_dispatch(){}

/**
 * @param callable $callback
 * @return mixed
 */
function swoole_event_defer(callable $callback){}

/**
 * @param callable $callback
 * @param $before
 * @return mixed
 */
function swoole_event_cycle(callable $callback, $before = null){}

/**
 * @param int $fd
 * @param mixed $data
 * @return mixed
 */
function swoole_event_write(int $fd, $data){}

/**
 * @return mixed
 */
function swoole_event_wait(){}

/**
 * @return mixed
 */
function swoole_event_exit(){}

/**
 * @param array $settings
 * @return mixed
 */
function swoole_timer_set(array $settings){}

/**
 * @param int $ms
 * @param callable $callback
 * @return mixed
 */
function swoole_timer_after(int $ms, callable $callback){}

/**
 * @param int $ms
 * @param callable $callback
 * @return mixed
 */
function swoole_timer_tick(int $ms, callable $callback){}

/**
 * @param int $timer_id
 * @return mixed
 */
function swoole_timer_exists(int $timer_id){}

/**
 * @param int $timer_id
 * @return mixed
 */
function swoole_timer_info(int $timer_id){}

/**
 * @return mixed
 */
function swoole_timer_stats(){}

/**
 * @return mixed
 */
function swoole_timer_list(){}

/**
 * @param int $timer_id
 * @return mixed
 */
function swoole_timer_clear(int $timer_id){}

/**
 * @return mixed
 */
function swoole_timer_clear_all(){}

/**
 * @param string $string
 * @return \Swoole\StringObject
 */
function _string(string $string = ''): \Swoole\StringObject{}

/**
 * @param array $array
 * @return \Swoole\ArrayObject
 */
function _array(array $array = []): \Swoole\ArrayObject{}

/**
 * @param string $string
 * @return \Swoole\StringObject
 */
function swoole_string(string $string = ''): \Swoole\StringObject{}

/**
 * @param array $array
 * @return \Swoole\ArrayObject
 */
function swoole_array(array $array = []): \Swoole\ArrayObject{}

/**
 * @param array $array
 * @param string $key
 * @param $default_value
 * @return mixed
 */
function swoole_array_default_value(array $array, string $key, $default_value = ''){}

/**
 * @param string $command
 * @param $output
 * @param $returnVar
 * @return mixed
 */
function swoole_exec(string $command, $output = null, $returnVar = null){}

/**
 * @param string $cmd
 * @return mixed
 */
function swoole_shell_exec(string $cmd){}

/**
 * @param $url
 * @return \Swoole\Curl\Handler
 */
function swoole_curl_init($url = null): \Swoole\Curl\Handler{}

/**
 * @param \Swoole\Curl\Handler $obj
 * @param $opt
 * @param $value
 * @return bool
 */
function swoole_curl_setopt(\Swoole\Curl\Handler $obj, $opt, $value): bool{}

/**
 * @param \Swoole\Curl\Handler $obj
 * @param $array
 * @return bool
 */
function swoole_curl_setopt_array(\Swoole\Curl\Handler $obj, $array): bool{}

/**
 * @param \Swoole\Curl\Handler $obj
 * @return mixed
 */
function swoole_curl_exec(\Swoole\Curl\Handler $obj){}

/**
 * @param \Swoole\Curl\Handler $obj
 * @return ?string
 */
function swoole_curl_multi_getcontent(\Swoole\Curl\Handler $obj): ?string{}

/**
 * @param \Swoole\Curl\Handler $obj
 * @return void
 */
function swoole_curl_close(\Swoole\Curl\Handler $obj): void{}

/**
 * @param \Swoole\Curl\Handler $obj
 * @return string
 */
function swoole_curl_error(\Swoole\Curl\Handler $obj): string{}

/**
 * @param \Swoole\Curl\Handler $obj
 * @return int
 */
function swoole_curl_errno(\Swoole\Curl\Handler $obj): int{}

/**
 * @param \Swoole\Curl\Handler $obj
 * @return void
 */
function swoole_curl_reset(\Swoole\Curl\Handler $obj): void{}

/**
 * @param \Swoole\Curl\Handler $obj
 * @param int $opt
 * @return mixed
 */
function swoole_curl_getinfo(\Swoole\Curl\Handler $obj, int $opt = 0){}

