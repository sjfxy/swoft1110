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

    /**
     * @param int $type
     * @param string $field
     * @return array|null
     */
    public function getIndexAd(int $type = 1, string $field = "id,img"): ?array
    {
        // TODO: Implement getIndexAd() method.
    }

    /**
     * @param array $typeMapping
     * @param string $field
     * @param int $offset
     * @return array|null
     */
    public function getIndex(array $typeMapping = array(), string $field = "fangfa,img", int $offset = 3): ?array
    {
        // TODO: Implement getIndex() method.
    }

    /**
     * @param int $type
     * @param int $status
     * @return array|null
     */
    public function getListold(int $type, int $status = 1): ?array
    {
        // TODO: Implement getListold() method.
    }

    /**获取车服务广告 v2.0
     * @param int $type
     * @param string $order
     * @return array|null
     */
    public function getService(int $type = 132, string $order = "sort ASC"): ?array
    {
        // TODO: Implement getService() method.
    }

    /**统一入口 查询广告信息
     * type 132 车服务 133 车生活
     * 134 车金融
     * 132 道路救援
     * 24 车金融ios
     * @param int $type
     * @param string $order
     * @return array|null
     */
    public function getCommon(int $type, string $order = "sort ASC"): ?array
    {
        // TODO: Implement getCommon() method.
    }

    /**获取车首页广告数据
     * @param string $retuType 返回类型 默认为返回json 类型 Header Content-Type text/json
     * @return mixed
     */
    public function getIndexListHtmlJson(string $retuType = "json")
    {
        // TODO: Implement getIndexListHtmlJson() method.
    }

    /**获取车首页广告数据 默认返回json格式
     * @param string $returnType
     * @return mixed
     */
    public function getIndexListSh(string $returnType = "json")
    {
        // TODO: Implement getIndexListSh() method.
    }

    /**获取车首页广告数据 默认返回json格式
     * @param string $returnType
     * @return mixed
     */
    public function getIndexLists(string $returnType = "json")
    {
        // TODO: Implement getIndexLists() method.
    }

    /**
     * @param int $type
     * @param int $status
     * @return array|null
     */
    public function getHandlerold(int $type, int $status = 1): ?array
    {
        // TODO: Implement getHandlerold() method.
    }
}
