<?php declare(strict_types=1);
namespace App\Http\Controller\ad;
use App\Rpc\Lib\Ad\AdInterface;
use App\Rpc\Lib\Ad\CeInterface;
use Exception;
use Swoft\Co;
use Swoft\Http\Server\Annotation\Mapping\Controller;
use Swoft\Http\Server\Annotation\Mapping\RequestMapping;
use Swoft\Limiter\Annotation\Mapping\RateLimiter;
use Swoft\Rpc\Client\Annotation\Mapping\Reference;
use App\Rpc\Lib\UserInterface;
/**
 * Class AdController
 *
 * @since 2.0
 *
 * @Controller(prefix="ad/ad")
 */
class AdController
{



    /**
     * @Reference(pool="user.pool")
     *
     * @var UserInterface
     */
    private $userService;

    /**
     * @Reference(pool="user.pool", version="1.2")
     *
     * @var UserInterface
     */
    private $userService2;

    /**@Reference(pool="user.pool",version="1.2")
     *
     * @var CeInterface
     */
    private $ceService;

    /**
     * @Reference(pool="user.pool")
     *
     * @var AdInterface
     */
    private $adService;

    /**
     * @Reference(pool="user.pool",version="2.1")
     *
     * @var AdInterface
     */

    private $adService2;

    /**
     * @RequestMapping("getList")
     *
     * @return array
     */
    public function getList(): array
    {
        $result  = $this->userService->getList(12, 'type');
        $result2 = $this->userService2->getList(12, 'type');

        return [$result, $result2];
    }

    /**
     * @RequestMapping("returnBool")
     *
     * @return array
     */
    public function returnBool(): array
    {
        $result = $this->userService->delete(12);

        if (is_bool($result)) {
            return ['bool'];
        }

        return ['notBool'];
    }

    /**
     * @RequestMapping()
     *
     * @return array
     */
    public function bigString(): array
    {
        $string = $this->userService->getBigContent();

        return ['string', strlen($string)];
    }

    /**
     * @RequestMapping()
     *
     * @return array
     */
    public function sendBigString(): array
    {
        $content = Co::readFile(__DIR__ . '/../../Rpc/Service/big.data');

        $len    = strlen($content);
        $result = $this->userService->sendBigContent($content);
        return [$len, $result];
    }

    /**
     * @RequestMapping()
     *
     * @return array
     */
    public function returnNull(): array
    {
        $this->userService->returnNull();
        return [null];
    }

    /**
     * @RequestMapping()
     *
     * @return array
     *
     * @throws Exception
     */
    public function exception(): array
    {
        $this->userService->exception();

        return ['exception'];
    }

    /**
     * @RequestMapping("getFinance")
     *
     *
     * @RateLimiter(rate=20,fallback="getFinaceFall")
     *
     * @return array
     */
    public function getFinance():array {
        $status = context()->getRequest()->get("status",1);
        $status=  intval($status);
        $data = $this->adService->getFinance($status);
        return $data;
    }

    public function getFinanceFall(){
        return [
            'fallRate'=>context()->getRequest()->withHeader("Content-Type","text/html")
        ];
    }

}
