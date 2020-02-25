<?php declare(strict_types=1);
/**
 * This file is part of Swoft.
 *
 * @link     https://swoft.org
 * @document https://swoft.org/docs
 * @contact  group@swoft.org
 * @license  https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace App\Task\Task;

use Swoft\Log\Debug;
use Swoft\Log\Error;
use Swoft\Task\Annotation\Mapping\Task;
use Swoft\Task\Annotation\Mapping\TaskMapping;

/**
 * Class TestTask
 *
 * @since 2.0
 *
 * @Task(name="rpclog")
 */
class RpcLogTask
{
    /**
     * @TaskMapping(name="list")
     *
     * @param int    $id
     * @param string $default
     *
     * @return array
     */
    public function getList(int $id, string $default = 'def'): array
    {
        return [
            'list'    => [1, 3, 3],
            'id'      => $id,
            'default' => $default
        ];
    }

    /**
     * @TaskMapping()
     *
     * @param int $id
     *
     * @return bool
     */
    public function delete(int $id): bool
    {
        if ($id > 10) {
            return true;
        }

        return false;
    }

    /**
     * @TaskMapping()
     *
     * @param string $name
     *
     * @return null
     */
    public function returnNull(string $name)
    {
        return null;
    }

    /**
     * @TaskMapping()
     *
     * @param string $name
     */
    public function returnVoid(string $name): void
    {
        return;
    }

    /**
     * @TaskMapping(name="log")
     */
    public function log(\Exception $e){
        if (!APP_DEBUG) {
            // just show error message
            $error = Error::new($e->getCode(), $e->getMessage(), null);
        } else {
            $message = sprintf(' %s At %s line %d', $e->getMessage(), $e->getFile(), $e->getLine());
            $error   = Error::new($e->getCode(), $message, null);
        }
        Debug::log('Rpc server error(%s)', $e->getMessage());
    }
}
