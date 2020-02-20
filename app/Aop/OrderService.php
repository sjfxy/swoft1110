<?php  declare(strict_types=1);
namespace App\Aop;
class OrderService {
    /**
     * 生成订单
     * @param string $product_name 商品名字
     * @param int $quantity 购买的数量
     * @param int $user_id 用户ID
     * @return array
     */
    public function genrateOrder($product_name,$quantity,$user_id):array{
        $price = 1000;
        $amount = $price * $quantity;
        $order = [
          'order_no'=>uniqid($user_id.time().$amount),
            'product_name'=>$product_name,
            'price'=>$price,
            'quantity'=>$quantity,
            'amount'=>$amount
        ];
        return $order;
    }
}