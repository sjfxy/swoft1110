<?php declare(strict_types=1);
/**
 * This file is part of Swoft.
 *
 * @link     https://swoft.org
 * @document https://swoft.org/docs
 * @contact  group@swoft.org
 * @license  https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace App\Rpc\Service\Ad;
use App\Rpc\Lib\Ad\AdInterface;
use App\Rpc\Lib\Ad\CeInterface;
use Exception;
use Swoft\Co;
use Swoft\Db\DB;
use Swoft\Db\Exception\DbException;
use Swoft\Rpc\Server\Annotation\Mapping\Service;

/**
 * Class UserService
 *
 * @since 2.0
 *
 * @Service(version="2.1")
 */
class AdServiceV2 implements AdInterface
{
    /**
     * @param int   $id
     * @param mixed $type
     * @param int   $count
     *
     * @return array
     */
    public function getList(int $id, $type, int $count = 10): array
    {
        return ['name' => ['list']];
    }

    /**
     * @param int $id
     *
     * @return bool
     */
    public function delete(int $id): bool
    {
        return false;
    }

    /**
     * @return void
     */
    public function returnNull(): void
    {
        return;
    }

    /**
     * @return string
     */
    public function getBigContent(): string
    {
        $content = Co::readFile(__DIR__ . '/big.data');
        return $content;
    }

    /**
     * Exception
     * @throws Exception
     */
    public function exception(): void
    {
        throw new Exception('exception version');
    }

    /**
     * @param string $content
     *
     * @return int
     */
    public function sendBigContent(string $content): int
    {
        return strlen($content);
    }

    /**
     * @param int $status
     *
     * @return array
     */
    public function getFinance(int $status = 1): array
    {
        try{
            $tableName = "finance";
            $where = array("status"=>$status);
            $data = DB::table($tableName)->where($where)->get();
            foreach($data as $key=>$val) {
                $keywords = $val['keywords'];
                $keywords = explode(',', $keywords);
                $content = $val['content'];
                $content = explode(',', $content);
                $re_data[$key]['title'] = $val['title'];
                $re_data[$key]['excerpt'] = $val['excerpt'];
                $re_data[$key]['addtime'] = date('Y-m-d', $val['addtime']);
                foreach ($keywords as $key2 => $val2) {
                    $re_data[$key]['mokuai'][$key2]['keywords'] = $val2;
                    $re_data[$key]['mokuai'][$key2]['content'] = $content[$key2];
                }
            }
            return $re_data;

        }catch (DbException $exception){
            return [];
        }catch (Exception $exception){
            return [];
        }
    }
}
