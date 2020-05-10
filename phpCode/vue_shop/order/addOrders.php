<?php

if($_SERVER['REQUEST_METHOD'] == 'OPTIONS'){
    header("Access-Control-Allow-Origin: *");
    header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, Authorization");
    header('Access-Control-Allow-Methods: GET, POST, PUT,DELETE,OPTIONS,PATCH');
    file_put_contents('option.txt',json_encode($_REQUEST));
    exit;
}

header('Access-Control-Allow-Origin:*');
// 响应类型
header('Access-Control-Allow-Methods:*');
// 响应头设置
header('Access-Control-Allow-Headers:content-type,token,id');
header("Access-Control-Request-Headers: Origin, X-Requested-With, content-Type, Accept, Authorization");
header('Content-type: application/x-www-form-urlencoded');

$servername = "localhost";
$username = "root";
$password = "123456";
$dbname = "vue_shop";
$data = json_decode(file_get_contents("php://input"));
$order_customers = $data->selectedCustomers;
$order_goods = $data->selectedGoods;
$order_dates = $data->dates;
$order_send = $data->isSend;
$order_pay = $data->isPay;

// $order_customers = array(
//     (object) array(
//         'cate_id' => 3,
//         'address1' => "天津市/天津市/和平区",
//         'address2' => "第三街"
//     )
// );
// $order_goods = array(
//     (object) array(
//         'goods_id' => 16,
//         'order_quantity' => 1
//     )
// );
// $order_dates = array(
//     1585497600,1586275200,1587312000,1586966400
// );
// $order_send = true;
// $order_pay = true;

$quote = "'";
$response = (object) array(
    'meta' => (object) array(
        'status' => 200,
        'message' => '修改成功！'
    ));

$upt_time = strtotime(date("Y-m-d  h:i:sa"));// 当前时间

function checkToken($conn){
    $token = "'".(apache_request_headers())['Authorization']."'";
    $userNamePreSqls = $conn->prepare("SELECT * FROM `login_status` where token=".$token);
    $userNamePreSqls->execute();
    $userNameResult = $userNamePreSqls->setFetchMode(PDO::FETCH_ASSOC);
    $userNameRes = $userNamePreSqls->fetchAll();

    if(count($userNameRes) !== 0){
        return true;
    }else {
        return false;
    }
}


function checkRight($conn,$rightId){// 返回true则有效
    $token = "'".(apache_request_headers())['Authorization']."'";
    $userNamePreSqls = $conn->prepare("SELECT user_name FROM `login_status` where token=".$token);
    $userNamePreSqls->execute();
    $userNameResult = $userNamePreSqls->setFetchMode(PDO::FETCH_ASSOC);
    $userNameRes = $userNamePreSqls->fetchAll();

    $userName = "'".((object) $userNameRes[0])->user_name."'";
    $roleIdPreSqls = $conn->prepare("SELECT role_id FROM `users` where user_name=".$userName);
    $roleIdPreSqls->execute();
    $roleIdResult = $roleIdPreSqls->setFetchMode(PDO::FETCH_ASSOC);
    $roleIdRes = $roleIdPreSqls->fetchAll();

    if(count($roleIdRes) !== 0){
        $roleId = ((object) $roleIdRes[0])->role_id;
        $roleRightPreSqls = $conn->prepare("SELECT thirdLevel FROM `roles` where id=".$roleId);
        $roleRightPreSqls->execute();
        $roleRightResult = $roleRightPreSqls->setFetchMode(PDO::FETCH_ASSOC);
        $roleRightRes = $roleRightPreSqls->fetchAll();
        if(count($roleRightRes) !== 0){

            $rightsStr = ((object) $roleRightRes[0])->thirdLevel;
            $rightsArr = explode(',', $rightsStr);
            $rightIdStr = strval($rightId);
            if(array_search($rightIdStr,$rightsArr) !== false){
                return true;
            }else {
                return false;
            }
        }
        return false;
        
    }else {
        return false;
    }
}



try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname",$username,$password);
    //设置PDO错误模式，抛出异常
    $conn->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $conn->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);
    if(!checkToken($conn)){
        $response->data = null;
        $response->meta->status = 403;
        $response->meta->message = '无效的token';
        echo json_encode($response);
        exit;
    }
    if(!checkRight($conn,211)){
        $response->data = null;
        $response->meta->status = 403;
        $response->meta->message = '权限不足';
        echo json_encode($response);
        exit;
    }
    foreach($order_customers as $customerKey => $customer) {
        $customer_id = $customer->user_id;
        $customer_address = $customer->address1." ".$customer->address2;

        foreach($order_goods as $goodsKey => $goods) {
            $goods_id = $goods->cate_id; // 商品Id
            $order_quantity = $goods->order_quantity; // 商品数量
            $goods_price = $goods->goods_price;// 商品价格
            $order_price = $order_quantity * $goods_price; // 订单价格

            foreach($order_dates as $dateKey => $intDate){
                $order_number = 'my-shop'.$intDate.$customer_id.$goods_id.$order_quantity;
                $goodsPricePreSqls = $conn->prepare("INSERT INTO `orders` (user_id,goods_id,order_quantity,order_price,is_send,pay_status,consignee_addr,order_number,create_time,update_time) VALUES (".$customer_id.",".$goods_id.",".$order_quantity.",".$order_price.",".$order_send.",".$order_pay.",".$quote.$customer_address.$quote.",".$quote.$order_number.$quote.",".$intDate.",".$intDate.")");
                $goodsPricePreSqls->execute();
                $priceResult = $goodsPricePreSqls->setFetchMode(PDO::FETCH_ASSOC);
                $priceRes = $goodsPricePreSqls->fetchAll();
            }
        }
    }
}
catch(PDOException $e){
    echo $e->getMessage();
}
echo json_encode($response);
$conn = null;


?>