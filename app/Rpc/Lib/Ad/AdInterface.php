<?php declare(strict_types=1);
/**
 * This file is part of Swoft.
 *
 * @link     https://swoft.org
 * @document https://swoft.org/docs
 * @contact  group@swoft.org
 * @license  https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

namespace App\Rpc\Lib\Ad;

/**
 * Class UserInterface
 *
 * @since 2.0
 */
interface AdInterface
{
    /**
     * @param int   $id
     * @param mixed $type
     * @param int   $count
     *
     * @return array
     */
    public function getList(int $id, $type, int $count = 10): array;

    /**
     * @param int $id
     *
     * @return bool
     */
    public function delete(int $id): bool;

    /**
     * @return string
     */
    public function getBigContent(): string;

    /**
     * @return void
     */
    public function returnNull():void ;

    /**
     * Exception
     */
    public function exception(): void;

    /**
     * @param string $content
     *
     * @return int
     */
    public function sendBigContent(string $content): int;

    /**
     * @param int $status
     *
     * @return array
     */
    public function getFinance(int $status=1): array;

    /**
     * @param int $type
     * @param string $field
     * @return array|null
     */
    //app 获取广告
    public function getIndexAd(int $type=1,string $field="id,img"):?array ;
    /**
     * @param array $typeMapping
     * @param string $field
     * @param int $offset
     * @return array|null
     */
    //获取首页广告
    public function getIndex(array $typeMapping=array(),string $field="fangfa,img",int $offset=3):?array ;

    /**
     * @param int $type
     * @param int $status
     * @return array|null
     */
    //获取广告老版本的接口规范
    public function getListold(int $type,int $status=1): ?array ;

    /**获取车服务广告 v2.0
     * @param int $type
     * @param string $order
     * @return array|null
     */
    public function getService(int $type=132,string $order="sort ASC"):?array ;

    /**统一入口 查询广告信息
     * type 132 车服务 133 车生活
     * 134 车金融
     * 132 道路救援
     * 24 车金融ios
     * @param int $type
     * @param string $order
     * @return array|null
     */
    public function getCommon(int $type,string $order="sort ASC"): ?array ;

    /**获取车首页广告数据
     * @param string $retuType 返回类型 默认为返回json 类型 Header Content-Type text/json
     * @return mixed
     */
    public function getIndexListHtmlJson(string $retuType="json");

    /**获取车首页广告数据 默认返回json格式
     * @param string $returnType
     * @return mixed
     */
    public function getIndexListSh(string $returnType="json");

    /**获取车首页广告数据 默认返回json格式
     * @param string $returnType
     * @return mixed
     */
    public function getIndexLists(string $returnType="json");

    /**
     * @param int $type
     * @param int $status
     * @return array|null
     */
    public function getHandlerold(int $type ,int $status=1):?array;

}
