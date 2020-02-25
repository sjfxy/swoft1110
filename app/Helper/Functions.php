<?php
/**
 * This file is part of Swoft.
 *
 * @link     https://swoft.org
 * @document https://swoft.org/docs
 * @contact  group@swoft.org
 * @license  https://github.com/swoft-cloud/swoft/blob/master/LICENSE
 */

function user_func(): string
{
    return 'hello';
}
function json($code=200,$message="ok",$data=array()):string {
    if(!empty($data)){
        return json_encode(['code'=>$code,"message"=>$message,"data"=>$data],320);
    }
    return json_encode(['code'=>$code,"message"=>$message],320);
}
