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
use Swoft\Breaker\Annotation\Mapping\Breaker;
use Swoft\Co;
use Swoft\Db\DB;
use Swoft\Db\Exception\DbException;
use Swoft\Rpc\Server\Annotation\Mapping\Service;
use Swoft\Task\Task;

/**
 * Class AdService
 *
 * @since 2.0
 *
 * @Service()
 */
class AdService implements AdInterface
{
    private $currentVersion = 1.0;//当前版本号
    private $Token ;// 身份认证信息
    private $who;//身份认证 都可以进行Inject() 进行注入对象进来 who 不是 instance APIAuthor 这里不在这里处理
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
     * @Breaker(timeout=3.0,fallback="getFinanceFall",failThreshold=3)
     * @return array
     */
    public function getFinance(int $status = 1): array
    {
        try{
           $tableName = "finance";
           $where = array("status"=>$status);
           $data = DB::table($tableName)->where($where)->get();
           return $data->toArray();
            }catch (DbException $exception){
              return ["exception"=>$exception->getMessage()];
        }catch (Exception $exception){
           return ['exception'=>$exception->getMessage()];
        }
    }
    //方法降级
    public function getFinanceFall():array {
        return ["exception"=>AdService::class];
    }

    /**
     * @Breaker(failThreshold=3,fallback="getIndexAdFallBack")
     * @param int $type
     * @param string $field
     * @return array|null
     */
    public function getIndexAd(int $type = 1, string $field = "id,img"): ?array
    {
        try{
            $data = DB::table('ad')->where(array("type"=>$type))
                ->select($field)
                ->get();
            return $data->toArray();
        }catch (Exception $exception){
            throw new Exception($exception->getMessage());
        }catch (DbException $exception){
            throw new DbException($exception->getMessage());
        }
    }
 //熔断处理 降级为v1.0版本的即可 这里可以设置降级的版本号 默认为当前版本然后
    public function getIndexAdFallBack(int $type=1,string $field="id,img"):?array
    {
        return [];
    }

    /**
     * @Breaker(fallback="getIndexFacWrapped",failThreshold=3,retryTime=3)
     *
     * @param array $typeMapping
     * @param string $field
     * @param int $offset
     * @return array|null
     * 协程环境可以使用异步Task处理一下东西和其他协程网络的调度方式
     */
    public function getIndex(array $typeMapping = array(), string $field = "fangfa,img", int $offset = 3): ?array
    {
        try{
            $typeMapping = array();
            $typeMapping['top'] = 24;
            $typeMapping['newcar'] = 25;
            $typeMapping['supcar'] = 26;
            $typeMapping['chejinrong'] = 27;
            $typeMapping['chefuwu'] = 118;
            $typeMapping['cheyongpin'] = 119;
            $tableName = "ad";
            $field = explode(",",$field);
            $request = array(
                "top"=>function()use($typeMapping,$tableName,$field,$offset){
                    $res =  DB::table($tableName)->where(array("type"=>$typeMapping['top']))
                        ->limit($offset)
                        ->offset(0)
                        ->orderBy("sort","ASC")
                        ->get($field)
                        ->toArray();
                    return $res;
                },
                "newcar"=>function()use($typeMapping,$tableName,$field,$offset){
                    $res =  DB::table($tableName)->where(array("type"=>$typeMapping['newcar']))
                        ->orderBy("sort","ASC")
                        ->first($field);
                    //这里的第一列自动转换
                    return $res;
                },
                "supcar"=>function()use($typeMapping,$tableName,$field,$offset){
                    $res =  DB::table($tableName)->where(array("type"=>$typeMapping['supcar']))
                        ->orderBy("sort","ASC")
                        ->first($field);
                    return $res;
                },
                "chejingrong"=>function()use($typeMapping,$tableName,$field,$offset){
                    $res =  DB::table($tableName)->where(array("type"=>$typeMapping['chejinrong']))
                        ->orderBy("sort","ASC")
                        ->first($field);
                    return $res;
                },
                "chefuwu"=>function()use($typeMapping,$tableName,$field,$offset){
                    $res =  DB::table($tableName)->where(array("type"=>$typeMapping['chefuwu']))
                        ->orderBy("sort","ASC")
                        ->first($field);
                    return $res;
                },
                "cheyongpin"=>function()use($typeMapping,$tableName,$field,$offset){
                    $res =  DB::table($tableName)->where(array("type"=>$typeMapping['cheyongpin']))
                        ->orderBy("sort","ASC")
                        ->first($field);
                    return $res;
                },
            );
           $res =  CO::multi($request,100);
           return $res;
        }catch (Exception $exception){
            throw new Exception($exception->getMessage());
        }catch (DbException $exception){
            throw new DbException($exception->getMessage());
        }
    }
    // 上面的熔断处理 可以查询对应的时间 如果规定时间没有出现数据 则 再次定义 先的熔断 5s->7s->10s->return exception
    public function getIndexFacWrapped():?array
    {
       return [];
       //这里可以使用 异步Task进行处理机制
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
}
